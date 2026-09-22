<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\TeamMember;
use App\Models\Product;
use App\Models\Project;
use App\Models\WhatsappClick;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $since = now()->subDays(30);

        $topProducts = WhatsappClick::query()
            ->select('product_id', DB::raw('count(*) as clicks'))
            ->whereNotNull('product_id')
            ->where('created_at', '>=', $since)
            ->groupBy('product_id')
            ->orderByDesc('clicks')
            ->limit(5)
            ->with('product:id,name,slug')
            ->get();

        // What still needs doing before the site looks finished.
        $checklist = collect([
            ['label' => 'WhatsApp number', 'done' => filled(setting('contact.whatsapp')), 'url' => route('admin.settings.edit', 'contact')],
            ['label' => 'Phone number', 'done' => filled(setting('contact.phone')), 'url' => route('admin.settings.edit', 'contact')],
            ['label' => 'Email address', 'done' => filled(setting('contact.email')), 'url' => route('admin.settings.edit', 'contact')],
            ['label' => 'Street address', 'done' => filled(setting('contact.address')), 'url' => route('admin.settings.edit', 'contact')],
            ['label' => 'At least one social link', 'done' => filled(active_socials()), 'url' => route('admin.settings.edit', 'social')],
            ['label' => 'At least 6 products', 'done' => Product::query()->published()->count() >= 6, 'url' => route('admin.products.create')],
            ['label' => 'At least 3 projects', 'done' => Project::query()->published()->count() >= 3, 'url' => route('admin.projects.index')],
            ['label' => 'Team on the About page', 'done' => Schema::hasTable('team_members') && TeamMember::query()->published()->exists(), 'url' => route('admin.team.create')],
            ['label' => 'First news post published', 'done' => Schema::hasTable('posts') && Post::query()->live()->exists(), 'url' => route('admin.posts.create')],
        ]);

        // Queued email that has sat for 10+ minutes means the background
        // worker (schedule:run cron) is not running.
        $stuckEmails = Schema::hasTable('jobs')
            ? DB::table('jobs')->where('created_at', '<', now()->subMinutes(10)->getTimestamp())->count()
            : 0;
        $failedEmails = Schema::hasTable('failed_jobs')
            ? DB::table('failed_jobs')->where('failed_at', '>=', now()->subDays(7))->count()
            : 0;

        return view('admin.dashboard', [
            'stuckEmails' => $stuckEmails,
            'failedEmails' => $failedEmails,
            'stats' => [
                ['label' => 'Products live', 'value' => Product::query()->published()->count(), 'url' => route('admin.products.index')],
                ['label' => 'Unread messages', 'value' => ContactMessage::query()->where('is_read', false)->count(), 'url' => route('admin.messages.index')],
                ['label' => 'WhatsApp taps (30 days)', 'value' => WhatsappClick::query()->where('created_at', '>=', $since)->count(), 'url' => null],
                ['label' => 'Projects live', 'value' => Project::query()->published()->count(), 'url' => route('admin.projects.index')],
            ],
            'recentMessages' => ContactMessage::query()->latest()->limit(5)->get(),
            'topProducts' => $topProducts,
            'checklist' => $checklist,
        ]);
    }
}
