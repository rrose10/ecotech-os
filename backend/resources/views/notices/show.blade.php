@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-white rounded-xl shadow p-8">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-3xl font-bold">
                    {{ $notice->title }}
                </h1>

                <p class="text-gray-500 mt-2">

                    {{ $notice->type }}

                    •

                    Published:
                    {{ $notice->publish_date->format('d M Y') }}

                </p>

            </div>

            <a href="{{ route('notices.index') }}"
               class="border px-4 py-2 rounded-lg">

                Back

            </a>

        </div>

        <div class="prose max-w-none whitespace-pre-line">

            {{ $notice->content }}

        </div>

    </div>

</div>

@endsection