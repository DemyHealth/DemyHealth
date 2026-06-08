<nav aria-label="Admin modules">
    @foreach (app(\App\Services\AdminNavigation::class)->linksFor(auth()->user()) as $link)
        <a href="/admin/{{ str($link)->lower()->replace(' / ', '-')->replace(' ', '-') }}">{{ $link }}</a>
    @endforeach
</nav>
