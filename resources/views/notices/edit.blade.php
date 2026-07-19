@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <h1 class="text-3xl font-bold mb-6">
        Edit Notice
    </h1>

    @if ($errors->any())

        <div class="mb-6 rounded-lg border border-red-300 bg-red-100 p-4 text-red-700">

            <ul class="list-disc ml-5">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    <div class="bg-white shadow rounded-xl p-6">

        <form
            action="{{ route('notices.update', $notice) }}"
            method="POST">

            @csrf
            @method('PUT')

            @include('notices._form')

        </form>

    </div>

</div>

@endsection