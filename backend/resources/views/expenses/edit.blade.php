@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page_title', 'Edit Expense')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6">

        <h2 class="text-2xl font-bold mb-6">
            Edit Expense
        </h2>

        <form action="{{ route('expenses.update', $expense) }}" method="POST">

            @csrf
            @method('PUT')

            @include('expenses._form')

        </form>

    </div>

</div>

@endsection