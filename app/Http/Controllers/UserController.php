<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\UserPermission;
use App\Models\ActivityLog;
use App\Services\SensitiveActionLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * All known permissions in the system.
     */
    private function getAllPermissions(): array
    {
        return [
            'view_applicants',
            'edit_applicants',
            'view_bills',
            'edit_bills',
            'view_employers',
            'edit_employers',
            'view_reports',
            'manage_users',
        ];
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::orderBy('name');

        // Super admin sees users across all agencies; others are scoped
        if (auth()->user()->user_type !== 'super_admin') {
            $query->where('agency_id', auth()->user()->agency_id);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role (user_type)
        if ($request->filled('role')) {
            $query->where('user_type', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(20);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $branches = Branch::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get();

        return view('users.create', compact('branches'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname'     => ['nullable', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users')],
            'contact'     => ['nullable', 'string', 'max:50'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
            'user_type'   => ['required', 'string', 'max:50', Rule::in(array_keys(\App\Models\User::ACCESS_PRESETS))],
            'branch_id'   => ['nullable', Rule::exists('branches', 'id')->where(function ($q) {
                $q->where('agency_id', auth()->user()->agency_id);
            })],
            'status'      => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        User::create([
            'agency_id'   => auth()->user()->agency_id,
            'name'        => $validated['name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'surname'     => $validated['surname'] ?? null,
            'contact'     => $validated['contact'] ?? null,
            'branch_id'   => $validated['branch_id'] ?? null,
            'email'       => $validated['email'],
            'password'    => Hash::make($validated['password']),
            'user_type'   => $validated['user_type'],
            'status'      => $validated['status'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $branches = Branch::where('agency_id', $user->agency_id ?? auth()->user()->agency_id)->orderBy('name')->get();

        return view('users.edit', compact('user', 'branches'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname'     => ['nullable', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'contact'   => ['nullable', 'string', 'max:50'],
            'user_type' => ['required', 'string', 'max:50', Rule::in(array_merge(array_keys(\App\Models\User::ACCESS_PRESETS), ['employer', 'manager', 'coordinator', 'recruiter', 'interviewer', 'report_viewer', 'director', 'marketer']))],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where(function ($q) use ($user) {
                $q->where('agency_id', $user->agency_id);
            })],
            'status'    => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $activities = $user->activities()->latest()->get();

        return view('users.show', compact('user', 'activities'));
    }

    /**
     * Show the permissions/roles management page for a user.
     */
    public function permissions(User $user)
    {
        $this->authorize('view', $user);

        $allPermissions = $this->getAllPermissions();
        $userPermissions = $user->permissions()->pluck('permission')->toArray();

        return view('users.permissions', compact('user', 'userPermissions') + ['permissions' => $allPermissions]);
    }

    /**
     * Update the user's role and granular permissions.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'user_type'   => ['required', 'string', 'max:50'],
            'permissions'  => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in($this->getAllPermissions())],
        ]);

        $oldRole = $user->user_type;

        // Update the user's role
        $user->update(['user_type' => $validated['user_type']]);

        // Log role change if role actually changed
        if ($oldRole !== $validated['user_type']) {
            SensitiveActionLogger::roleChanged($user, $oldRole, $validated['user_type'], auth()->user());
        }

        // Replace all permissions (delete old, create new)
        $user->permissions()->delete();

        if (! empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permission) {
                $user->permissions()->create([
                    'permission' => $permission,
                ]);
            }
        }

        return redirect()->route('users.permissions', $user)
            ->with('success', 'Permissions updated successfully.');
    }

    /**
     * Activate a user (set status to active).
     */
    public function activate(User $user)
    {
        $this->authorize('update', $user);

        $user->update(['status' => 'active']);

        ActivityLog::create([
            'agency_id'    => $user->agency_id,
            'user_id'      => auth()->id(),
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'action'       => 'activated',
            'description'  => auth()->user()->name.' activated user '.$user->name,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User activated successfully.');
    }

    /**
     * Suspend a user (set status to suspended).
     */
    public function suspend(User $user)
    {
        // Prevent self-suspension
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot suspend your own account.');
        }

        $this->authorize('update', $user);

        $user->update(['status' => 'suspended']);

        ActivityLog::create([
            'agency_id'    => $user->agency_id,
            'user_id'      => auth()->id(),
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'action'       => 'suspended',
            'description'  => auth()->user()->name.' suspended user '.$user->name,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User suspended successfully.');
    }

    /**
     * Deactivate a user (set status to inactive).
     */
    public function deactivate(User $user)
    {
        $this->authorize('update', $user);

        $user->update(['status' => 'inactive']);

        ActivityLog::create([
            'agency_id'    => $user->agency_id,
            'user_id'      => auth()->id(),
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'action'       => 'deactivated',
            'description'  => auth()->user()->name.' deactivated user '.$user->name,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion (business rule, not authorization)
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $this->authorize('delete', $user);

        // Log deletion before delete
        SensitiveActionLogger::deletion($user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
