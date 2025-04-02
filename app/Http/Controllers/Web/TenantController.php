<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display tenant settings page
     */
    public function settings()
    {
        $tenant = tenancy()->tenant;
        return view('pages.tenants.settings', compact('tenant'));
    }

    /**
     * Activate tenant
     */
    public function activate(Request $request, $id)
    {
        $tenant = $this->tenantService->activateTenant($id);

        if ($tenant) {
            return redirect()->route('tenant.settings')->with('success', 'Locataire activé avec succès.');
        }

        return redirect()->route('tenant.settings')->with('error', 'Impossible d\'activer le locataire.');
    }

    /**
     * Deactivate tenant
     */
    public function deactivate(Request $request, $id)
    {
        $tenant = $this->tenantService->deactivateTenant($id);

        if ($tenant) {
            return redirect()->route('tenant.settings')->with('success', 'Locataire désactivé avec succès.');
        }

        return redirect()->route('tenant.settings')->with('error', 'Impossible de désactiver le locataire.');
    }
}
