@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Add Payroll
    </h1>

    <form action="{{ route('payrolls.store') }}" method="POST">

        @csrf

        @include('payrolls._form')

    </form>

</div>

@endsection