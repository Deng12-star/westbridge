<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Services\Media\ImageUploader;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The team shown on the About page. */
class TeamController extends Controller
{
    public function __construct(private readonly ImageUploader $images) {}

    public function index(): View
    {
        return view('admin.team.index', ['members' => TeamMember::query()->ordered()->get()]);
    }

    public function create(): View
    {
        return view('admin.team.form', ['member' => new TeamMember([
            'is_published' => true,
            'sort_order' => (int) TeamMember::query()->max('sort_order') + 1,
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->images->store($request->file('photo'), 'team', 800, 'photo');
        }

        TeamMember::query()->create($data);

        return redirect()->route('admin.team.index')->with('status', 'Team member added.');
    }

    public function edit(TeamMember $member): View
    {
        return view('admin.team.form', ['member' => $member]);
    }

    public function update(Request $request, TeamMember $member): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $this->images->delete($member->photo_path);
            $data['photo_path'] = $this->images->store($request->file('photo'), 'team', 800, 'photo');
        } elseif ($request->boolean('remove_photo')) {
            $this->images->delete($member->photo_path);
            $data['photo_path'] = null;
        }

        $member->update($data);

        return redirect()->route('admin.team.index')->with('status', 'Saved.');
    }

    public function destroy(TeamMember $member): RedirectResponse
    {
        $this->images->delete($member->photo_path);
        $member->delete();

        return redirect()->route('admin.team.index')->with('status', 'Team member removed.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'position' => ['required', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:600'],
            'linkedin_url' => ['nullable', 'url:https', 'max:255', 'regex:#^https://([a-z]+\.)?linkedin\.com/#i'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'linkedin_url.regex' => 'Use the address of a LinkedIn profile, starting with https://www.linkedin.com/',
        ]);

        unset($data['photo']);

        return [...$data, 'sort_order' => $data['sort_order'] ?? 0, 'is_published' => $request->boolean('is_published')];
    }
}
