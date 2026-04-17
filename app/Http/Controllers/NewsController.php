<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class NewsController extends Controller
{
    public function welcome()
    {
        $articles = Article::orderBy('published_at', 'desc')->take(3)->get();
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
        $article = Article::findOrFail($id);
        return view('news.show', compact('article'));
    }
}
