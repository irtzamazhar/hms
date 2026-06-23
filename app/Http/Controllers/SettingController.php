<?php

namespace App\Http\Controllers;

use App\Models\HospitalSetting;
use App\Models\Module;
use App\Providers\AppServiceProvider;
use App\Support\Modules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $this->authorize('view settings');
        $setting = HospitalSetting::current();
        $moduleStates = Modules::states();

        return view('settings.index', compact('setting', 'moduleStates'));
    }

    public function updateHospital(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        $request->validate([
            'hospital_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'currency' => 'nullable|string|max:10',
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
        $data = $request->only(['timezone', 'date_format', 'time_format', 'tax_label', 'tax_rate', 'low_stock_alert', 'low_stock_threshold']);
        HospitalSetting::current()->update($data);

        // Timezone drives now()/today() app-wide (see AppServiceProvider) and is cached.
        Cache::forget(AppServiceProvider::TIMEZONE_CACHE_KEY);

        return back()->with('success', 'System settings updated.');
    }

    /**
     * Enable/disable site-wide feature modules. Super admin only.
     */
    public function updateModules(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');
        abort_unless($request->user()->hasRole('super_admin'), 403);

        $enabled = (array) $request->input('modules', []);

        foreach (Modules::catalogue() as $key => $meta) {
            Module::updateOrCreate(
                ['key' => $key],
                ['name' => $meta['label'], 'enabled' => in_array($key, $enabled, true)]
            );
        }

        Modules::forget();

        return back()->with('success', 'Module settings updated.');
    }
}
