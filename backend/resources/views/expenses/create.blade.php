@extends('layouts.app')

@section('title', 'Add Expense')
@section('page_title', 'Add Expense')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-2xl font-bold mb-6">
            Add Expense
        </h2>

        <form action="{{ route('expenses.store') }}" method="POST">

            @csrf

            @include('expenses._form')

        </form>

    </div>

</div>

@endsection