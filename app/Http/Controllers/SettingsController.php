<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Cache\Factory;
use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    function index()
    {
        $settings = Settings::select()->with('user')->get();
        return view('settings.index')->with('settings', $settings);
    }

    public function update(Settings $setting, UpdateSettingsRequest $request)
    {
        Log::info('Updating setting: ' . $setting->id);
        $request->validated();
        
        $data = $request->post();

        if ($setting->type == Settings::TYPE_FILE) {
            if (!empty($request->file('file') ?? null)) {
                $originalName = $request->file('file')->getClientOriginalName();
                $request->file('file')->storeAs(
                    'assets',
                    $originalName,
                    'public'
                );

                if (Storage::disk('public')->exists('assets/' . $setting->value)) {
                    Storage::disk('public')->delete('assets/' . $setting->value);
                }

                $setting->value = $originalName;
            }
            // File was not sent
        } else {
            $setting->value = $data['value'];
        }

        $setting->save();

        Cache::forget('app.settings');

        return response()->json([
            'status' => true,
            'id' => $setting->id,
            'value' => $setting->value
        ]);

    }

    public function restoreFromFactory() 
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

        Cache::forget('app.settings');

        return redirect(route('settings.index'));
    }
}
