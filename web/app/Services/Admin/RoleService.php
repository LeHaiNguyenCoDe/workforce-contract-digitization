<?php

namespace App\Services\Admin;

use App\Models\Role;
use App\Models\Right;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * Get all roles
     */
    public function getAll(): Collection
    {
        return Role::with('rights')->orderBy('id')->get();
    }

    /**
     * Get role by ID
     */
    public function getById(int $id): Role
    {
        return Role::with('rights')->findOrFail($id);
    }

    /**
     * Create role
     */
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Update role
     */
    public function update(int $id, array $data): Role
    {
        $role = $this->getById($id);
        $role->update($data);
        return $role;
    }

    /**
     * Delete role
     */
    public function delete(int $id): bool
    {
        $role = $this->getById($id);
        return $role->delete();
    }

    /**
     * Sync permissions
     */
    public function syncPermissions(int $roleId, array $permissionNames): Role
    {
        $role = $this->getById($roleId);
        
        // Find rights by name (assuming name in FE matches name in DB)
        $rights = Right::whereIn('name', $permissionNames)->pluck('id');
        
        $role->rights()->sync($rights);
        
        return $role->load('rights');
    }
}
