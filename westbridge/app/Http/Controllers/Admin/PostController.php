<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Media\ImageUploader;
use App\Support\PostFormatter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** News and updates. Publishing needs the content.publish permission. */
class PostController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function index(Request $request): View
    {
        $posts = Post::query()->with('author:id,name')
            ->when($request->input('status') === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($request->input('status') === 'scheduled', fn ($q) => $q->where('is_published', true)->where('published_at', '>', now()))
            ->when($request->input('status') === 'published', fn ($q) => $q->live())
            ->orderByRaw('published_at is null desc')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.posts.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['category' => 'News'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['author_id'] = $request->user()->id;

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $this->images->store($request->file('cover'), 'news', 1600, 'cover');
        }

        $post = Post::query()->create($data);

        return redirect()->route('admin.posts.edit', $post)->with('status', $this->savedMessage($post));
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $this->validated($request, $post);

        if ($request->hasFile('cover')) {
            $this->images->delete($post->cover_path);
            $data['cover_path'] = $this->images->store($request->file('cover'), 'news', 1600, 'cover');
        } elseif ($request->boolean('remove_cover')) {
            $this->images->delete($post->cover_path);
            $data['cover_path'] = null;
        }

        $post->update($data);

        return back()->with('status', $this->savedMessage($post->fresh()));
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->images->delete($post->cover_path);
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', 'Post deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'alpha_dash', 'max:190'],
            'category' => ['required', Rule::in(Post::CATEGORIES)],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:60000'],
            'published_at' => ['nullable', 'date'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['cover']);

        // Only staff with the publish permission can make a post public.
        $publish = $request->boolean('is_published') && $request->user()->can('content.publish');
        if (! $request->user()->can('content.publish') && $post) {
            $publish = $post->is_published;
        }

        $publishedAt = filled($data['published_at'] ?? null)
            ? Carbon::parse($data['published_at'], config('app.timezone'))
            : ($publish ? ($post?->published_at ?? now()) : $post?->published_at);

        return [
            ...$data,
            'slug' => $this->uniqueSlug(($data['slug'] ?? '') ?: $data['title'], $post?->id),
            'body_html' => PostFormatter::toHtml($data['body']),
            'is_published' => $publish,
            'published_at' => $publishedAt,
        ];
    }

    private function uniqueSlug(string $source, ?int $ignoreId): string
    {
        $base = Str::slug($source) ?: 'post';
        $slug = $base;
        $i = 2;

        while (Post::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function savedMessage(Post $post): string
    {
        return match ($post->statusLabel()) {
            'Published' => 'Saved - this post is live on the website.',
            'Scheduled' => 'Saved - this post goes live on '.$post->published_at->format('j M Y \a\t H:i').'.',
            default => 'Saved as a draft. It is not visible to the public.',
        };
    }
}
