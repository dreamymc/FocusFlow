<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Actions\AcceptInviteAction;
use App\Enums\InviteStatus;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvitationWebController extends Controller
{
    public function index(Request $request)
    {
        $invitations = Invitation::where('email', $request->user()->email)
            ->where('status', InviteStatus::Pending)
            ->with('workspace')
            ->get();

        return Inertia::render('Invitations/Index', [
            'invitations' => $invitations->map(fn ($inv) => [
                'id' => $inv->id,
                'workspace_name' => $inv->workspace->name,
                'role' => $inv->role->value,
                'token' => $inv->token,
                'created_at' => $inv->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function accept(Request $request, AcceptInviteAction $acceptInviteAction)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $workspace = $acceptInviteAction->execute($validated['token'], $request->user());
            session(['current_workspace_id' => $workspace->id]);
            return redirect()->route('dashboard')
                ->with('success', "You've joined {$workspace->name}!");
        } catch (\Exception $e) {
            return redirect()->route('invitations.index')
                ->with('error', 'Invalid or expired invitation token.');
        }
    }

    public function decline(Request $request, Invitation $invitation)
    {
        if ($invitation->email !== $request->user()->email) {
            abort(403);
        }

        $invitation->update(['status' => InviteStatus::Declined]);

        return redirect()->route('invitations.index')
            ->with('success', 'Invitation declined.');
    }
}
