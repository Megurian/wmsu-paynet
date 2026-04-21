@extends('layouts.dashboard')

@section('title', 'OSA System Maintenance')
@section('page-title', 'System Maintenance')

@section('content')
<div class="space-y-8">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 p-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 p-4">
            {{ session('error') }}
        </div>
    @endif

    @if(session('command_output'))
        <div class="rounded-xl bg-slate-50 border border-slate-200 text-slate-800 p-4 whitespace-pre-wrap font-mono text-sm">
            <div class="font-semibold mb-2">Command output</div>
            {{ session('command_output') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl bg-white shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Console Commands</h2>
                    <p class="text-sm text-gray-500">Run existing artisan commands from the OSA dashboard.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Debug</span>
            </div>

            <form action="{{ route('osa.system-maintenance.execute-command') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="command" class="block text-sm font-medium text-gray-700">Select command</label>
                    <select id="command" name="command" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700">
                        @foreach($availableCommands as $signature => $label)
                            <option value="{{ $signature }}" @selected(old('command') === $signature)>{{ $label }} ({{ $signature }})</option>
                        @endforeach
                    </select>
                    @error('command')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div id="asOfContainer" class="hidden">
                    <label for="as_of" class="block text-sm font-medium text-gray-700">As of date</label>
                    <input id="as_of" name="as_of" type="date" value="{{ old('as_of') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    <p class="text-xs text-gray-500 mt-1">Optional for <code>promissory-notes:process-delinquency</code> backfill/testing.</p>
                    @error('as_of')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">Run command</button>
            </form>
        </section>

        <section class="rounded-xl bg-white shadow-sm p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">SMTP Email Configuration</h2>
                    <p class="text-sm text-gray-500">Update mail settings without editing <code>.env</code>.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Email</span>
            </div>

            <form action="{{ route('osa.system-maintenance.email-settings') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="mail_default" class="block text-sm font-medium text-gray-700">Default mailer</label>
                    <select id="mail_default" name="mail_default" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700">
                        <option value="smtp" @selected(old('mail_default', $settings['mail_default'] ?? config('mail.default')) === 'smtp')>SMTP</option>
                        <option value="log" @selected(old('mail_default', $settings['mail_default'] ?? config('mail.default')) === 'log')>Log</option>
                    </select>
                    @error('mail_default')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_host" class="block text-sm font-medium text-gray-700">Mail host</label>
                    <input id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? config('mail.mailers.smtp.host')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    @error('mail_host')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_port" class="block text-sm font-medium text-gray-700">Mail port</label>
                    <input id="mail_port" name="mail_port" type="number" value="{{ old('mail_port', $settings['mail_port'] ?? config('mail.mailers.smtp.port')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    @error('mail_port')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_username" class="block text-sm font-medium text-gray-700">SMTP username</label>
                    <input id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? config('mail.mailers.smtp.username')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    @error('mail_username')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_password" class="block text-sm font-medium text-gray-700">SMTP password</label>
                    <input id="mail_password" name="mail_password" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    <p class="text-xs text-gray-500 mt-1">Leave blank to keep the current password.</p>
                    @error('mail_password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                    <select id="mail_encryption" name="mail_encryption" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700">
                        @php $encryption = old('mail_encryption', $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption')); @endphp
                        <option value="" @selected($encryption === null || $encryption === '')>None</option>
                        <option value="tls" @selected($encryption === 'tls')>TLS</option>
                        <option value="ssl" @selected($encryption === 'ssl')>SSL</option>
                        <option value="starttls" @selected($encryption === 'starttls')>STARTTLS</option>
                    </select>
                    @error('mail_encryption')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_from_address" class="block text-sm font-medium text-gray-700">From address</label>
                    <input id="mail_from_address" name="mail_from_address" type="email" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? config('mail.from.address')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    @error('mail_from_address')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_from_name" class="block text-sm font-medium text-gray-700">From name</label>
                    <input id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? config('mail.from.name')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-700 focus:ring-red-700" />
                    @error('mail_from_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-800">Save SMTP settings</button>
                </div>
            </form>
        </section>
    </div>
</div>

<script>
    const commandSelect = document.getElementById('command');
    const asOfContainer = document.getElementById('asOfContainer');

    function updateAsOfVisibility() {
        asOfContainer.classList.toggle('hidden', commandSelect.value !== 'promissory-notes:process-delinquency');
    }

    commandSelect.addEventListener('change', updateAsOfVisibility);
    updateAsOfVisibility();
</script>
@endsection

