{{-- Global error banner. Success messages are shown by each page. --}}
@php
    $flashError = session('error');
    $bagError   = $errors->has('error') ? $errors->first('error') : null;
    $message    = $flashError ?: $bagError;
@endphp

@if($message)
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        {{ $message }}
    </div>
@endif
