<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get a specific user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUser(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Get all users
     *
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return User::all();
    }

    /**
     * Get users paginated
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getUsersByRole(string $role): Collection
    {
        return User::where('role', $role)->get();
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return User::create($data);
    }

    /**
     * Update user information
     *
     * @param int $id
     * @param array $data
     * @return User|null
     */
    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->getUser($id);

        if (!$user) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUser($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    /**
     * Search users by name or email
     *
     * @param string $query
     * @return Collection
     */
    public function searchUsers(string $query): Collection
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }

    /**
     * Count total users
     *
     * @return int
     */
    public function countUsers(): int
    {
        return User::count();
    }
}
