@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="max-w-3xl mx-auto p-8">
    <h1 class="text-3xl font-bold mb-6">Settings</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-xl font-bold">Company</h2>

        <div>
            <label class="block mb-2 font-semibold">Company name</label>
            <input type="text" name="company_name" value="{{ old('company_name', $values['company_name']) }}" class="w-full border rounded p-3" required>
            @error('company_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            <p class="text-sm text-gray-500 mt-1">Shown at the top of the sidebar.</p>
        </div>
        <div>
            <label class="block mb-2 font-semibold">Email</label>
            <input type="email" name="company_email" value="{{ old('company_email', $values['company_email']) }}" class="w-full border rounded p-3">
            @error('company_email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block mb-2 font-semibold">Phone</label>
            <input type="text" name="company_phone" value="{{ old('company_phone', $values['company_phone']) }}" class="w-full border rounded p-3">
        </div>
        <div>
            <label class="block mb-2 font-semibold">Address</label>
            <textarea name="company_address" rows="3" class="w-full border rounded p-3">{{ old('company_address', $values['company_address']) }}</textarea>
        </div>

        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded">Save settings</button>
    </form>

    <div class="bg-white rounded-lg shadow p-6 mt-6">
        <h2 class="text-xl font-bold mb-2">Your account</h2>
        <p class="text-gray-600 mb-3">Change your own name, email or password.</p>
        <a href="{{ route('profile.edit') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Open profile</a>
    </div>
</div>
@endsection
