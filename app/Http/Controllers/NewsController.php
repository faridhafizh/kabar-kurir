<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Article;

class NewsController extends Controller
{
    public function welcome()
    {
        $articles = Cache::rememberForever('welcome_articles', function () {
            // Select only necessary columns, avoiding the longText `content` field
            // and saving DB memory/transfer for the most hit page.
            return Article::select(['id', 'title', 'source', 'description', 'published_at'])
                ->orderBy('published_at', 'desc')
                ->take(3)
                ->get();
        });
        return view('welcome', compact('articles'));
    }

    public function index(Request $request)
    {
        // ⚡ Bolt Optimization: Only select necessary columns for the index view
        // to avoid loading the large 'content' longText field into memory for every article.
        $query = Article::select(['id', 'title', 'source', 'description', 'published_at'])
            ->orderBy('published_at', 'desc');
        
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
        $article = Article::findOrFail($id);
        return view('news.show', compact('article'));
    }
}
