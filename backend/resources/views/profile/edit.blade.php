@extends('layouts.app')
@section('title', 'My profile')
@section('content')
<div class="max-w-3xl mx-auto p-8 space-y-6">
    <h1 class="text-3xl font-bold">My profile</h1>

    @if(session('status') === 'profile-updated')
        <div class="bg-green-100 text-green-700 p-3 rounded">Profile saved.</div>
    @endif
    @if(session('status') === 'password-updated')
        <div class="bg-green-100 text-green-700 p-3 rounded">Password changed.</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PATCH')
        <h2 class="text-xl font-bold">Name and email</h2>
        <div>
            <label class="block mb-2 font-semibold">Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded p-3" required>
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded p-3" required>
            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Save</button>
    </form>

    <form method="POST" action="{{ route('password.update') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-xl font-bold">Change password</h2>
        <div>
            <label class="block mb-2 font-semibold">Current password</label>
            <input type="password" name="current_password" autocomplete="current-password" class="w-full border rounded p-3" required>
            @error('current_password', 'updatePassword')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block mb-2 font-semibold">New password</label>
            <input type="password" name="password" autocomplete="new-password" class="w-full border rounded p-3" required>
            @error('password', 'updatePassword')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block mb-2 font-semibold">Confirm new password</label>
            <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border rounded p-3" required>
        </div>
        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Change password</button>
    </form>
</div>
@endsection
