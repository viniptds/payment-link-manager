<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CieloWebhookController extends Controller
{
    // Captura
    // Cancelamento
    // Sondagem
    public function notification(Request $request)
    {
        Log::debug('WEBHOOK - Notification');
        Log::info(json_encode($request->all()));
        $paymentId = $request->post('PaymentId');
        $changeType = $request->post('ChangeType');

        $payment = Payment::find($paymentId);

        // Temporary response
        return response('', 200);
    }

    public function changeStatus(Request $request)
    {
        Log::debug('WEBHOOK - Change status');
        Log::info(json_encode($request->all()));
        
        // Temporary response
        return response('', 200);
    }

    public function returnHook(Request $request)
    {
        Log::debug('WEBHOOK - Return');
        Log::info(json_encode($request->all()));
        
        // Temporary response
        return response('', 200);
    }
    
}
