<?php

namespace App\Http\Controllers;

use App\Models\HospitalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('view settings');
        $setting = HospitalSetting::current();

        return view('settings.index', compact('setting'));
    }

    public function updateHospital(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'email'         => 'nullable|email',
            'phone'         => 'nullable|string|max:20',
            'currency'      => 'nullable|string|max:10',
            'currency_symbol' => 'nullable|string|max:5',
        ]);

        $setting = HospitalSetting::current();

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('hospital', 'public');
            $setting->update(['logo' => $path]);
        }

        $setting->update($request->except(['logo', '_token', '_method']));

        return back()->with('success', 'Hospital settings updated.');
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        HospitalSetting::current()->update($request->only([
            'timezone', 'date_format', 'time_format',
            'morning_shift_start', 'morning_shift_end',
            'evening_shift_start', 'evening_shift_end',
            'night_shift_start', 'night_shift_end',
            'tax_label', 'tax_rate',
            'low_stock_alert', 'low_stock_threshold',
        ]));

        return back()->with('success', 'System settings updated.');
    }
}
