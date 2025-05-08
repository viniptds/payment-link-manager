<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gateways\StoreGatewayRequest;
use App\Http\Requests\Gateways\UpdateGatewayRequest;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gateways = Gateway::all();
        return view('settings.gateways', ['gateways' => $gateways]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGatewayRequest $request)
    {
        $data = $request->validated();

        $gateway = new Gateway();

        $gateway->name = $data['name'];
        $gateway->description = $data['description'] ?? '';

        // TODO: handle photo upload
        // $gateway->photo = $data['photo'];

        if (!empty($data['credentials'])) {
            $credentials = array_map(function ($item, $key) {
                return "$item=$key";
            }, $data['credentials']['label'], $data['credentials']['value']);

            $gateway->credentials = implode(',', $credentials ?? []);
        }

        $gateway->status = 1;

        $gateway->save();

        return redirect('settings/gateways/' . $gateway->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(Gateway $gateway)
    {
        return view('settings.gateways.show')->with('gateway', $gateway);
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
