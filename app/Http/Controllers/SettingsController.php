<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Factory;
use App\Models\Settings;

class SettingsController extends Controller
{
    function index()
    {
        $settings = Settings::select()->with('user')->get();
        return view('settings.index')->with('settings', $settings);
    }

    public function update(Settings $setting, Request $request, Factory $cache)
    {
        $request->validate([
            'file' => 'nullable|file',
            'value' => 'required|string|min:1|max:100',
        ]);
        
        $data = $request->post();

        if ($setting->type == 'file') {
            if (empty($data['file'])) {
                // File was not sent
            }
            // TODO: Process file handling
        }

        $setting->value = $request['value'];
        $setting->save();
        $cache->forget('settings');

        return response()->json([
            'status' => true,
            'id' => $setting->id,
            'value' => $setting->value
        ]);

    }

    public function restoreFromFactory(Factory $cache) 
    {
        $factoryValues = Settings::getFactoryValues();

        foreach($factoryValues as $id => $value) {
            Settings::updateOrCreate(
                ['id' => $id], 
                [
                    'value' => $value['value'], 
                    'type' => $value['type'],
                    'description' => $value['description'],
                    'updated_by' => Auth::user()->id,
                ]
            );
        }

        $cache->forget('settings');

        return redirect(route('settings.index'));
    }
}
