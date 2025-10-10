<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSecurityProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // CRITICAL: Block SQLite connections
        DB::beforeExecuting(function ($query, $bindings, $connection) {
            if ($connection === 'sqlite' || DB::connection()->getDriverName() === 'sqlite') {
                throw new RuntimeException(
                    "🚫 SECURITY VIOLATION: SQLite is FORBIDDEN in this application! " .
                    "Only MariaDB/MySQL connections are allowed. " .
                    "Check your .env file: DB_CONNECTION must be 'mysql'"
                );
            }
        });
        
        // Verify on boot that we're using MySQL/MariaDB
        $driver = config('database.default');
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            throw new RuntimeException(
                "🚫 CRITICAL ERROR: Database driver is set to '{$driver}'. " .
                "This application ONLY supports MariaDB/MySQL. " .
                "Set DB_CONNECTION=mysql in your .env file immediately!"
            );
        }
        
        // Double-check the actual connection
        try {
            $actualDriver = DB::connection()->getDriverName();
            if ($actualDriver === 'sqlite') {
                throw new RuntimeException(
                    "🚫 FATAL: Active database connection is SQLite! " .
                    "This is strictly forbidden. Application terminated."
                );
            }
        } catch (\Exception $e) {
            // Connection not yet established, will be checked on first query
        }
        
        $this->app->booted(function () {
            // Final check after app is fully booted
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                throw new RuntimeException(
                    "🚫 SECURITY BREACH DETECTED: SQLite connection active! " .
                    "Immediate termination required. Fix .env configuration!"
                );
            }
            
            \Log::info('✅ Database Security: Verified MariaDB/MySQL connection', [
                'driver' => $driver,
                'database' => DB::connection()->getDatabaseName(),
            ]);
        });
    }
}
