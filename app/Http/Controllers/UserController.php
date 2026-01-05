<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    /**
     * Instantiate a new UserController instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-user|edit-user|delete-user', ['only' => ['index', 'show']]);
        $this->middleware('permission:create-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:edit-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:delete-user', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::all(), // Bu avtomatik soft delete filter qiladi
        ]);
    }

    // O'CHIRILGAN USERLARNI KO'RSATISH - YANGI METHOD
    public function trashed(): View
    {
        return view('admin.users.trashed', [
            'users' => User::onlyTrashed()->get(), // Faqat o'chirilganlar
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Role::pluck('name')->all();
        $projects = Project::all();
        return view('admin.users.create', compact(['roles', 'projects']));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        $user = User::create($input);
        $user->assignRole($request->roles);

        return redirect()->route('admin.users.index')->withSuccess('New user is added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        // Check Only Super Admin can update his own Profile
        if ($user->hasRole('Super Admin')) {
            if ($user->id != auth()->user()->id) {
                abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
            }
        }

        $projects = Project::all();

        // 🔥 previousProject ni yuklash
        $user->load(['project', 'previousProject']);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::pluck('name')->all(),
            'userRoles' => $user->roles->pluck('name')->all(),
            'projects' => $projects,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $input = $request->all();

        if (!empty($request->password)) {
            $input['password'] = Hash::make($request->password);
        } else {
            $input = $request->except('password');
        }

        // 🔥 YANGI: Agar loyiha o'zgargan bo'lsa va sana kiritilgan bo'lsa
        $oldProjectId = $user->project_id;
        $newProjectId = $request->project_id;

        if ($newProjectId && $oldProjectId != $newProjectId) {
            // Previous project_id ni saqlash
            $input['previous_project_id'] = $oldProjectId;

            // Agar custom sana kiritilgan bo'lsa, uni ishlatish
            if ($request->filled('project_change_date')) {
                $input['project_changed_at'] = $request->project_change_date;
            } else {
                // Aks holda bugungi sanani ishlatish
                $input['project_changed_at'] = now()->toDateString();
            }
        }

        $user->update($input);
        $user->syncRoles($request->roles);

        // Success message
        $message = 'User is updated successfully.';

        if ($oldProjectId != $newProjectId && $newProjectId) {
            $oldProject = \App\Models\Project::withTrashed()->find($oldProjectId);
            $newProject = \App\Models\Project::withTrashed()->find($newProjectId);

            $message .= sprintf(' Loyiha o\'zgartirildi: %s → %s (%s)', $oldProject ? $oldProject->name : 'N/A', $newProject ? $newProject->name : 'N/A', \Carbon\Carbon::parse($input['project_changed_at'])->format('d.m.Y'));
        }

        return redirect()->back()->withSuccess($message);
    }

    /**
     * Remove the specified resource from storage. (SOFT DELETE)
     */
    public function destroy(User $user): RedirectResponse
    {
        // About if user is Super Admin or User ID belongs to Auth User
        if ($user->hasRole('Super Admin') || $user->id == auth()->user()->id) {
            abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
        }

        $user->syncRoles([]);
        $user->delete(); // BU ENDI SOFT DELETE
        return redirect()->route('admin.users.index')->withSuccess('User is deleted successfully.');
    }

    // QAYTA TIKLASH - YANGI METHOD
    public function restore($id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.trashed')->withSuccess('User is restored successfully.');
    }

    // BUTUNLAY O'CHIRISH - YANGI METHOD
    public function forceDelete($id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        // Super Admin tekshiruvi
        if ($user->hasRole('Super Admin')) {
            abort(403, 'USER DOES NOT HAVE THE RIGHT PERMISSIONS');
        }

        $user->forceDelete(); // Haqiqiy o'chirish

        return redirect()->route('admin.users.trashed')->withSuccess('User is permanently deleted.');
    }
}
