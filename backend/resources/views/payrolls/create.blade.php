@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        Add Payroll
    </h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-300 bg-green-100 px-4 py-3 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-100 px-4 py-3 text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payrolls.store') }}" method="POST">

        @csrf

        @include('payrolls._form')

    </form>

</div>

@endsection