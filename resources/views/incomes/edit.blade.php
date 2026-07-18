@extends('layouts.app')

@section('title', 'Edit Income')
@section('page_title', 'Edit Income')

@section('content')

<div class="p-8">

    <div class="bg-white rounded-lg shadow p-6 max-w-3xl">

        <h1 class="text-3xl font-bold mb-6">
            Edit Income
        </h1>

        <form action="{{ route('incomes.update', $income) }}" method="POST">

            @csrf
            @method('PUT')

            @include('incomes._form')

        </form>

    </div>

</div>

@endsection
