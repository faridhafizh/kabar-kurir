@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 0 auto; padding-top: 2rem;">
    <a href="{{ route('news.index') }}" style="display: inline-flex; items-center; gap: 0.5rem; color: var(--color-text-muted); text-decoration: none; font-weight: 500; margin-bottom: 2rem; font-size: 0.95rem;">
        &larr; Kembali ke Sorotan
    </a>

    <article class="card">
        <header style="margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--color-border);">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <span class="badge" style="background: var(--color-bg-secondary); color: var(--color-text-primary);">{{ $article->source }}</span>
                <span class="text-sm text-muted">{{ $article->published_at ? $article->published_at->translatedFormat('l, d F Y - H:i') : 'Waktu tidak diketahui' }} WIB</span>
            </div>
            
            <h1 style="font-size: 2.25rem; line-height: 1.25; margin-bottom: 1.5rem;">{{ $article->title }}</h1>
            
            @if($article->url)
                <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="font-size: 0.85rem; padding: 0.5rem 1rem;">
                    Baca Sumber Asli &nearr;
                </a>
            @endif
        </header>

        <div style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-primary);">
            <p style="font-weight: 500; font-size: 1.2rem; margin-bottom: 2rem; color: var(--color-text-secondary);">
                {{ $article->description }}
            </p>
            
            <div style="white-space: pre-wrap;">{{ $article->content ?? 'Konten lengkap tersedia di sumber asli.' }}</div>
        </div>
        
        <footer style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border); font-size: 0.85rem; color: var(--color-text-muted); text-align: center;">
            Artikel ini diagregasi secara otomatis. Hak cipta milik {{ $article->source }}.
        </footer>
    </article>
</div>
@endsection
