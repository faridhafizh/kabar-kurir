<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;

class NewsController extends Controller
{
    public function welcome()
    {
        // Cache the latest articles for 1 hour to reduce database queries on the homepage
        $articles = Cache::remember('welcome_articles', 3600, function () {
            return Article::orderBy('published_at', 'desc')->take(3)->get();
        });
        return view('welcome', compact('articles'));
    }

    public function index(Request $request)
    {
        $query = Article::orderBy('published_at', 'desc');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $articles = $query->paginate(10);
        return view('news.index', compact('articles'));
    }

    public function show($id)
    {
        // Cache individual article for 1 day as they rarely change after being published
        $article = Cache::remember("article_{$id}", 86400, function () use ($id) {
            return Article::findOrFail($id);
        });
        return view('news.show', compact('article'));
    }
}
