@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-violet-700']) }}>
        {{ $status }}
    </div>
@endif
