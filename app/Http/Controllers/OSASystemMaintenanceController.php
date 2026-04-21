<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class OSASystemMaintenanceController extends Controller
{
    protected array $mailKeys = [
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'mail_default',
    ];

    protected array $availableCommands = [
        'promissory-notes:check-signature-deadline' => 'Check promissory note signature deadlines',
        'promissory-notes:process-delinquency' => 'Process promissory note delinquency',
        'cache:clear' => 'Clear application cache',
        'config:clear' => 'Clear configuration cache',
        'route:clear' => 'Clear route cache',
        'view:clear' => 'Clear compiled views',
    ];

    public function index()
    {
        $settings = SystemSetting::getKeyValuePairs($this->mailKeys);

        return view('osa.system-maintenance', [
            'settings' => $settings,
            'availableCommands' => $this->availableCommands,
        ]);
    }

    public function saveEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:20',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_default' => 'required|string|in:smtp,log',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                foreach (['mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name', 'mail_default'] as $key) {
                    SystemSetting::setValue($key, $validated[$key]);
                }

                if (filled($validated['mail_password'])) {
                    SystemSetting::setValue('mail_password', $validated['mail_password']);
                }
            });
        } catch (\Throwable $e) {
            return Redirect::route('osa.system-maintenance')
                ->with('error', 'Failed to save SMTP settings: ' . $e->getMessage());
        }

        return Redirect::route('osa.system-maintenance')
            ->with('success', 'SMTP settings saved successfully.');
    }

    public function executeConsoleCommand(Request $request)
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'in:' . implode(',', array_keys($this->availableCommands))],
            'as_of' => 'nullable|date',
        ]);

        $arguments = [];

        if ($validated['command'] === 'promissory-notes:process-delinquency' && filled($validated['as_of'])) {
            $arguments['--as-of'] = $validated['as_of'];
        }

        try {
            $status = Artisan::call($validated['command'], $arguments);
            $output = trim(Artisan::output());

            $message = $status === 0
                ? 'Command executed successfully.'
                : "Command completed with exit code {$status}.";

            return Redirect::route('osa.system-maintenance')
                ->with($status === 0 ? 'success' : 'error', $message)
                ->with('command_output', $output ?: 'Command completed without output.');
        } catch (\Throwable $e) {
            return Redirect::route('osa.system-maintenance')
                ->with('error', 'Command failed: ' . $e->getMessage())
                ->with('command_output', trim(Artisan::output()) ?: 'No output available.');
        }
    }
}
