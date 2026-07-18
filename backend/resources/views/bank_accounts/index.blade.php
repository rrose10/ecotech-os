@extends('layouts.app')

@section('title', 'Cash & Bank')
@section('page_title', 'Cash & Bank')

@section('content')

<div class="p-8">

    <div class="flex justify-between items-center mb-6">

        <h1 class="text-3xl font-bold">
            Cash & Bank
        </h1>

        <a href="{{ route('bank-accounts.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Add Account
        </a>

    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">

        <form method="GET" class="mb-4">

            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search account..."
                class="w-full md:w-80 border rounded-lg px-4 py-2 focus:ring focus:ring-blue-200">

        </form>

        @if($accounts->count())

        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead>

                    <tr class="border-b bg-gray-50">

                        <th class="text-left p-3">Account</th>
                        <th class="text-left p-3">Type</th>
                        <th class="text-left p-3">Bank</th>
                        <th class="text-right p-3">Opening Balance</th>
                        <th class="text-right p-3">Current Balance</th>
                        <th class="text-center p-3">Status</th>
                        <th class="text-center p-3">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($accounts as $account)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3">

                            <div class="font-semibold">
                                {{ $account->account_name }}
                            </div>

                            @if($account->account_number)
                                <div class="text-xs text-gray-500">
                                    {{ $account->account_number }}
                                </div>
                            @endif

                        </td>

                        <td class="p-3">

                            @if($account->type == 'Cash')

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                    Cash
                                </span>

                            @else

                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs">
                                    Bank
                                </span>

                            @endif

                        </td>

                        <td class="p-3">
                            {{ $account->bank_name ?? '-' }}
                        </td>

                        <td class="p-3 text-right">
                            ৳ {{ number_format($account->opening_balance,2) }}
                        </td>

                        <td class="p-3 text-right font-bold text-green-700">
                            ৳ {{ number_format($account->current_balance,2) }}
                        </td>

                        <td class="p-3 text-center">

                            @if($account->is_active)

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                    Active
                                </span>

                            @else

                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
                                    Inactive
                                </span>

                            @endif

                        </td>

                        <td class="p-3">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('bank-accounts.edit', $account) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                                    Edit
                                </a>

                                <form action="{{ route('bank-accounts.destroy', $account) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this account?')">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">

                                        Delete

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <div class="mt-6">

            {{ $accounts->links() }}

        </div>

        @else

            <div class="text-center py-10">

                <h3 class="text-xl font-semibold text-gray-700">
                    No Accounts Found
                </h3>

                <p class="text-gray-500 mt-2">
                    Create your first Cash or Bank account.
                </p>

            </div>

        @endif

    </div>

</div>

@endsection