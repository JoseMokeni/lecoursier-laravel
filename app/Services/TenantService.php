<?php
namespace App\Services;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class TenantService
{
    /**
     * Get a specific tenant by ID
     *
     * @param int $id
     * @return Tenant|null
     */
    public function getTenant(string $id): ?Tenant
    {
        return Tenant::find($id);
    }

    /**
     * Activate a tenant
     *
     */
    public function activateTenant(string $id): ?Tenant
    {
        $tenant = Tenant::find($id);
        if ($tenant) {
            $tenant->status = 'active';
            $tenant->save();
        }
        return $tenant;
    }

    /**
     * Deactivate a tenant
     *
     */
    public function deactivateTenant(string $id): ?Tenant
    {
        $tenant = Tenant::find($id);
        if ($tenant) {
            $tenant->status = 'inactive';
            $tenant->save();
        }
        return $tenant;
    }

}
