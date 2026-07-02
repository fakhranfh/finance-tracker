@extends('layouts.app')

@section('title', 'Change Password')

@php
    $topbarTitle = 'Change Password';
@endphp

@section('app-content')
    <div class="flex items-center justify-center py-space-xl px-gutter">
        <div class="w-full max-w-2xl">
            @include('auth.success-and-error-alert')

            <div class="bg-surface rounded-xl border border-outline-variant p-space-lg md:p-space-xl shadow-[0_2px_8px_rgba(0,0,0,0.06)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.1)] transition-shadow duration-200 password-card">
                <div class="relative z-10">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-space-xs">Change Password</h2>
                    <p class="font-body-md text-body-md text-secondary mb-space-xl">Update your password to keep your account secure. Use a strong, unique password.</p>

                    <!-- Form -->
                    <form class="space-y-space-lg" method="POST" action="/user/password">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="space-y-space-xs">
                            <label class="font-label-md text-label-md text-on-surface" for="current_password">Current Password</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary/60 text-[20px]">lock</span>
                                <input
                                    class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface font-body-md text-body-md rounded-lg py-space-sm pl-10 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('current_password') border-error @enderror"
                                    id="current_password"
                                    name="current_password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary/60 hover:text-secondary transition-colors"
                                    onclick="togglePasswordVisibility('current_password')"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[20px]" id="current_password_icon">visibility</span>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-error text-body-sm font-body-sm mt-space-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="space-y-space-xs">
                            <label class="font-label-md text-label-md text-on-surface" for="password">New Password</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary/60 text-[20px]">lock_reset</span>
                                <input
                                    class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface font-body-md text-body-md rounded-lg py-space-sm pl-10 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password') border-error @enderror"
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary/60 hover:text-secondary transition-colors"
                                    onclick="togglePasswordVisibility('password')"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[20px]" id="password_icon">visibility</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-error text-body-sm font-body-sm mt-space-xs">{{ $message }}</p>
                            @enderror
                            <div class="mt-space-sm">
                                <div class="password-strength" id="passwordStrength"></div>
                                <p class="text-body-sm text-secondary mt-space-xs">Use at least 8 characters with uppercase, lowercase, numbers, and symbols.</p>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-space-xs">
                            <label class="font-label-md text-label-md text-on-surface" for="password_confirmation">Confirm Password</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-secondary/60 text-[20px]">verified</span>
                                <input
                                    class="w-full bg-surface-container-lowest border border-outline-variant text-on-surface font-body-md text-body-md rounded-lg py-space-sm pl-10 pr-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all @error('password_confirmation') border-error @enderror"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    required
                                />
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary/60 hover:text-secondary transition-colors"
                                    onclick="togglePasswordVisibility('password_confirmation')"
                                    tabindex="-1"
                                >
                                    <span class="material-symbols-outlined text-[20px]" id="password_confirmation_icon">visibility</span>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="text-error text-body-sm font-body-sm mt-space-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Security Tips -->
                        <div class="bg-primary/5 border border-primary/20 rounded-lg p-space-md">
                            <div class="flex gap-space-md">
                                <span class="material-symbols-outlined text-primary flex-shrink-0 mt-space-xxs">security</span>
                                <div>
                                    <p class="font-label-md text-label-md text-on-surface">Password Security Tips</p>
                                    <ul class="text-body-sm text-secondary mt-space-xs space-y-space-xs">
                                        <li>• Use a unique password not used on other accounts</li>
                                        <li>• Include uppercase, lowercase, numbers, and symbols</li>
                                        <li>• Avoid using personal information (name, birthdate, etc.)</li>
                                        <li>• Never share your password with anyone</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-space-lg mt-space-lg border-t border-outline-variant flex justify-end gap-space-md">
                            <a href="{{ route('dashboard') }}" class="px-space-lg py-space-sm rounded-lg border border-outline-variant bg-surface text-on-surface font-label-md text-label-md hover:bg-surface-container-low transition-colors inline-block">
                                Cancel
                            </a>
                            <button id="update-password-btn" class="px-space-lg py-space-sm rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-on-primary-fixed-variant transition-colors flex items-center gap-space-sm shadow-sm" type="submit">
                                <span id="submit-icon" class="material-symbols-outlined text-[18px]">check_circle</span>
                                <span id="submit-text">Update Password</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .password-card {
                position: relative;
                overflow: hidden;
            }

            .password-card::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 300px;
                height: 300px;
                background: linear-gradient(135deg, #004ac6, #2563eb);
                border-radius: 50%;
                opacity: 0.05;
                pointer-events: none;
            }

            .password-strength {
                height: 6px;
                border-radius: 3px;
                transition: all 0.3s ease;
            }

            .password-strength.weak {
                background-color: rgb(239, 68, 68);
                width: 33%;
            }

            .password-strength.medium {
                background-color: rgb(245, 158, 11);
                width: 66%;
            }

            .password-strength.strong {
                background-color: rgb(34, 197, 94);
                width: 100%;
            }

            @keyframes spin {
                from {
                    transform: rotate(0deg);
                }
                to {
                    transform: rotate(360deg);
                }
            }

            .animate-spin {
                animation: spin 1s linear infinite;
            }

            #update-password-btn:disabled {
                background-color: #a1a7b8;
                color: #ffffff;
                cursor: not-allowed;
                opacity: 0.7;
            }

            #update-password-btn:disabled:hover {
                background-color: #a1a7b8;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('passwordStrength');

            function calculatePasswordStrength(password) {
                let strength = 0;

                // Length check
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;

                // Character variety checks
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;

                return strength;
            }

            function togglePasswordVisibility(fieldId) {
                const field = document.getElementById(fieldId);
                const icon = document.getElementById(fieldId + '_icon');

                if (field.type === 'password') {
                    field.type = 'text';
                    icon.textContent = 'visibility_off';
                } else {
                    field.type = 'password';
                    icon.textContent = 'visibility';
                }
            }

            passwordInput.addEventListener('input', function() {
                const strength = calculatePasswordStrength(this.value);

                // Remove all strength classes
                strengthBar.classList.remove('weak', 'medium', 'strong');

                if (this.value.length === 0) {
                    strengthBar.style.width = '0';
                } else if (strength <= 2) {
                    strengthBar.classList.add('weak');
                } else if (strength <= 4) {
                    strengthBar.classList.add('medium');
                } else {
                    strengthBar.classList.add('strong');
                }
            });

            const btn = document.getElementById('update-password-btn');
            const icon = document.getElementById('submit-icon');
            const text = document.getElementById('submit-text');

            btn?.addEventListener('click', () => {
                if (!btn.form.checkValidity()) return;
                
                icon.textContent = 'hourglass_empty';
                icon.classList.add('animate-spin');
                text.textContent = 'Updating...';
                btn.disabled = true;
                btn.form.submit();
            });
        </script>
    @endpush
@endsection
