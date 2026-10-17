<?php

namespace App\Http\Controllers;

use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolInvitationController extends Controller
{
    public function show(Request $request, string $token)
    {
        $invitation = $this->findValidInvitation($token);
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser && !$request->user()) {
            return redirect()->guest(route('school.invitations.show', $token))
                ->with('status', 'Connectez-vous avec l’adresse invitée pour rejoindre l’école.');
        }

        if ($existingUser) {
            abort_unless($request->user()->is($existingUser), 403);
            abort_if($existingUser->school_id, 409, 'Ce compte est déjà rattaché à un établissement.');
        } else {
            abort_if($request->user(), 403, 'Connectez-vous avec l’adresse email invitée pour accepter cette invitation.');
        }

        return view('schools.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
            'existingUser' => $existingUser !== null,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->findValidInvitation($token);
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            abort_unless($request->user()?->is($existingUser), 403, 'Connectez-vous avec l’adresse email invitée pour accepter cette invitation.');
            abort_if($existingUser->school_id, 409, 'Ce compte est déjà rattaché à un établissement.');
            $validated = [];
        } else {
            abort_if($request->user(), 403, 'Connectez-vous avec l’adresse email invitée pour accepter cette invitation.');
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        }

        $user = DB::transaction(function () use ($validated, $invitation, $existingUser): User {
            if ($existingUser) {
                $existingUser->school_id = $invitation->school_id;
                $existingUser->role = $invitation->role ?: 'staff';
                $existingUser->save();
                $invitation->forceFill(['accepted_at' => now()])->save();

                return $existingUser;
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
            ]);
            $user->school_id = $invitation->school_id;
            $user->role = $invitation->role ?: 'staff';
            $user->save();

            $invitation->forceFill(['accepted_at' => now()])->save();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Vous avez rejoint '.$invitation->school->name.'.');
    }

    private function findValidInvitation(string $token): SchoolInvitation
    {
        $invitation = SchoolInvitation::with('school')
            ->where('token', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->firstOrFail();

        abort_if($invitation->expires_at->isPast(), 410, 'Cette invitation a expiré. Demandez une nouvelle invitation à l’administrateur de l’école.');

        return $invitation;
    }
}
