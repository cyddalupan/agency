<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgencyUserController extends Controller
{
    use AuthorizesRequests;

    /**
     * List users belonging to the given agency (rendered on the agency detail page).
     */
    public function index(Agency $agency)
    {
        $this->authorize('viewAny', Agency::class);

        return redirect()->route('agencies.show', $agency);
    }

    /**
     * Show the form for creating a new user within the agency.
     */
    public function create(Agency $agency)
    {
        $this->authorize('update', $agency);

        return view('agencies.users.create', [
            'agency' => $agency,
            'roles'  => self::rolesFor($agency),
        ]);
    }

    /**
     * Store a new user scoped to the agency.
     */
    public function store(Request $request, Agency $agency)
    {
        $this->authorize('update', $agency);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')],
            'password'  => ['required', 'string', 'min:8'],
            'user_type' => ['required', 'string', 'max:50'],
            'status'    => ['required', 'in:active,inactive'],
        ]);

        $user = $agency->users()->create([
            'agency_id' => $agency->id,
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
            'status'    => $validated['status'],
        ]);

        return redirect()->route('agencies.show', $agency)
            ->with('success', "User {$user->name} created.");
    }

    /**
     * Show the form for editing a user within the agency.
     */
    public function edit(Agency $agency, User $user)
    {
        $this->authorize('update', $agency);
        abort_unless($user->agency_id === $agency->id, 404);

        return view('agencies.users.edit', [
            'agency' => $agency,
            'user'   => $user,
            'roles'  => self::rolesFor($agency),
        ]);
    }

    /**
     * Update a user within the agency.
     */
    public function update(Request $request, Agency $agency, User $user)
    {
        $this->authorize('update', $agency);
        abort_unless($user->agency_id === $agency->id, 404);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8'],
            'user_type' => ['required', 'string', 'max:50'],
            'status'    => ['required', 'in:active,inactive'],
        ]);

        $data = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'user_type' => $validated['user_type'],
            'status'    => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($validated['password']);
        }

        // Same sensitive-action rule as UserController::update: an admin
        // resetting another user's password must leave an audit trail. Capture
        // before the write (password is a hash after this point), log after it.
        $passwordChanged = ! empty($validated['password']);
        $oldRole = $user->user_type;

        $user->update($data);

        if ($oldRole !== $user->user_type) {
            SensitiveActionLogger::roleChanged($user, $oldRole, $user->user_type, auth()->user());
        }

        if ($passwordChanged) {
            SensitiveActionLogger::userPasswordResetByAdmin($user, auth()->user());
        }

        return redirect()->route('agencies.show', $agency)
            ->with('success', "User {$user->name} updated.");
    }

    /**
     * Delete a user within the agency.
     */
    public function destroy(Agency $agency, User $user)
    {
        $this->authorize('update', $agency);
        abort_unless($user->agency_id === $agency->id, 404);

        $user->delete();

        return redirect()->route('agencies.show', $agency)
            ->with('success', 'User deleted.');
    }

    /**
     * Access levels offered when editing users of this agency.
     *
     * DMW User is a FINAS/CSDI access level (card 6af81c30) and is not offered
     * on the other tenants, where it would grant nothing.
     */
    public static function rolesFor(Agency $agency): array
    {
        $roles = \App\Models\User::ACCESS_PRESETS;

        if (! \App\Support\ModuleAccess::isAnyAgency($agency->id, \App\Support\ModuleAccess::DMW_USER_AGENCIES)) {
            unset($roles['dmw_user']);
        }

        return $roles;
    }
}
