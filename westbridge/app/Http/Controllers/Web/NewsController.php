<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->validate(['category' => ['nullable', Rule::in(Post::CATEGORIES)]])['category'] ?? null;

        $posts = Post::query()->live()->latestFirst()
            ->when($category, fn ($q) => $q->where('category', $category))
            ->paginate(9)
            ->withQueryString();

        return view('pages.news.index', [
            'posts' => $posts,
            'category' => $category,
            'categories' => Post::query()->live()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()->live()->with('author:id,name')->where('slug', $slug)->firstOrFail();

        return view('pages.news.show', [
            'post' => $post,
            'more' => Post::query()->live()->latestFirst()->whereKeyNot($post->id)->limit(3)->get(),
        ]);
    }

    /** Old /insights/{slug} links. */
    public function legacy(string $slug): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('news.show', $slug, 301);
    }
}
