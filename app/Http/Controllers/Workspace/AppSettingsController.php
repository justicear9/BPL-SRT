<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\UpdateAppSettingsRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AppSettingsController extends Controller
{
    public function edit(): View
    {
        return view('content.workspace.settings.edit', [
            'currencyCode' => Setting::currencyCode(),
            'currencySymbol' => Setting::currencySymbol(),
            'currencyOptions' => Setting::currencyCodeOptions(),
        ]);
    }

    public function update(UpdateAppSettingsRequest $request): RedirectResponse
    {
        $request->saveSettings();

        return redirect()->route('workspace.settings.edit')
            ->with('status', __('Settings saved.'));
    }
}
