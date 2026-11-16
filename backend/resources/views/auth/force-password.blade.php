<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Set your own password before you continue. Use at least 8 characters with letters and numbers.
    </div>

    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf

        <div>
            <x-input-label for="password" value="New password" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autofocus autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm new password" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
        </div>

        <div class="mt-6 flex items-center justify-between">
            <x-primary-button>Save password</x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button class="text-sm text-gray-500 underline">Log out</button>
    </form>
</x-guest-layout>
