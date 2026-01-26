<div>
    <admin:page-header :title="__('hub::hub.settings.title')" :description="__('hub::hub.settings.subtitle')" />

    {{-- Settings card with sidebar --}}
    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
        <div class="flex flex-col md:flex-row md:-mr-px">

            {{-- Sidebar navigation --}}
            <div class="flex flex-nowrap overflow-x-scroll no-scrollbar md:block md:overflow-auto px-3 py-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700/60 min-w-60 md:space-y-3">
                {{-- Account settings group --}}
                <div>
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 hidden md:block">{{ __('hub::hub.settings.sections.profile.title') }}</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'profile')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'profile',
                                ])
                            >
                                <svg class="shrink-0 fill-current mr-2 {{ $activeSection === 'profile' ? 'text-violet-400' : 'text-gray-400 dark:text-gray-500' }}" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M8 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm-5.143 7.91a1 1 0 1 1-1.714-1.033A7.996 7.996 0 0 1 8 10a7.996 7.996 0 0 1 6.857 3.877 1 1 0 1 1-1.714 1.032A5.996 5.996 0 0 0 8 12a5.996 5.996 0 0 0-5.143 2.91Z" />
                                </svg>
                                <span class="text-sm font-medium {{ $activeSection === 'profile' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">{{ __('hub::hub.settings.nav.profile') }}</span>
                            </button>
                        </li>
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'preferences')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'preferences',
                                ])
                            >
                                <svg class="shrink-0 fill-current mr-2 {{ $activeSection === 'preferences' ? 'text-violet-400' : 'text-gray-400 dark:text-gray-500' }}" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M10.5 1a.5.5 0 0 1 .5.5v1.567a6.5 6.5 0 1 1-7.77 7.77H1.5a.5.5 0 0 1 0-1h1.77a6.5 6.5 0 0 1 6.24-6.24V1.5a.5.5 0 0 1 .5-.5Zm-.5 3.073V5.5a.5.5 0 0 0 1 0V4.51a5.5 5.5 0 1 1-5.49 5.49H5.5a.5.5 0 0 0 0-1H4.073A5.5 5.5 0 0 1 10 4.073ZM8 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
                                </svg>
                                <span class="text-sm font-medium {{ $activeSection === 'preferences' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">{{ __('hub::hub.settings.nav.preferences') }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Security settings group --}}
                <div class="md:mt-6">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 hidden md:block">{{ __('hub::hub.settings.nav.security') }}</div>
                    <ul class="flex flex-nowrap md:block mr-3 md:mr-0">
                        @if($isTwoFactorEnabled)
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'two_factor')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'two_factor',
                                ])
                            >
                                <svg class="shrink-0 fill-current mr-2 {{ $activeSection === 'two_factor' ? 'text-violet-400' : 'text-gray-400 dark:text-gray-500' }}" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M8 0a1 1 0 0 1 1 1v.07A7.002 7.002 0 0 1 15 8a7 7 0 0 1-14 0 7.002 7.002 0 0 1 6-6.93V1a1 1 0 0 1 1-1Zm0 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm0 2a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z" />
                                </svg>
                                <span class="text-sm font-medium {{ $activeSection === 'two_factor' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">2FA</span>
                            </button>
                        </li>
                        @endif
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'password')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' => $activeSection === 'password',
                                ])
                            >
                                <svg class="shrink-0 fill-current mr-2 {{ $activeSection === 'password' ? 'text-violet-400' : 'text-gray-400 dark:text-gray-500' }}" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M11.5 0A2.5 2.5 0 0 0 9 2.5V4H2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1V2.5A2.5 2.5 0 0 0 10.5 0h-1ZM10 4V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5V4h-2ZM8 10a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z" />
                                </svg>
                                <span class="text-sm font-medium {{ $activeSection === 'password' ? 'text-violet-500 dark:text-violet-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">{{ __('hub::hub.settings.nav.password') }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Danger zone --}}
                @if($isDeleteAccountEnabled)
                <div class="md:mt-6">
                    <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase mb-3 hidden md:block">{{ __('hub::hub.settings.nav.danger_zone') }}</div>
                    <ul class="flex flex-nowrap md:block">
                        <li class="mr-0.5 md:mr-0 md:mb-0.5">
                            <button
                                wire:click="$set('activeSection', 'delete')"
                                @class([
                                    'flex items-center px-2.5 py-2 rounded-lg whitespace-nowrap w-full text-left',
                                    'bg-gradient-to-r from-red-500/[0.12] dark:from-red-500/[0.24] to-red-500/[0.04]' => $activeSection === 'delete',
                                ])
                            >
                                <svg class="shrink-0 fill-current mr-2 {{ $activeSection === 'delete' ? 'text-red-400' : 'text-gray-400 dark:text-gray-500' }}" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M5 2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1h3a1 1 0 1 1 0 2h-.08l-.82 9.835A2 2 0 0 1 11.106 16H4.894a2 2 0 0 1-1.994-1.835L2.08 5H2a1 1 0 1 1 0-2h3V2Zm1 3v8a.5.5 0 0 0 1 0V5a.5.5 0 0 0-1 0Zm3 0v8a.5.5 0 0 0 1 0V5a.5.5 0 0 0-1 0Z" />
                                </svg>
                                <span class="text-sm font-medium {{ $activeSection === 'delete' ? 'text-red-500 dark:text-red-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200' }}">{{ __('hub::hub.settings.sections.delete_account.title') }}</span>
                            </button>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            {{-- Content panel --}}
            <div class="grow p-6">
                {{-- Profile Section --}}
                @if($activeSection === 'profile')
                    <form wire:submit="updateProfile">
                        <flux:fieldset>
                            <flux:legend>{{ __('hub::hub.settings.sections.profile.title') }}</flux:legend>
                            <flux:description>{{ __('hub::hub.settings.sections.profile.description') }}</flux:description>

                            <div class="space-y-4 mt-4">
                                <flux:input
                                    wire:model="name"
                                    :label="__('hub::hub.settings.fields.name')"
                                    :placeholder="__('hub::hub.settings.fields.name_placeholder')"
                                />

                                <flux:input
                                    type="email"
                                    wire:model="email"
                                    :label="__('hub::hub.settings.fields.email')"
                                    :placeholder="__('hub::hub.settings.fields.email_placeholder')"
                                />
                            </div>

                            <div class="flex justify-end mt-6">
                                <flux:button type="submit" variant="primary">
                                    {{ __('hub::hub.settings.actions.save_profile') }}
                                </flux:button>
                            </div>
                        </flux:fieldset>
                    </form>
                @endif

                {{-- Preferences Section --}}
                @if($activeSection === 'preferences')
                    <div>
                        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">{{ __('hub::hub.settings.sections.preferences.title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('hub::hub.settings.sections.preferences.description') }}</p>

                        <form wire:submit="updatePreferences" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.language') }}</flux:label>
                                    <flux:select wire:model="locale">
                                        @foreach($locales as $loc)
                                            <flux:select.option value="{{ $loc['long'] }}">{{ $loc['long'] }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="locale" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.timezone') }}</flux:label>
                                    <flux:select wire:model="timezone">
                                        @foreach($timezones as $group => $zones)
                                            <optgroup label="{{ $group }}">
                                                @foreach($zones as $zone => $label)
                                                    <flux:select.option value="{{ $zone }}">{{ $label }}</flux:select.option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </flux:select>
                                    <flux:error name="timezone" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.time_format') }}</flux:label>
                                    <flux:select wire:model="time_format">
                                        <flux:select.option value="12">{{ __('hub::hub.settings.fields.time_format_12') }}</flux:select.option>
                                        <flux:select.option value="24">{{ __('hub::hub.settings.fields.time_format_24') }}</flux:select.option>
                                    </flux:select>
                                    <flux:error name="time_format" />
                                </flux:field>

                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.week_starts_on') }}</flux:label>
                                    <flux:select wire:model="week_starts_on">
                                        <flux:select.option value="0">{{ __('hub::hub.settings.fields.week_sunday') }}</flux:select.option>
                                        <flux:select.option value="1">{{ __('hub::hub.settings.fields.week_monday') }}</flux:select.option>
                                    </flux:select>
                                    <flux:error name="week_starts_on" />
                                </flux:field>
                            </div>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">
                                    {{ __('hub::hub.settings.actions.save_preferences') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Two-Factor Authentication Section --}}
                @if($activeSection === 'two_factor' && $isTwoFactorEnabled)
                    <div>
                        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">{{ __('hub::hub.settings.sections.two_factor.title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('hub::hub.settings.sections.two_factor.description') }}</p>

                        @if(!$userHasTwoFactorEnabled && !$showTwoFactorSetup)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400">{{ __('hub::hub.settings.two_factor.not_enabled') }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">{{ __('hub::hub.settings.two_factor.not_enabled_description') }}</p>
                                </div>
                                <flux:button wire:click="enableTwoFactor" variant="primary">
                                    {{ __('hub::hub.settings.actions.enable') }}
                                </flux:button>
                            </div>
                        @endif

                        @if($showTwoFactorSetup)
                            <div class="space-y-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        {{ __('hub::hub.settings.two_factor.setup_instructions') }}
                                    </p>
                                    <div class="flex flex-col sm:flex-row items-center gap-6">
                                        <div class="bg-white p-4 rounded-lg">
                                            {!! $twoFactorQrCode !!}
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('hub::hub.settings.two_factor.secret_key') }}</p>
                                            <code class="block p-2 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono break-all">{{ $twoFactorSecretKey }}</code>
                                        </div>
                                    </div>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.verification_code') }}</flux:label>
                                    <flux:input wire:model="twoFactorCode" placeholder="{{ __('hub::hub.settings.fields.verification_code_placeholder') }}" maxlength="6" />
                                    <flux:error name="twoFactorCode" />
                                </flux:field>

                                <div class="flex gap-2">
                                    <flux:button wire:click="confirmTwoFactor" variant="primary">
                                        {{ __('hub::hub.settings.actions.confirm') }}
                                    </flux:button>
                                    <flux:button wire:click="$set('showTwoFactorSetup', false)" variant="ghost">
                                        {{ __('hub::hub.settings.actions.cancel') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif

                        @if($userHasTwoFactorEnabled && !$showTwoFactorSetup)
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                    <flux:icon name="shield-check" />
                                    <span class="font-medium">{{ __('hub::hub.settings.two_factor.enabled') }}</span>
                                </div>

                                @if($showRecoveryCodes && count($recoveryCodes) > 0)
                                    <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mb-3">
                                            <strong>{{ __('hub::hub.settings.two_factor.recovery_codes_warning') }}</strong>
                                        </p>
                                        <div class="grid grid-cols-2 gap-2 font-mono text-sm">
                                            @foreach($recoveryCodes as $code)
                                                <code class="p-2 bg-gray-100 dark:bg-gray-700 rounded">{{ $code }}</code>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="flex gap-2">
                                    <flux:button wire:click="showRecoveryCodesModal">
                                        {{ __('hub::hub.settings.actions.view_recovery_codes') }}
                                    </flux:button>
                                    <flux:button wire:click="regenerateRecoveryCodes">
                                        {{ __('hub::hub.settings.actions.regenerate_codes') }}
                                    </flux:button>
                                    <flux:button wire:click="disableTwoFactor" variant="danger">
                                        {{ __('hub::hub.settings.actions.disable') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Password Section --}}
                @if($activeSection === 'password')
                    <div>
                        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-1">{{ __('hub::hub.settings.sections.password.title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('hub::hub.settings.sections.password.description') }}</p>

                        <form wire:submit="updatePassword" class="space-y-4">
                            <flux:field>
                                <flux:label>{{ __('hub::hub.settings.fields.current_password') }}</flux:label>
                                <flux:input type="password" wire:model="current_password" viewable />
                                <flux:error name="current_password" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('hub::hub.settings.fields.new_password') }}</flux:label>
                                <flux:input type="password" wire:model="new_password" viewable />
                                <flux:error name="new_password" />
                            </flux:field>

                            <flux:field>
                                <flux:label>{{ __('hub::hub.settings.fields.confirm_password') }}</flux:label>
                                <flux:input type="password" wire:model="new_password_confirmation" viewable />
                                <flux:error name="new_password_confirmation" />
                            </flux:field>

                            <div class="flex justify-end">
                                <flux:button type="submit" variant="primary">
                                    {{ __('hub::hub.settings.actions.update_password') }}
                                </flux:button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Delete Account Section --}}
                @if($activeSection === 'delete' && $isDeleteAccountEnabled)
                    <div>
                        <h2 class="text-2xl text-red-600 dark:text-red-400 font-bold mb-1">{{ __('hub::hub.settings.sections.delete_account.title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('hub::hub.settings.sections.delete_account.description') }}</p>

                        @if($pendingDeletion)
                            {{-- Pending Deletion State --}}
                            <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg mb-4">
                                <div class="flex items-start gap-3">
                                    <flux:icon name="clock" class="text-red-500 mt-0.5" />
                                    <div class="flex-1">
                                        <p class="font-medium text-red-600 dark:text-red-400">{{ __('hub::hub.settings.delete.scheduled_title') }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ __('hub::hub.settings.delete.scheduled_description', ['date' => $pendingDeletion->expires_at->format('F j, Y \a\t g:i A'), 'days' => $pendingDeletion->daysRemaining()]) }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                                            {{ __('hub::hub.settings.delete.scheduled_email_note') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <flux:button wire:click="cancelAccountDeletion" icon="x-mark">
                                {{ __('hub::hub.settings.actions.cancel_deletion') }}
                            </flux:button>
                        @elseif($showDeleteConfirmation)
                            {{-- Confirmation Form --}}
                            <div class="space-y-4">
                                <div class="p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
                                    <p class="text-sm text-red-600 dark:text-red-400 font-medium mb-2">
                                        <flux:icon name="exclamation-triangle" class="inline mr-1" /> {{ __('hub::hub.settings.delete.warning_title') }}
                                    </p>
                                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 ml-5 list-disc">
                                        <li>{{ __('hub::hub.settings.delete.warning_delay') }}</li>
                                        <li>{{ __('hub::hub.settings.delete.warning_workspaces') }}</li>
                                        <li>{{ __('hub::hub.settings.delete.warning_content') }}</li>
                                        <li>{{ __('hub::hub.settings.delete.warning_email') }}</li>
                                    </ul>
                                </div>

                                <flux:field>
                                    <flux:label>{{ __('hub::hub.settings.fields.delete_reason') }}</flux:label>
                                    <flux:textarea wire:model="deleteReason" placeholder="{{ __('hub::hub.settings.fields.delete_reason_placeholder') }}" rows="2" />
                                </flux:field>

                                <div class="flex gap-2">
                                    <flux:button wire:click="requestAccountDeletion" variant="danger" icon="trash">
                                        {{ __('hub::hub.settings.actions.request_deletion') }}
                                    </flux:button>
                                    <flux:button wire:click="$set('showDeleteConfirmation', false)" variant="ghost">
                                        {{ __('hub::hub.settings.actions.cancel') }}
                                    </flux:button>
                                </div>
                            </div>
                        @else
                            {{-- Initial State --}}
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                {{ __('hub::hub.settings.delete.initial_description') }}
                            </p>
                            <flux:button wire:click="$set('showDeleteConfirmation', true)" variant="danger" icon="trash">
                                {{ __('hub::hub.settings.actions.delete_account') }}
                            </flux:button>
                        @endif
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
