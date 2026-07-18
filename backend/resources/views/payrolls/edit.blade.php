@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Edit Payroll
    </h1>

    <form action="{{ route('payrolls.update', $payroll) }}" method="POST">

        @csrf
        @method('PUT')

        @include('payrolls._form')

    </form>

</div>

@endsection