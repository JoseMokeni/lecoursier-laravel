<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeUserMail;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $query = \App\Models\User::query();

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

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

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

        return redirect('/users')->with('success', 'Utilisateur créé avec succès! Un email de bienvenue a été envoyé.');
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = $this->userService->getUser($id);
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

        return redirect('/users')->with('success', 'Utilisateur supprimé avec succès!');
    }
}
