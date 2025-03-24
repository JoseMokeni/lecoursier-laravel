<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = $this->userService->getUsers();
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|max:255|unique:users',
            'role' => ['required', Rule::in(['admin', 'user'])]
        ]);

        $this->userService->createUser($validated);

        return redirect('/users')->with('success', 'Utilisateur créé avec succès!');
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
            'role' => ['required', Rule::in(['admin', 'user'])],
            'status' => ['required', Rule::in(['active', 'inactive'])]
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $this->userService->updateUser($id, $validated);

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
