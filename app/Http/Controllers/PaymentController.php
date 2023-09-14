<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    function index() 
    {
        $links = Payment::all();
        return view('payments.index', [
            'links' => $links,
        ]);
    }
    function show(Payment $payment)
    {
        return view('payments.show', [
            'payment' => $payment
        ]);
    }
    
    function store(Request $request)
    {
        $data = $request->post();
        
        $payment = new Payment();
        $payment->id = Str::uuid();
        $payment->value = $data['value'];
        $payment->description = $data['description'];
        $payment->expire_at = $data['expire_at'] ?? null;
        $payment->status = Payment::STATUS_ACTIVE;

        var_dump($data);
        var_dump($payment);
        die;
        if ($payment->expire_at <= date('Y-m-d H:i:s')) {
            $payment->status = Payment::STATUS_EXPIRED;
        }

        $payment->save();

        return response()->json($payment, 200);
    }

    function toggleActive(Payment $payment)
    {
        switch($payment->status)
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

    function markAsPaid(Payment $payment, Request $request)
    {
        if ($payment->status == Payment::STATUS_ACTIVE) {
            $payment->status = Payment::STATUS_PAID;
            $payment->paid_at = date('Y-m-d H:i:s');
            $payment->save();

            return redirect('payments');
        } else {
            $message = __('The payment is not able to be marked as paid.');
        }
        return redirect('payments')->with('message', $message);
    }
}
