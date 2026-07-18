@extends('layouts.app')

@section('title', 'Edit Bank Account')
@section('page_title', 'Edit Bank Account')

@section('content')

<div class="p-8">

    <div class="max-w-3xl mx-auto">

        <div class="flex justify-between items-center mb-6">

            <h1 class="text-3xl font-bold">
                Edit Account
            </h1>

            <a href="{{ route('bank-accounts.index') }}"
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ← Back
            </a>

        </div>

        <div class="bg-white rounded-lg shadow p-6">

            <form action="{{ route('bank-accounts.update', $bankAccount) }}" method="POST">

                @csrf
                @method('PUT')

                @include('bank_accounts._form')

            </form>

        </div>

    </div>

</div>

@endsection