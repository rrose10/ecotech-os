@extends('layouts.app')

@section('title', 'Add Income')
@section('page_title', 'Add Income')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">

        <h1 class="text-3xl font-bold mb-6">
            Add Income
        </h1>

        <form action="{{ route('incomes.store') }}" method="POST">

            @csrf

            @include('incomes._form')

        </form>

    </div>

</div>

@endsection