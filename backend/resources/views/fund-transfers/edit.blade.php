@extends('layouts.app')

@section('title', 'Edit transfer')

@section('page_title', 'Edit transfer')

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow">
        <div class="border-b px-6 py-4">
            <h2 class="text-xl font-bold">Edit transfer</h2>
            <p class="text-gray-500 text-sm mt-1">Move money between your own accounts, for example bank to cash in hand.</p>
        </div>

        <div class="p-6">
            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 text-red-700 px-4 py-3">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-100 border border-red-300 p-4">
                    <ul class="list-disc list-inside text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('fund-transfers.update', $transfer) }}" method="POST">
                @method('PUT')
                @include('fund-transfers._form')
            </form>
        </div>
    </div>
</div>

@endsection
