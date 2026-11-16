<form method="POST" action="{{ route('locale.switch', app()->getLocale() === 'bn' ? 'en' : 'bn') }}" class="inline">
    @csrf
    <button type="submit" class="eco-logout !px-3" title="{{ app()->getLocale() === 'bn' ? 'Switch to English' : 'বাংলায় দেখুন' }}">
        {{ app()->getLocale() === 'bn' ? 'English' : 'বাংলা' }}
    </button>
</form>
