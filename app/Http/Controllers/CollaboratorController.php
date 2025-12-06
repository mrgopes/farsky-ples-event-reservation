<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;

class CollaboratorController extends Controller
{
    /**
     * Add a collaborator to an event.
     */
    public function store(Request $request, $url_slug)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();

        // Check if user is the owner
        $user = $request->user();
        if ($event->user_id !== $user->id) {
            abort(403, 'Iba vlastník podujatia môže pridávať spolupracovníkov.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:manager,staff',
        ]);

        // Check if user is not already a collaborator
        if ($event->users()->where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Tento užívateľ je už spolupracovníkom.']);
        }

        // Check if trying to add themselves
        if ($validated['user_id'] == $user->id) {
            return back()->withErrors(['user_id' => 'Nemôžete pridať seba ako spolupracovníka.']);
        }

        $event->users()->attach($validated['user_id'], ['role' => $validated['role']]);

        return back()->with('success', 'Spolupracovník bol úspešne pridaný!');
    }

    /**
     * Update a collaborator's role.
     */
    public function update(Request $request, $url_slug, $userId)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();

        // Check if user is the owner
        $user = $request->user();
        if ($event->user_id !== $user->id) {
            abort(403, 'Iba vlastník podujatia môže upravovať spolupracovníkov.');
        }

        $validated = $request->validate([
            'role' => 'required|in:manager,staff',
        ]);

        $event->users()->updateExistingPivot($userId, ['role' => $validated['role']]);

        return back()->with('success', 'Rola spolupracovníka bola úspešne aktualizovaná!');
    }

    /**
     * Remove a collaborator from an event.
     */
    public function destroy(Request $request, $url_slug, $userId)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();

        // Check if user is the owner
        $user = $request->user();
        if ($event->user_id !== $user->id) {
            abort(403, 'Iba vlastník podujatia môže odstraňovať spolupracovníkov.');
        }

        $event->users()->detach($userId);

        return back()->with('success', 'Spolupracovník bol úspešne odstránený!');
    }
}

