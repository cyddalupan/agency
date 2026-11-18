<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Employer;
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

        // Card 6afae4a7 (Cyd 2026-11-16): no pagination — 73 users total,
        // 42 in the largest agency. Eager-load the branch column the table
        // renders, since we now hydrate every row in one request.
        $users = $query->with('branch')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $branches = Branch::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get();
        $employers = $this->employersForSelect(auth()->user()->agency_id);

        return view('users.create', compact('branches', 'employers') + ['assignableRoles' => $this->assignableRoles()]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $this->ensureCanAssignRole($request->input('user_type'));

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname'     => ['nullable', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users')],
            'username'    => ['nullable', 'string', 'max:255', Rule::unique('users')],
            'contact'     => ['nullable', 'string', 'max:50'],
            'password'    => ['required', 'string', 'confirmed'],
            'user_type'   => ['required', 'string', 'max:50', Rule::in(array_keys(\App\Models\User::ACCESS_PRESETS))],
            'branch_id'   => ['nullable', Rule::exists('branches', 'id')->where(function ($q) {
                $q->where('agency_id', auth()->user()->agency_id);
            })],
            'status'      => ['required', 'string', 'in:active,inactive,suspended'],
            'employer_id' => ['nullable', Rule::exists('employers', 'id')->where(function ($q) {
                $q->where('agency_id', auth()->user()->agency_id);
            })],
        ]);

        // (Branch feature) Branch accounts may only create users under their own
        // branch: omitted defaults to their branch, a different branch is rejected.
        $this->applyBranchDefaults($validated);

        // FRA accounts must be tied to an employer (FRA) record; other roles must not.
        $this->applyEmployerDefaults($validated, auth()->user()->agency_id);

        User::create([
            'agency_id'   => auth()->user()->agency_id,
            'name'        => $validated['name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'surname'     => $validated['surname'] ?? null,
            'contact'     => $validated['contact'] ?? null,
            'branch_id'   => $validated['branch_id'] ?? null,
            'employer_id' => $validated['employer_id'] ?? null,
            'email'       => $validated['email'],
            'username'    => $validated['username'] ?? null,
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
        $employers = $this->employersForSelect($user->agency_id ?? auth()->user()->agency_id);

        return view('users.edit', compact('user', 'branches', 'employers') + ['assignableRoles' => $this->assignableRoles()]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $this->ensureCanAssignRole($request->input('user_type'));

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'surname'     => ['nullable', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'username'  => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'contact'   => ['nullable', 'string', 'max:50'],
            'user_type' => ['required', 'string', 'max:50', Rule::in(array_merge(array_keys(\App\Models\User::ACCESS_PRESETS), ['employer', 'manager', 'coordinator', 'recruiter', 'interviewer', 'report_viewer', 'director', 'marketer']))],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where(function ($q) use ($user) {
                $q->where('agency_id', $user->agency_id);
            })],
            'status'    => ['required', 'string', 'in:active,inactive,suspended'],
            'password'  => ['nullable', 'string', 'confirmed'],
            'employer_id' => ['nullable', Rule::exists('employers', 'id')->where(function ($q) use ($user) {
                $q->where('agency_id', $user->agency_id);
            })],
        ]);

        $data = $validated;

        // (Branch feature) Branch accounts may only keep users in their own branch.
        $this->applyBranchDefaults($data);

        // FRA accounts must stay tied to an employer (FRA) record.
        $this->applyEmployerDefaults($data, $user->agency_id);

        // Toybits 2026-08-16: admin can reset a user's password from the edit
        // page. Only hash/update when a new password is provided.
        if (filled($data['password'] ?? null)) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Capture the pre-update role: the logger needs both values and
        // $user is mutated in place by update(). Neither applyBranchDefaults()
        // nor applyEmployerDefaults() rewrites user_type, so this stays the truth.
        $oldRole = $user->user_type;

        // An admin resetting someone else's password is a sensitive action.
        // Capture the fact BEFORE the update: $data['password'] holds a bcrypt
        // hash by this point, and after update() $user->password would compare
        // hash-to-hash. Reading the post-update value would always look "changed".
        $passwordChanged = filled($data['password'] ?? null);

        $user->update($data);

        // Log role changes. The call site for this used to live in the deleted
        // permissions() action, so role changes went unlogged from 5cd83d07
        // (2026-10-06) until this was restored. Guarded so ordinary profile
        // edits do not flood the audit log.
        if ($oldRole !== $user->user_type) {
            SensitiveActionLogger::roleChanged($user, $oldRole, $user->user_type, auth()->user());
        }

        // Logged AFTER the write so a rolled-back save never leaves a phantom
        // entry. Both guards are independent: a save that changes role AND
        // password produces two rows, one per action.
        if ($passwordChanged) {
            SensitiveActionLogger::userPasswordResetByAdmin($user, auth()->user());
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Employer (FRA) records available in the access-level dropdown. Only the
     * current agency's active employers are listed (tenant isolation).
     */
    private function employersForSelect(?int $agencyId)
    {
        return Employer::query()
            ->where('agency_id', $agencyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * FRA portal accounts (user_type = employer) must point at an employer
     * record from the same agency; every other access level clears the link so
     * a role change cannot leave a stray employer scope behind.
     */
    private function applyEmployerDefaults(array &$validated, ?int $agencyId): void
    {
        if (($validated['user_type'] ?? null) !== 'employer') {
            $validated['employer_id'] = null;

            return;
        }

        if (blank($validated['employer_id'] ?? null)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employer_id' => 'Select the FRA / employer this account belongs to.',
            ]);
        }

        $belongs = Employer::query()
            ->where('agency_id', $agencyId)
            ->whereKey($validated['employer_id'])
            ->exists();

        if (! $belongs) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'employer_id' => 'The selected employer does not belong to this agency.',
            ]);
        }
    }

    /**
     * (Branch feature) Branch accounts (non-admin with a branch) may only
     * assign users to their OWN branch. Omitted branch_id defaults to their
     * branch; any other branch is rejected server-side.
     */
    private function applyBranchDefaults(array &$validated): void
    {
        $user = auth()->user();
        if (! $user || ! $user->isBranchLocked()) {
            return;
        }

        $submitted = $validated['branch_id'] ?? null;

        if (blank($submitted)) {
            $validated['branch_id'] = $user->branch_id;

            return;
        }

        if ((int) $submitted !== (int) $user->branch_id) {
            abort(403, 'You can only assign users to your own branch.');
        }
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

    /**
     * Roles the current user is allowed to assign to others.
     *
     * Only super admins may create/promote other super admins. Agency admins
     * can assign every other preset role (admin, billing, staff, processor).
     *
     * @return array<string,string>
     */
    private function assignableRoles(): array
    {
        $roles = \App\Models\User::ACCESS_PRESETS;

        if (auth()->user()->user_type !== 'super_admin') {
            unset($roles['super_admin']);
        }

        // DMW User is a FINAS/CSDI access level (card 6af81c30); do not offer it
        // on the other tenants where it would grant nothing.
        if (! \App\Support\ModuleAccess::isAnyAgency(
            (int) (request()->route('agency')->id ?? auth()->user()->agency_id),
            \App\Support\ModuleAccess::DMW_USER_AGENCIES
        )) {
            unset($roles['dmw_user']);
        }

        return $roles;
    }

    /**
     * Abort 403 when the current user is not a super admin but tries to
     * create or promote a user to the super_admin role.
     */
    private function ensureCanAssignRole(?string $role): void
    {
        if ($role === 'super_admin' && auth()->user()->user_type !== 'super_admin') {
            abort(403, 'Only Super Admins can assign the Super Admin role.');
        }
    }
}
