@extends('layouts.portal')

@section('title', 'Notices')
@section('page_title', 'Notices')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold text-slate-900">
                📢 Notices
            </h1>

            <p class="text-slate-500 mt-2">
                Latest office announcements
            </p>

        </div>

    </div>

    @forelse($notices as $notice)

        <a href="{{ route('portal.notices.show', $notice) }}"
           class="block bg-white border border-slate-200 rounded-2xl p-6 hover:shadow-lg transition">

            <div class="flex justify-between items-start">

                <div>

                    <h2 class="text-xl font-semibold text-slate-900">
                        {{ $notice->title }}
                    </h2>

                    <p class="text-sm text-slate-500 mt-2">
                        {{ $notice->publish_date->format('d M Y') }}
                    </p>

                </div>

                @switch($notice->type)

                    @case('Important')

                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm">
                            Important
                        </span>

                        @break

                    @case('Holiday')

                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">
                            Holiday
                        </span>

                        @break

                    @case('Meeting')

                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm">
                            Meeting
                        </span>

                        @break

                    @default

                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">
                            General
                        </span>

                @endswitch

            </div>

            <p class="mt-4 text-slate-600">

                {{ \Illuminate\Support\Str::limit(strip_tags($notice->content), 150) }}

            </p>

        </a>

    @empty

        <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-500">

            No notices available.

        </div>

    @endforelse

    <div>

        {{ $notices->links() }}

    </div>

</div>

@endsection