<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGatewayRequest;
use App\Http\Requests\UpdateGatewayRequest;
use App\Models\Gateway;

class GatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = Gateway::all();
        return view('settings.gateways', [ 'gateways' => $gateways]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGatewayRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Gateway $gateway)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGatewayRequest $request, Gateway $gateway)
    {
        $gateway->status = $request->input('status');
        $gateway->save();

        return response()->json([
            'status' => true,
            'data' => $gateway
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gateway $gateway)
    {
        //
    }
}
