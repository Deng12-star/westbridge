<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Media\ImageUploader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', ['project' => new Project(['is_published' => true, 'mock' => 'dashboard'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->images->store($request->file('image'), 'projects');
        }

        $project = Project::query()->create($data);

        return redirect()->route('admin.projects.edit', $project)->with('status', 'Project added.');
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', ['project' => $project]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request, $project);

        if ($request->hasFile('image')) {
            $this->images->delete($project->image_path);
            $data['image_path'] = $this->images->store($request->file('image'), 'projects');
        } elseif ($request->boolean('remove_image')) {
            $this->images->delete($project->image_path);
            $data['image_path'] = null;
        }

        $project->update($data);

        return back()->with('status', 'Project saved.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->images->delete($project->image_path);
        $project->delete();

        return redirect()->route('admin.projects.index')->with('status', 'Project deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'alpha_dash', 'max:190'],
            'client' => ['nullable', 'string', 'max:190'],
            'location' => ['nullable', 'string', 'max:190'],
            'industry' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'service' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:40'],
            'year' => ['nullable', 'string', 'max:10'],
            'mock' => ['required', Rule::in(array_keys(Project::MOCKS))],
            'live_url' => ['nullable', 'url:http,https', 'max:255'],
            'summary' => ['nullable', 'string', 'max:400'],
            'overview' => ['nullable', 'string', 'max:10000'],
            'scope' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['image']);

        $base = Str::slug(($data['slug'] ?? '') ?: $data['title']) ?: 'project';
        $slug = $base;
        $i = 2;
        while (Project::query()->where('slug', $slug)->when($project, fn ($q) => $q->whereKeyNot($project->id))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return [
            ...$data,
            'slug' => $slug,
            // Paragraphs are separated by a blank line; scope is one item per line.
            'overview' => collect(preg_split('/\R\s*\R/', trim($data['overview'] ?? '')) ?: [])->map(fn ($p) => trim(preg_replace('/\s+/', ' ', (string) $p)))->filter()->values()->all(),
            'scope' => collect(preg_split('/\R/', $data['scope'] ?? '') ?: [])->map(fn ($l) => trim(ltrim(trim((string) $l), '-*• ')))->filter()->values()->all(),
            'sort_order' => $data['sort_order'] ?? 0,
            'show_live_link' => $request->boolean('show_live_link'),
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
