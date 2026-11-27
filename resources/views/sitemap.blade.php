<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>1.0</priority>
    </url>
    @foreach (['about', 'how-it-works', 'privacy', 'contact'] as $page)
        <url>
            <loc>{{ route($page) }}</loc>
            <changefreq>yearly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach
</urlset>
