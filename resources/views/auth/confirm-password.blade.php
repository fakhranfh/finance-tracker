@extends('auth.master')

@section('title', 'Confirm Password')

@section('body_class', 'bg-background text-on-background font-body-md min-h-screen flex flex-col items-center justify-center p-gutter')

@section('auth-content')
<div class="text-center mb-space-xl">
    <h2 class="font-headline-md text-headline-md text-on-surface mb-space-xxs">Confirm your password</h2>
    <p class="mt-2 text-center font-body-sm text-body-sm text-secondary">
        This is a secure area of the application. Please confirm your password before continuing.
    </p>
</div>
@include('auth.success-and-error-alert')
<form class="space-y-6" method="POST" action="{{ route('password.confirm.store') }}">
    @csrf
    <div>
        <label class="block font-label-md text-label-md text-on-surface" for="password">Password</label>
        <div class="mt-2 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-outline" data-icon="lock">lock</span>
            </div>
            <input
                class="block w-full pl-10 pr-10 sm:text-sm border-outline-variant rounded-lg focus:ring-primary focus:border-primary text-on-surface bg-surface h-[44px] @error('password') border-error @enderror"
                id="password" name="password" placeholder="••••••••" required autofocus type="password">
        </div>
        @error('password')
            <p class="mt-2 font-body-sm text-body-sm text-error">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <button
            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm font-label-md text-label-md text-on-primary bg-primary hover:bg-on-primary-fixed-variant focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors h-[44px] items-center"
            type="submit">
            Confirm
        </button>
    </div>
</form>
@endsection
