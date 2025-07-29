<?php

namespace App\Http\Controllers;

use App\Helpers\CieloGatewayHelper;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Requests\Payments\UpdatePaymentRequest;
use App\Models\Customer;
use App\Models\Gateway;
use App\Models\GatewayOperation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $links = Payment::select();

        if (!$request->user()->is_admin) {
            $links = Payment::select()->where('created_by', $request->user()->id);
        }
        
        if (!empty($request->search)) {
            $search = $request->search;
            $links = $links->where(function ($query) use ($search) {
                $query->where('description', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($request->status)) {
            $status = $request->status;
            $links = $links->where('status', $status);
        }

        $links = $links->orderByDesc('created_at')->paginate(10);

        return view('payments.index', [
            'links' => $links,
        ]);
    }

    public function show(Payment $payment)
    {
        $gateways = Gateway::all();
        $customers = Customer::all();
        $payment->load('gateways');

        return view('payments.show', [
            'payment' => $payment,
            'available_gateways' => $gateways,
            'customers' => $customers
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();

        $payment = new Payment();
        $payment->id = Str::uuid();
        $payment->value = $data['value'];
        $payment->description = $data['description'];
        $payment->max_installments = $data['max_installments'] ?? 1;
        $payment->expire_at = $data['expire_at'] ?? null;
        $payment->created_by = $request->user()->id;

        $payment->status = Payment::STATUS_ACTIVE;

        if ($payment->expire_at && $payment->expire_at <= date('Y-m-d H:i:s')) {
            $payment->status = Payment::STATUS_EXPIRED;
        }

        $payment->save();

        if (empty($data['gateway_ids'])) {
            $data['gateway_ids'] = [1];
        }

        if ($data['gateway_ids'] ?? []) {
            $payment->gateways()->sync($data['gateway_ids']);
        }

        return redirect('payments/' . $payment->id);
    }

    public function update(Payment $payment, UpdatePaymentRequest $request)
    {
        $data = $request->validated();
        $message = 'O pagamento já foi efetuado. Não é possível editar os dados.';

        if ($payment->status != Payment::STATUS_PAID) {
            $payment->fill($data);

            if (!empty($data['gateway_ids'])) {
                $payment->gateways()->sync($data['gateway_ids']);
            }
            $payment->save();
            $message = 'O link de pagamento foi atualizado com sucesso';
        }

        return redirect('/payments/' . $payment->id)->with('editMessage', $message);
    }

    public function destroy(Payment $payment)
    {
        $message = 'O pagamento já foi pago e não pode ser removido';
        if ($payment->status != Payment::STATUS_PAID) {
            $payment->gateways()->detach();
            $payment->delete();
            $message = 'O pagamento foi removido com sucesso';
        }

        return redirect('payments')->with('message', $message);
    }

    public function toggleActive(Payment $payment)
    {
        switch ($payment->status) {
            case Payment::STATUS_PAID:
            case Payment::STATUS_CANCELLED:
            case Payment::STATUS_EXPIRED:
                break;
            case Payment::STATUS_ACTIVE:
                $payment->status = Payment::STATUS_INACTIVE;
                $payment->save();
                break;
            case Payment::STATUS_INACTIVE:
                // TODO: Check if payment is still on time
                $payment->status = Payment::STATUS_ACTIVE;
                $payment->save();
                break;
        }

        return redirect('payments/' . $payment->id);
    }

    public function markAsPaid(Payment $payment, Request $request)
    {
        $message = __('The payment is not able to be marked as paid.');

        if ($payment->status == Payment::STATUS_ACTIVE) {
            $payment->status = Payment::STATUS_PAID;
            $payment->paid_at = date('Y-m-d H:i:s');
            $payment->save();

            $message = __('The payment was successfully marked as paid');
        }

        return redirect('payments/' . $payment->id)->with('message', $message);
    }

    public function void(Payment $payment)
    {
        $response = [
            'status' => false,
            'code' => 403
        ];

        if ($payment->status == Payment::STATUS_CANCELLED) {
            $response['message'] = 'O pagamento já foi estornado';
        } elseif ($payment->status == Payment::STATUS_PAID) {
            $latestPayment = $payment->latestPayment;

            if ($latestPayment->type != GatewayOperation::PAY_OPERATION) {
                $response['message'] = 'O último status do pagamento não é válido';
            } else {
                $transaction = json_decode($latestPayment->log ?? '', 1);
                $paymentId = $transaction['paymentId'] ?? false;

                if ($paymentId) {
                    $cieloHelper = new CieloGatewayHelper($payment->id);
                    $sale = $cieloHelper->cancelPayment($paymentId, $payment->amount);
                    $returnOptions = CieloGatewayHelper::getCreditCardVoidReturnMessages();

                    // Save gateway operation
                    $gatewayOperation = new GatewayOperation();
                    $gatewayOperation->gateway = 'CIELO30';
                    $gatewayOperation->type = GatewayOperation::VOID_OPERATION;
                    $gatewayOperation->status = false;
                    $gatewayOperation->log = json_encode($sale);

                    if (is_array($sale)) {
                        $response['message'] = $returnOptions[$sale['code']] ?? 'Falha no cancelamento do pagamento.';
                        $sale['response'] = $response['message'];
                    } else {
                        $status = $sale->getStatus();

                        if (CieloGatewayHelper::creditCardVoidIsSuccessful($status)) {
                            $updatedSale = $cieloHelper->getSale($paymentId);
                            $updatedPayment = $updatedSale->getPayment();

                            if ($updatedPayment->getCapturedAmount() == $updatedPayment->getVoidedAmount()) {
                                $payment->status = Payment::STATUS_CANCELLED;
                                $payment->save();

                                $gatewayOperation->status = true;

                                $response['message'] = 'O pagamento foi cancelado e estornado com sucesso.';
                                $response['status'] = true;
                                $response['code'] = 200;
                            } else {
                                $response['message'] = 'O valor cancelado é diferente do valor pago. Por favor, verifique no painel Cielo';
                            }
                        } else {
                            Log::debug('Payment Id: ' . $payment->id);
                            Log::debug('Payment Status: ' . $status);
                            $response['message'] = $returnOptions[$status] ?? 'Falha no cancelamento do pagamento.';
                        }
                    }

                    $payment->gatewayOperations()->save($gatewayOperation);
                } else {
                    $response['message'] = 'O pagamento não possui dados de transação';
                }
            }
        } else {
            $response['message'] = 'O pagamento ainda não foi pago para ser cancelado';
        }
        return response($response, $response['code']);
    }
}
