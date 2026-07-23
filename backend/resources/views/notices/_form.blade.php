@csrf

<div class="space-y-6">

    <div>
        <label class="block font-medium mb-2">
            Title
        </label>

        <input
            type="text"
            name="title"
            value="{{ old('title', $notice->title ?? '') }}"
            class="w-full border rounded-lg px-4 py-2"
            required>
    </div>

    <div>
        <label class="block font-medium mb-2">
            Type
        </label>

        <select
            name="type"
            class="w-full border rounded-lg px-4 py-2">

            @foreach(['General','Important','Holiday','Meeting'] as $type)

                <option
                    value="{{ $type }}"
                    @selected(old('type', $notice->type ?? 'General') == $type)>

                    {{ $type }}

                </option>

            @endforeach

        </select>
    </div>

    <div>
        <label class="block font-medium mb-2">
            Content
        </label>

        <textarea
            name="content"
            rows="8"
            class="w-full border rounded-lg px-4 py-2"
            required>{{ old('content', $notice->content ?? '') }}</textarea>
    </div>

    <div class="grid md:grid-cols-2 gap-6">

        <div>

            <label class="block font-medium mb-2">
                Publish Date
            </label>

            <input
                type="date"
                name="publish_date"
                value="{{ old('publish_date', isset($notice) ? optional($notice->publish_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                class="w-full border rounded-lg px-4 py-2"
                required>

        </div>

        <div>

            <label class="block font-medium mb-2">
                Expiry Date
            </label>

            <input
                type="date"
                name="expiry_date"
                value="{{ old('expiry_date', isset($notice) && $notice->expiry_date ? $notice->expiry_date->format('Y-m-d') : '') }}"
                class="w-full border rounded-lg px-4 py-2">

        </div>

    </div>

    <div>

        <label class="inline-flex items-center gap-2">

            <input
                type="checkbox"
                name="is_published"
                value="1"
                @checked(old('is_published', $notice->is_published ?? true))>

            <span>Published</span>

        </label>

    </div>

</div>

<div class="mt-8 flex gap-3">

    <button
        type="submit"
        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">

        Save Notice

    </button>

    <a
        href="{{ route('notices.index') }}"
        class="px-6 py-2 border rounded-lg">

        Cancel

    </a>

</div>