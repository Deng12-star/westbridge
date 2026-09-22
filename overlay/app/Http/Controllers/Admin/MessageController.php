<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Lead;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.messages.index', [
            'messages' => ContactMessage::query()
                ->when($request->input('filter') === 'unread', fn ($q) => $q->where('is_read', false))
                ->when($request->input('filter') === 'deleted', fn ($q) => $q->onlyTrashed())
                ->latest()
                ->paginate(25)
                ->withQueryString(),
            'unread' => ContactMessage::query()->where('is_read', false)->count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        // (Deleted messages are opened through the "Recently deleted" list's Restore.)
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', ['message' => $message]);
    }

    public function read(Request $request, ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['is_read' => true])->save();

        return $this->backTo($request, 'Marked as read.');
    }

    public function unread(Request $request, ContactMessage $message): RedirectResponse
    {
        $message->forceFill(['is_read' => false])->save();

        return $this->backTo($request, 'Marked as unread.');
    }

    public function readAll(): RedirectResponse
    {
        $count = ContactMessage::query()->where('is_read', false)->update(['is_read' => true]);

        return redirect()->route('admin.messages.index')
            ->with('status', $count === 1 ? '1 message marked as read.' : $count.' messages marked as read.');
    }

    /** Return to the list (keeping its filter) when the button was pressed there. */
    private function backTo(Request $request, string $status): RedirectResponse
    {
        $from = (string) $request->input('from');

        return ($from === 'list'
            ? redirect()->route('admin.messages.index', array_filter(['filter' => $request->input('filter')]))
            : redirect()->back())
            ->with('status', $status);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->lead()->delete();
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('status', 'Message deleted. You can restore it from "Recently deleted" for '.ContactMessage::RESTORE_DAYS.' days.');
    }

    public function restore(ContactMessage $message): RedirectResponse
    {
        DB::transaction(function () use ($message): void {
            $message->restore();
            Lead::withTrashed()
                ->where('sourceable_type', $message->getMorphClass())
                ->where('sourceable_id', $message->id)
                ->restore();
        });

        return redirect()->route('admin.messages.show', $message)->with('status', 'Message restored.');
    }
}
