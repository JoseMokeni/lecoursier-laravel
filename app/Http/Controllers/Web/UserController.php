<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeUserMail;
use App\Models\Tenant;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users with server-side filtering and pagination
     */
    public function index(Request $request)
    {
        // Create cache key based on all current filters and pagination
        $cacheKey = 'users.list.' . md5(json_encode([
            'role' => $request->role,
            'status' => $request->status,
            'search' => $request->search,
            'page' => $request->page ?? 1
        ]));

        // Get users from cache or database (cache for 30 minutes)
        $users = Cache::remember($cacheKey, 1800, function () use ($request) {
            $query = User::query();

            // Apply filters if they exist in the request
            if ($request->filled('role') && in_array($request->role, ['admin', 'user'])) {
                $query->where('role', $request->role);
            }
            if ($request->filled('status') && in_array($request->status, ['active', 'inactive'])) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            return $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        });

        return view('pages.users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('pages.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        // Define basic validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|max:255|unique:users',
        ];

        // Only the main admin can create admin users
        if ($request->input('role') === 'admin' && $currentUser->username !== session('tenant_id')) {
            // Force role to be 'user' if not main admin
            $request->merge(['role' => 'user']);
        }

        $rules['role'] = ['required', Rule::in(['admin', 'user'])];
        $validated = $request->validate($rules);

        // Add default status for new users
        $validated['status'] = 'active';

        // Store the original password before it gets hashed
        $originalPassword = $validated['password'];

        // Create the user
        $user = $this->userService->createUser($validated);

        // Prepare user data for the welcome email
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'original_password' => $originalPassword
        ];

        // Send welcome email with credentials
        try {
            Mail::to($validated['email'])
                ->send(new WelcomeUserMail($userData, session('tenant_id')));
        } catch (\Exception $e) {
            // Log the error but don't stop the process
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        // Clear user listing cache after creating a new user
        $this->clearUserCache();

        return redirect('/users')->with('success', 'Utilisateur créé avec succès! Un email de bienvenue a été envoyé.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        // Cache the user data for edit form (5 minutes)
        $user = Cache::remember('user.edit.' . $id, 300, function () use ($id) {
            return $this->userService->getUser($id);
        });

        $currentUser = Auth::user();

        if (!$user) {
            return redirect('/users')->with('error', 'Utilisateur non trouvé');
        }

        // Check permissions: only main admin can edit other admins
        if ($user->role == 'admin' && $user->id != $currentUser->id && $currentUser->username !== session('tenant_id')) {
            return redirect('/users')->with('error', 'Vous n\'avez pas la permission de modifier cet administrateur');
        }

        return view('pages.users.edit', ['user' => $user]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = $this->userService->getUser($id);
        $currentUser = Auth::user();

        if (!$user) {
            return redirect('/users')->with('error', 'Utilisateur non trouvé');
        }

        // Check permissions: only main admin can update other admins
        if ($user->role == 'admin' && $user->id != $currentUser->id && $currentUser->username !== session('tenant_id')) {
            return redirect('/users')->with('error', 'Vous n\'avez pas la permission de modifier cet administrateur');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
        ];

        // Role update permission handling
        if ($user->username === session('tenant_id')) {
            // Nobody can change the main admin's role, remove from request
            $request->request->remove('role');
        } else if ($currentUser->username === session('tenant_id')) {
            // Only main admin can change other users' roles
            $rules['role'] = ['required', Rule::in(['admin', 'user'])];
        } else {
            // Non-main admin cannot change roles, preserve existing role
            $request->request->remove('role');
        }

        // Status update permission handling
        if ($user->username === session('tenant_id')) {
            // Nobody can change the main admin's status, remove from request
            $request->request->remove('status');
        } else if ($user->role == 'admin') {
            // Only main admin can update other admin status
            if ($currentUser->username === session('tenant_id')) {
                $rules['status'] = ['required', Rule::in(['active', 'inactive'])];
            } else {
                // Remove status from request to prevent updates by non-main admins
                $request->request->remove('status');
            }
        } else {
            // For regular users, any admin can update status
            $rules['status'] = ['required', Rule::in(['active', 'inactive'])];
        }

        // Track if password is being updated
        $passwordUpdated = false;
        $newPassword = null;

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $passwordUpdated = true;
            $newPassword = $request->input('password');
        }

        $validated = $request->validate($rules);

        // Add original role back if it was removed due to permissions
        if (!$request->has('role')) {
            if ($user->username === session('tenant_id')) {
                // Main admin role is always 'admin'
                $validated['role'] = 'admin';
            } else if ($currentUser->username !== session('tenant_id')) {
                $validated['role'] = $user->role;
            }
        }

        // Add original status back if it was removed due to permissions
        if (!$request->has('status')) {
            if ($user->username === session('tenant_id')) {
                // Main admin status is always active
                $validated['status'] = 'active';
            } else if ($user->role == 'admin' && $currentUser->username !== session('tenant_id')) {
                $validated['status'] = $user->status;
            }
        }

        $this->userService->updateUser($id, $validated);

        // Clear user caches after update
        $this->clearUserCache($id);

        // Send password changed email notification if password was updated
        if ($passwordUpdated) {
            try {
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'username' => $user->username
                ];

                // Only include the new password in the email if the admin changed another user's password
                $includePassword = ($currentUser->id !== $user->id);
                $passwordToSend = $includePassword ? $newPassword : null;

                Mail::to($validated['email'])
                    ->send(new PasswordChangedMail($userData, session('tenant_id'), $passwordToSend));
            } catch (\Exception $e) {
                // Log the error but don't stop the process
                Log::error('Failed to send password changed email: ' . $e->getMessage());
            }

            return redirect('/users')->with('success', 'Utilisateur mis à jour avec succès! Un email de notification de changement de mot de passe a été envoyé.');
        }

        return redirect('/users')->with('success', 'Utilisateur mis à jour avec succès!');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = $this->userService->getUser($id);
        $currentUser = Auth::user();

        // Check if user exists
        if (!$user) {
            return redirect('/users')->with('error', 'Utilisateur non trouvé');
        }

        // Check if trying to delete main admin (tenant ID check)
        if ($user->username === session('tenant_id')) {
            return redirect('/users')->with('error', 'L\'administrateur principal ne peut pas être supprimé');
        }

        // Only main admin can delete other admins
        if ($user->role == 'admin' && $currentUser->username !== session('tenant_id')) {
            return redirect('/users')->with('error', 'Seul l\'administrateur principal peut supprimer d\'autres administrateurs');
        }

        $deleted = $this->userService->deleteUser($id);
        if (!$deleted) {
            return redirect('/users')->with('error', 'Erreur lors de la suppression de l\'utilisateur');
        }

        // Clear user caches after deletion
        $this->clearUserCache($id);

        return redirect('/users')->with('success', 'Utilisateur supprimé avec succès!');
    }

    /**
     * Show the form for changing password
     */
    public function changePassword()
    {
        return view('pages.users.change-password');
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'company_code' => 'required|string',
            'username' => 'required|string',
            'old_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Find the tenant by the company code
        $tenant = Tenant::where('id', $request->company_code)->first();

        if (!$tenant) {
            return back()->withErrors(['company_code' => 'Code entreprise invalide']);
        }

        // Initialize tenancy for this tenant
        tenancy()->initialize($tenant->id);

        // Now find the user after tenancy is initialized
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['username' => 'Utilisateur introuvable']);
        }

        // Verify the old password directly (without authentication)
        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'Le mot de passe actuel est incorrect']);
        }

        // Update the password
        $user->password = bcrypt($request->password);
        $user->save();

        // exit the tenancy context
        tenancy()->end();

        return redirect()->route('login')->with('success', 'Mot de passe modifié avec succès. Veuillez vous connecter avec votre nouveau mot de passe.');
    }

    /**
     * Clear user related caches
     *
     * @param int|null $userId
     */
    private function clearUserCache($userId = null)
    {
        // Clear user listing cache (all patterns)
        Cache::flush('users.list.*');

        // If specific user ID provided, clear that user's cache
        if ($userId) {
            Cache::forget('user.edit.' . $userId);
        }
    }
}
