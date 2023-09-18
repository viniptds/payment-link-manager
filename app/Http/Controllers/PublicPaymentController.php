<?php

namespace App\Http\Controllers;

use App\Helpers\CieloGatewayHelper;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PublicPaymentController extends Controller
{
    function show(Payment $payment)
    {
        $availableBrands = CieloGatewayHelper::getAvailableBrands();
        return view('public.payment')
        ->with('payment', $payment)
        ->with('card_brands', $availableBrands);
    }

    function personal(Payment $payment, Request $request)
    {
        $request->validate([
            'email' => 'required',
            'name' => 'required',
            'cpf' => 'required',
            'document' => 'nullable',
            'customer_id' => 'nullable|exists:customers,id'
        ]);

        $data = $request->post();
        $customerId = $data['customer_id'] ?? false;
        if ($customerId) {
            $customer = Customer::find($customerId);
        } else {
            $customer = new Customer();
        }

        $customer->email = $data['email'];
        $customer->name = $data['name'];
        $customer->cpf = $data['cpf'];
        $customer->document = $data['document'] ?? null;
        $customer->save();

        $payment->customer_id = $customer->id;
        // $payment->curr_step = Payment::STEP_CARD;
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
            'card_cvv' => 'required|integer|min_digits:3|max_digits:3',
            'payment_installments' => 'required'
        ]);

        if ($payment->status != Payment::STATUS_ACTIVE) {
            $response = 'Este link não é válido.';
        } elseif($payment->expire_at < date('Y-m-d H:i:s')) {
            $response = 'Este link expirou.';
        } else {
            $data = $request->post();
            $card = [
                'cvv' => $data['card_cvv'],
                'brand' => $data['card_brand'],
                'expiration_date' => date('m/Y', strtotime($data['card_expiration_date'])),
                'number' => $data['card_number'],
                'holder' => $data['card_holder']
            ];
            // $customer = $payment->customer;

            $cieloHelper = new CieloGatewayHelper($payment->id);
            $cieloHelper->setCustomer($card['holder']);

            $cieloHelper->setPayment($payment->value * 100, $data['payment_installments']);
            
            $sale = $cieloHelper->makeCreditCardPayment($card);
            $cieloPayment = $sale->getPayment();
            
            if ($cieloPayment->returnCode == '4/6') { // Check the API to compare
                Log::debug(json_encode($cieloPayment));
                $payment->transaction_log = json_encode($cieloPayment);
                $payment->status = Payment::STATUS_PAID;
                $payment->paid_at = date('Y-m-d H:i:s');
                $payment->save();

                return redirect('pay/' . $payment->id . '/receipt');
            } else {
                $response = 'O cartão não é válido';
            }
        }

        return redirect('pay/' . $payment->id)->with('errors', $response);
        
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