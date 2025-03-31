<?php

namespace Tests\Utilities;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

trait DatabaseRefresh
{
    /**
     * Refresh the database for testing
     */
    public function refreshTenantDatabase(): void
    {
        // Clear tenant databases
        $this->clearTenantDatabases();
    }

    /**
     * Clear all tenant databases
     */
    protected function clearTenantDatabases(): void
    {
        // Get the database directory path
        $databasePath = database_path();

        dump($databasePath);

        // Get all db files in the database directory
        $tenantDbPrefix = config('tenancy.database.prefix');
        $tenantDbs = glob($databasePath . '/' . $tenantDbPrefix . '*');

        // Remove each tenant database file
        foreach ($tenantDbs as $dbFile) {
            if (File::exists($dbFile)) {
                File::delete($dbFile);
            }
        }
    }
}
