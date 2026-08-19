<?php

namespace App\Http\Controllers;

use App\Helpers\CieloGatewayHelper;
use App\Models\Company;
use App\Models\Customer;
use App\Models\GatewayOperation;
use App\Models\Payment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PublicPaymentController extends Controller
{
    function show(Payment $payment, Request $request)
    {
        if (($request->get('page') ?? '') == 'card' && empty($payment->customer)) {
            return redirect('pay/' . $payment->id);
        }
        $availableBrands = CieloGatewayHelper::getAvailableBrands();
        return view('public.payment')
            ->with('payment', $payment)
            ->with('card_brands', $availableBrands);
    }

    function personal(Payment $payment, Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'cpf' => 'required|cpf',
            'document' => 'required|numeric',
            'customer_id' => 'nullable|exists:customers,id'
        ]);

        $data = $request->post();
        $customerId = $data['customer_id'] ?? false;
        if ($customerId) {
            $customer = Customer::find($customerId);
        } else {
            $customer = new Customer();
        }

        $data['cpf'] = preg_replace('/[^0-9]/', '', $data['cpf']);

        $customer->email = $data['email'];
        $customer->name = $data['name'];
        $customer->cpf = $data['cpf'];
        $customer->document = $data['document'] ?? null;
        $customer->company_id = $payment->company_id ?? Company::current()?->id;
        $customer->save();

        $payment->customer_id = $customer->id;
        $payment->save();

        return redirect('/pay/' . $payment->id . '?page=card')->with('customer', $customer);
    }

    function checkout(Payment $payment, Request $request)
    {
        $request->validate([
            'card_number' => 'required',
            'card_holder' => 'required',
            'card_brand' => 'required',
            'card_expiration_date' => 'required|after_or_equal:now',
            'card_cvv' => 'required|numeric|min_digits:3|max_digits:3',
            'payment_installments' => 'required',
            'customer_id' => 'required|exists:customers,id'
        ]);
        
        $success = false;
        $response = null;
        $gatewayOperation = null;

        try {
            if ($payment->status != Payment::STATUS_ACTIVE) {
                throw new Exception('Este link não é válido.');
            } elseif (!is_null($payment->expire_at) && $payment->expire_at < date('Y-m-d H:i:s')) {
                throw new Exception('Este link expirou.');
            } else {
                $data = $request->post();

                if ($payment->max_installments_type) {
                    switch ($payment->max_installments_type) {
                        case Payment::INSTALLMENT_TYPE_MAX:
                            if ($data['payment_installments'] > $payment->max_installments) {
                                throw new Exception('O valor de parcelas deve ser menor ou igual ao valor de parcelas permitidas.');
                            }
                            break;
                        case Payment::INSTALLMENT_TYPE_MIN:
                            if ($data['payment_installments'] < $payment->max_installments) {
                                throw new Exception('O valor de parcelas deve ser maior ou igual ao valor de parcelas permitidas.');
                            }
                            break;
                        default:
                            if ($data['payment_installments'] != $payment->max_installments) {
                                throw new Exception('O valor de parcelas deve ser igual ao valor de parcelas permitidas.');
                            }
                            break;
                    }
                }

                $card = [
                    'cvv' => $data['card_cvv'],
                    'brand' => $data['card_brand'],
                    'expiration_date' => date('m/Y', strtotime($data['card_expiration_date'])),
                    'number' => filter_var($data['card_number'], FILTER_SANITIZE_NUMBER_INT),
                    'holder' => $data['card_holder']
                ];
                $customer = $payment->customer;

                $cieloHelper = new CieloGatewayHelper($payment->id);
                $cieloHelper->setCustomer($card['holder'], preg_replace('/[^0-9]/', '', $customer->cpf));

                $cieloHelper->setPayment($payment->value, $data['payment_installments']);

                $sale = $cieloHelper->makeCreditCardPayment($card);

                $gatewayOperation = new GatewayOperation();
                $gatewayOperation->gateway_id = 1; // Assuming 1 is the ID for Cielo gateway
                $gatewayOperation->type = GatewayOperation::PAY_OPERATION;
                $gatewayOperation->status = false;

                $returnOptions = CieloGatewayHelper::getCreditCardPaymentReturnMessages($card['brand']);

                if (is_array($sale)) {
                    $response = $returnOptions[$sale['code']] ?? 'Falha no pagamento.';
                    $sale['response'] = $response;
                    $gatewayOperation->log = json_encode($sale);
                    throw new Exception($response);
                } else {
                    $cieloPayment = $sale->getPayment();
                    $returnCode = $cieloPayment->getReturnCode();
                    $status = $cieloPayment->getStatus();

                    $gatewayOperation->log = json_encode($cieloPayment);

                    Log::debug('Return Code: ' . $returnCode);
                    Log::debug('Payment Status: ' . $status);

                    if (CieloGatewayHelper::creditCardPaymentIsSuccessful($status, $returnCode)) {
                        $payment->status = Payment::STATUS_PAID;
                        $payment->paid_at = date('Y-m-d H:i:s');
                        $payment->save();

                        $gatewayOperation->status = true;
                        $success = true;
                        $payment->gatewayOperations()->save($gatewayOperation);
                    } else {
                        throw new Exception($returnOptions[$returnCode] ?? 'Falha no pagamento.');
                    }
                }
            }
        } catch (Exception $e) {
            $response = $e->getMessage();

            if (!is_null($gatewayOperation)) {
                $payment->gatewayOperations()->save($gatewayOperation);
            }
        }

        return $success ?
            redirect('pay/' . $payment->id . '/receipt')->with('receiptMessage', 'O pagamento foi realizado com sucesso!') :
            redirect('pay/' . $payment->id . "?page=card")->with('cardMessage', $response);
    }

    function receipt(Payment $payment)
    {
        if ($payment->status != Payment::STATUS_PAID) {
            return redirect('pay/' . $payment->id);
        } else {
            return view('public.receipt')->with('payment', $payment);
        }
    }
}
