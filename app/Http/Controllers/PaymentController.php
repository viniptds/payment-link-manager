<?php

namespace App\Http\Controllers;

use App\Helpers\CieloGatewayHelper;
use App\Models\GatewayOperation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index(Request $request) 
    {
        if ($request->user()->is_admin) {
            $links = Payment::select()->orderByDesc('created_at')->get();
        } else {
            $links = Payment::select()->where('created_by', $request->user()->id)->orderByDesc('created_at')->get();
        }

        return view('payments.index', [
            'links' => $links,
        ]);
    }

    public function show(Payment $payment)
    {
        return view('payments.show', [
            'payment' => $payment
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric|decimal:0,2|gte:' . env('CIELO_MIN_INSTALLMENT_VALUE'),
            'description' => 'required|string|min:1|max:100',
            'expire_at' => 'nullable|date|after_or_equal:now',
            'max_installments' => 'nullable|numeric|gt:0|lte:' . env('CIELO_MAX_INSTALLMENTS', 12)
        ]);
        $data = $request->post();
        
        $payment = new Payment();
        $payment->id = Str::uuid();
        $payment->value = $data['value'];
        $payment->description = $data['description'];
        $payment->max_installments = $data['max_installments'] ?? null;
        $payment->expire_at = $data['expire_at'] ?? null;
        $payment->created_by = $request->user()->id ?? null;
        $payment->status = Payment::STATUS_ACTIVE;

        if ($payment->expire_at && $payment->expire_at <= date('Y-m-d H:i:s')) {
            $payment->status = Payment::STATUS_EXPIRED;
        }

        $payment->save();

        return redirect('payments');
    }

    public function update(Payment $payment, Request $request)
    {
        $request->validate([
            'value' => 'required|numeric',
            'description' => 'required',
            'expire_at' => 'nullable|date'
        ]);
        
        $message = 'O pagamento já foi efetuado. Não é possível editar os dados.';

        if ($payment->status != Payment::STATUS_PAID) {
            $payment->fill($request->all());
            $payment->save();
            $message = 'O link de pagamento foi atualizado com sucesso';
        }

        return redirect('/payments/' . $payment->id)->with('editMessage', $message);
    }

    public function destroy(Payment $payment)
    {
        $message = 'O pagamento já foi pago e não pode ser removido';
        if ($payment->status != Payment::STATUS_PAID) {
            $payment->delete();
            $message = 'O pagamento foi removido com sucesso';
        }

        return redirect('payments')->with('message', $message);
    }

    public function toggleActive(Payment $payment)
    {
        switch ($payment->status)
        {
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

        return redirect('payments');
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
            $payment->latestPayment();
            $transaction = json_decode($payment->latestPayment()->log ?? '', 1);
            $paymentId = $transaction['paymentId'] ?? false;

            if ($paymentId) {
                $cieloHelper = new CieloGatewayHelper($payment->id);
                $sale = $cieloHelper->cancelPayment($paymentId, $payment->amount);
                
                if ($sale) {
                    // Save gateway operation
                    $gatewayOperation = new GatewayOperation();
                    $gatewayOperation->gateway = 'CIELO30';
                    $gatewayOperation->type = GatewayOperation::VOID_OPERATION;
                    $gatewayOperation->status = false;
                    

                    if (CieloGatewayHelper::creditCardVoidIsSuccessful($status)) {
                        $updatedSale = $cieloHelper->getSale($paymentId);
                        $updatedPayment = $updatedSale->getPayment();

                        if ($updatedPayment->getCapturedAmount() == $updatedPayment->getVoidedAmount()) {

                            $payment->status = Payment::STATUS_CANCELLED;
                            $payment->save();

                            $gatewayOperation->log = json_encode($sale);
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
                        $returnOptions = CieloGatewayHelper::getCreditCardVoidReturnMessages();
                        $response['message'] = $returnOptions[$status] ?? 'Falha no cancelamento do pagamento.';
                    }

                    $payment->gatewayOperations()->save($gatewayOperation);
                }
            } else {
                $response['message'] = 'O pagamento não possui dados de transação';
            }
        } else {
            $response['message'] = 'O pagamento ainda não foi pago para ser cancelado';
        }
        return response($response, $response['code']);
    }
}
