<?php

namespace Website\Hub\View\Modal\Admin;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Core\Mod\Social\Models\Setting;
use Core\Mod\Tenant\Mail\AccountDeletionRequested;
use Core\Mod\Tenant\Models\AccountDeletionRequest;
use Core\Mod\Tenant\Models\User;
use Core\Mod\Tenant\Services\UserStatsService;

class Settings extends Component
{
    // Active section for sidebar navigation
    #[Url(as: 'tab')]
    public string $activeSection = 'profile';

    // Profile Info
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    // Preferences
    public string $locale = '';

    public string $timezone = '';

    public int $time_format = 12;

    public int $week_starts_on = 1;

    // Password Change
    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    // Two-Factor Authentication
    public bool $showTwoFactorSetup = false;

    public ?string $twoFactorQrCode = null;

    public ?string $twoFactorSecretKey = null;

    public string $twoFactorCode = '';

    public array $recoveryCodes = [];

    public bool $showRecoveryCodes = false;

    // Feature flags
    public bool $isTwoFactorEnabled = false;

    public bool $userHasTwoFactorEnabled = false;

    public bool $isDeleteAccountEnabled = true; // Always enabled with our GDPR flow

    // Account Deletion
    public bool $showDeleteConfirmation = false;

    public string $deleteReason = '';

    public ?AccountDeletionRequest $pendingDeletion = null;

    // Data
    public array $locales = [];

    public array $timezones = [];

    public function mount(): void
    {
        $user = User::findOrFail(Auth::id());

        // Profile Info
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';

        // Load preferences from user settings
        $this->locale = $this->getUserSetting('locale', config('app.locale', 'en_GB'));
        $this->timezone = $this->getUserSetting('timezone', config('app.timezone', 'Europe/London'));
        $this->time_format = (int) $this->getUserSetting('time_format', 12);
        $this->week_starts_on = (int) $this->getUserSetting('week_starts_on', 1);

        // Feature flags - 2FA disabled until native implementation
        $this->isTwoFactorEnabled = config('social.features.two_factor_auth', false);
        $this->userHasTwoFactorEnabled = method_exists($user, 'hasTwoFactorAuthEnabled')
            ? $user->hasTwoFactorAuthEnabled()
            : false;

        // Check for pending deletion request
        $this->pendingDeletion = AccountDeletionRequest::where('user_id', $user->id)
            ->whereNull('completed_at')
            ->whereNull('cancelled_at')
            ->first();

        // Data for selects (cached for performance)
        $this->locales = UserStatsService::getLocaleList();
        $this->timezones = UserStatsService::getTimezoneList();
    }

    protected function getUserSetting(string $name, mixed $default = null): mixed
    {
        $setting = Setting::where('user_id', Auth::id())
            ->where('name', $name)
            ->first();

        return $setting?->payload ?? $default;
    }

    public function updateProfile(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:'.(new User)->getTable().',email,'.Auth::id()],
        ]);

        $user = User::findOrFail(Auth::id());
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('profile-updated');
        Flux::toast(text: __('hub::hub.settings.messages.profile_updated'), variant: 'success');
    }

    public function updatePreferences(): void
    {
        $this->validate([
            'locale' => ['required', 'string'],
            'timezone' => ['required', 'timezone'],
            'time_format' => ['required', 'in:12,24'],
            'week_starts_on' => ['required', 'in:0,1'],
        ]);

        $preferences = [
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'time_format' => (int) $this->time_format,
            'week_starts_on' => (int) $this->week_starts_on,
        ];

        foreach ($preferences as $name => $payload) {
            Setting::updateOrCreate(
                ['name' => $name, 'user_id' => Auth::id()],
                ['payload' => $payload]
            );
        }

        $this->dispatch('preferences-updated');
        Flux::toast(text: __('hub::hub.settings.messages.preferences_updated'), variant: 'success');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::findOrFail(Auth::id());
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->dispatch('password-updated');
        Flux::toast(text: __('hub::hub.settings.messages.password_updated'), variant: 'success');
    }

    public function enableTwoFactor(): void
    {
        // TODO: Implement native 2FA - currently disabled
        Flux::toast(text: __('hub::hub.settings.messages.two_factor_upgrading'), variant: 'warning');
    }

    public function confirmTwoFactor(): void
    {
        // TODO: Implement native 2FA - currently disabled
        Flux::toast(text: __('hub::hub.settings.messages.two_factor_upgrading'), variant: 'warning');
    }

    public function showRecoveryCodesModal(): void
    {
        // TODO: Implement native 2FA - currently disabled
        Flux::toast(text: __('hub::hub.settings.messages.two_factor_upgrading'), variant: 'warning');
    }

    public function regenerateRecoveryCodes(): void
    {
        // TODO: Implement native 2FA - currently disabled
        Flux::toast(text: __('hub::hub.settings.messages.two_factor_upgrading'), variant: 'warning');
    }

    public function disableTwoFactor(): void
    {
        // TODO: Implement native 2FA - currently disabled
        Flux::toast(text: __('hub::hub.settings.messages.two_factor_upgrading'), variant: 'warning');
    }

    public function requestAccountDeletion(): void
    {
        // Get the base user model for the app
        $user = \Core\Mod\Tenant\Models\User::findOrFail(Auth::id());

        // Create the deletion request
        $deletionRequest = AccountDeletionRequest::createForUser($user, $this->deleteReason ?: null);

        // Send confirmation email
        Mail::to($user->email)->send(new AccountDeletionRequested($deletionRequest));

        $this->pendingDeletion = $deletionRequest;
        $this->showDeleteConfirmation = false;
        $this->deleteReason = '';

        Flux::toast(text: __('hub::hub.settings.messages.deletion_scheduled'), variant: 'warning');
    }

    public function cancelAccountDeletion(): void
    {
        if ($this->pendingDeletion) {
            $this->pendingDeletion->cancel();
            $this->pendingDeletion = null;
        }

        Flux::toast(text: __('hub::hub.settings.messages.deletion_cancelled'), variant: 'success');
    }

    public function render()
    {
        return view('hub::admin.settings')
            ->layout('hub::admin.layouts.app', ['title' => 'Settings']);
    }
}
