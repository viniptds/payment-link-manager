<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    function index(Request $request) 
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
    function show(Payment $payment)
    {
        return view('payments.show', [
            'payment' => $payment
        ]);
    }
    
    function store(Request $request)
    {
        $request->validate([
            'value' => 'required|numeric|decimal:0,2',
            'description' => 'required',
            'expire_at' => 'nullable|date|after_or_equal:now'
        ]);
        $data = $request->post();
        
        $payment = new Payment();
        $payment->id = Str::uuid();
        $payment->value = $data['value'];
        $payment->description = $data['description'];
        $payment->expire_at = $data['expire_at'] ?? null;
        $payment->created_by = $request->user()->id ?? null;
        $payment->status = Payment::STATUS_ACTIVE;

        if ($payment->expire_at && $payment->expire_at <= date('Y-m-d H:i:s')) {
            $payment->status = Payment::STATUS_EXPIRED;
        }

        $payment->save();

        return redirect('payments');
    }

    function toggleActive(Payment $payment)
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

    function markAsPaid(Payment $payment, Request $request)
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
}
