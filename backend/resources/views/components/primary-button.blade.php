<button {{ $attributes->merge(['type' => 'submit', 'class' => 'eco-primary-btn inline-flex items-center justify-center text-sm focus:outline-none']) }}>
    {{ $slot }}
</button>
