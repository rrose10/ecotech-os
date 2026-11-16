@extends('layouts.portal')

@section('title', __('Notice'))
@section('page_title', __('Notice'))

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="bg-white border border-slate-200 rounded-2xl p-8">

        <div class="flex items-center justify-between mb-8">

            <div>

                <h1 class="text-3xl font-bold text-slate-900">

                    {{ $notice->title }}

                </h1>

                <p class="text-slate-500 mt-2">

                    {{ $notice->publish_date->format('d M Y') }}

                </p>

            </div>

            <a href="{{ route('portal.notices') }}"
               class="px-5 py-2 border rounded-lg hover:bg-slate-100">

                {{ __("← Back") }}

            </a>

        </div>

        <hr class="mb-8">

        <div class="whitespace-pre-line leading-8 text-slate-700">

            {{ $notice->content }}

        </div>

    </div>

</div>

@endsection