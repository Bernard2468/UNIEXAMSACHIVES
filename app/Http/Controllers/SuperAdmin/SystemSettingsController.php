<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class SystemSettingsController extends Controller
{
    /**
     * Display system settings
     * Only show settings that are defined in the SuperAdminSystemSeeder
     */
    public function index()
    {
        // List of allowed setting keys from SuperAdminSystemSeeder
        $allowedKeys = [
            'paystack_public_key',
            'paystack_secret_key',
            'paystack_webhook_secret',
            'subscription_base_price',
            'subscription_grace_period_days',
            'auto_renewal_enabled',
            'renewal_reminder_days',
            'system_name',
            'system_email',
            'send_renewal_emails',
            'send_payment_receipts',
            'default_currency',
        ];

        $settings = SystemSetting::whereIn('key', $allowedKeys)
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');

        return view('super-admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $updated = 0;
        $requiresRestart = false;

        // Handle renewal_reminder_days separately (comes as array)
        if ($request->has('renewal_reminder_days') && is_array($request->renewal_reminder_days)) {
            // Process each value - convert empty strings to 0, then validate
            $days = [];
            foreach ($request->renewal_reminder_days as $day) {
                $day = trim($day);
                if ($day === '' || $day === null) {
                    continue; // Skip empty values
                }
                if (is_numeric($day) && $day >= 0 && $day <= 365) {
                    $days[] = (int) $day;
                }
            }
            
            // Ensure we have exactly 4 values, pad with defaults if needed
            $defaults = [30, 14, 7, 1];
            while (count($days) < 4) {
                $days[] = $defaults[count($days)];
            }
            
            $days = array_slice($days, 0, 4); // Take only first 4
            // Don't sort - preserve user's order
            
            $setting = SystemSetting::where('key', 'renewal_reminder_days')->first();
            if ($setting && $setting->is_editable) {
                // Pass the array directly - setTypedValue will handle JSON encoding
                $oldValue = $setting->value;
                $setting->setTypedValue($days);
                $setting->updated_by = auth()->id();
                $setting->save();
                
                // Clear cache
                \Illuminate\Support\Facades\Cache::forget("system_setting_renewal_reminder_days");
                
                if ($oldValue !== $setting->value) {
                    $updated++;
                }
            }
        }

        foreach ($request->all() as $key => $value) {
            // Skip CSRF token, renewal_reminder_days (handled above), and other non-setting fields
            if (in_array($key, ['_token', '_method', 'renewal_reminder_days'])) {
                continue;
            }

            $setting = SystemSetting::where('key', $key)->first();

            if ($setting && $setting->is_editable) {
                // Handle boolean values
                if ($setting->data_type === 'boolean') {
                    $value = $request->has($key) ? true : false;
                }

                // Update setting
                $oldValue = $setting->value;
                SystemSetting::set($key, $value, auth()->id());

                if ($oldValue !== $value) {
                    $updated++;

                    if ($setting->requires_restart) {
                        $requiresRestart = true;
                    }
                }
            }
        }

        // Clear application cache if any settings changed
        if ($updated > 0) {
            Cache::flush();
        }

        $message = "{$updated} setting(s) updated successfully.";
        
        if ($requiresRestart) {
            $message .= " Note: Some changes may require application restart to take effect.";
        }

        return back()->with('success', $message);
    }

    /**
     * Test Paystack connection
     */
    public function testPaystack()
    {
        $publicKey = SystemSetting::getPaystackPublicKey();
        $secretKey = SystemSetting::getPaystackSecretKey();

        if (empty($publicKey) || empty($secretKey)) {
            return back()->with('error', 'Paystack keys are not configured.');
        }

        try {
            // Test API connection by fetching available payment methods
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get('https://api.paystack.co/bank');

            if ($response->successful()) {
                return back()->with('success', 'Paystack connection successful! API keys are valid.');
            }

            return back()->with('error', 'Paystack connection failed: ' . ($response->json()['message'] ?? 'Unknown error'));

        } catch (\Exception $e) {
            return back()->with('error', 'Paystack connection error: ' . $e->getMessage());
        }
    }

    /**
     * Enable/disable maintenance mode
     */
    public function toggleMaintenanceMode(Request $request)
    {
        $enabled = $request->has('enable');

        SystemSetting::setMaintenanceMode($enabled, auth()->id());

        $message = $enabled 
            ? 'Maintenance mode enabled. Users will see a maintenance page.'
            : 'Maintenance mode disabled. System is now accessible to all users.';

        return back()->with('success', $message);
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return back()->with('success', 'All caches cleared successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cache clearing failed: ' . $e->getMessage());
        }
    }

    /**
     * Run application optimization
     */
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return back()->with('success', 'Application optimized successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Export settings as JSON
     */
    public function export()
    {
        $settings = SystemSetting::all()->map(function($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->typed_value,
                'category' => $setting->category,
                'data_type' => $setting->data_type,
                'label' => $setting->label,
                'description' => $setting->description,
            ];
        });

        $filename = 'system_settings_' . now()->format('Y-m-d_His') . '.json';

        return response()->json($settings, 200, [
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import settings from JSON
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json|max:2048',
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getRealPath());
            $settings = json_decode($content, true);

            if (!is_array($settings)) {
                return back()->with('error', 'Invalid settings file format.');
            }

            $imported = 0;
            foreach ($settings as $settingData) {
                if (isset($settingData['key']) && isset($settingData['value'])) {
                    $setting = SystemSetting::where('key', $settingData['key'])->first();
                    
                    if ($setting && $setting->is_editable) {
                        SystemSetting::set($settingData['key'], $settingData['value'], auth()->id());
                        $imported++;
                    }
                }
            }

            Cache::flush();

            return back()->with('success', "{$imported} settings imported successfully.");

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to default
     */
    public function reset(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'confirm' => 'required|accepted',
        ]);

        $settings = SystemSetting::where('category', $request->category)
            ->where('is_editable', true)
            ->get();

        $reset = 0;
        foreach ($settings as $setting) {
            if ($setting->default_value !== null) {
                SystemSetting::set($setting->key, $setting->default_value, auth()->id());
                $reset++;
            }
        }

        Cache::flush();

        return back()->with('success', "{$reset} settings reset to default values.");
    }
}

