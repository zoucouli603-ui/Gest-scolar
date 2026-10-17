<?php

namespace App\Http\Controllers;

use App\Mail\SchoolInvitationMail;
use App\Models\SchoolInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SchoolTeamController extends Controller
{
    public function index(Request $request)
    {
        $school = $request->user()->school;

        return view('schools.team', [
            'members' => $school->users()->orderBy('name')->get(),
            'roles' => User::lyceeStaffRoles(),
            'invitations' => $school->invitations()
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(array_keys(User::lyceeStaffRoles()))],
        ]);
        $school = $request->user()->school;
        $existingUser = User::where('email', $validated['email'])->first();

        if ($existingUser?->school_id) {
            return back()->withErrors([
                'email' => 'Cette adresse est déjà rattachée à un établissement.',
            ])->withInput();
        }

        if ($school->invitations()->where('email', $validated['email'])->whereNull('accepted_at')->where('expires_at', '>', now())->exists()) {
            return back()->withErrors(['email' => 'Une invitation valide a déjà été envoyée à cette adresse.'])->withInput();
        }

        $token = Str::random(64);
        $invitation = $school->invitations()->updateOrCreate(
            ['email' => $validated['email']],
            [
                'token' => hash('sha256', $token),
                'role' => $validated['role'],
                'invited_by' => $request->user()->id,
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]
        );

        Mail::to($invitation->email)->send(new SchoolInvitationMail(
            $school->name,
            $invitation->email,
            route('school.invitations.show', ['token' => $token])
        ));

        return redirect()->route('school.team.index')->with('success', 'Invitation envoyée. Elle est valable pendant 7 jours.');
    }
}
