<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Sys\ReadonlyCommand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    /**
     * Block an IP address in the firewall for 2 hours
     */
    public function blockIp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Ugyldig IP-adresse',
                'errors' => $validator->errors(),
            ], 400);
        }

        $ip = $request->input('ip');
        $reason = $request->input('reason', 'Dashboard manual block');
        
        // Validate IP is not local/private
        if ($this->isPrivateIp($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Kan ikke blokkere private/lokale IP-adresser',
            ], 400);
        }

        try {
            // Use iptables to block the IP for 2 hours (7200 seconds)
            // We use a temporary chain to make cleanup easier
            $commands = [
                // Ensure our custom chain exists
                "sudo iptables -N DASHBOARD_BLOCKS 2>/dev/null || true",
                "sudo iptables -C INPUT -j DASHBOARD_BLOCKS 2>/dev/null || sudo iptables -I INPUT 1 -j DASHBOARD_BLOCKS",
                
                // Add the block rule
                "sudo iptables -I DASHBOARD_BLOCKS 1 -s {$ip} -j DROP",
                
                // Schedule auto-unblock after 2 hours using at command
                "echo 'sudo iptables -D DASHBOARD_BLOCKS -s {$ip} -j DROP 2>/dev/null' | at now + 2 hours 2>/dev/null",
            ];

            foreach ($commands as $cmd) {
                $result = ReadonlyCommand::run($cmd);
                
                if (!$result['success']) {
                    Log::warning("Firewall command failed: {$cmd}", ['output' => $result['output']]);
                }
            }

            // Also add to fail2ban if available
            $this->addToFail2ban($ip, $reason);

            // Log the action
            Log::info("IP blocked via dashboard", [
                'ip' => $ip,
                'reason' => $reason,
                'user' => auth()->user()->email ?? 'unknown',
                'duration' => '2 hours',
            ]);

            return response()->json([
                'success' => true,
                'message' => "IP {$ip} er blokkert i 2 timer",
                'blocked_ip' => $ip,
                'duration' => '2 timer',
                'unblock_time' => now()->addHours(2)->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to block IP", [
                'ip' => $ip,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Kunne ikke blokkere IP-adresse: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if IP is private/local
     */
    protected function isPrivateIp(string $ip): bool
    {
        // Check if IP is in private ranges
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8',
            '::1/128',
            'fc00::/7',
        ];

        foreach ($privateRanges as $range) {
            if (strpos($range, '/') !== false) {
                [$subnet, $mask] = explode('/', $range);
                
                if (strpos($ip, ':') !== false) {
                    // IPv6 - simplified check
                    if ($ip === '::1') return true;
                    continue;
                }
                
                // IPv4
                $ipLong = ip2long($ip);
                $subnetLong = ip2long($subnet);
                $maskLong = -1 << (32 - (int)$mask);
                
                if (($ipLong & $maskLong) === ($subnetLong & $maskLong)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add IP to fail2ban jail if available
     */
    protected function addToFail2ban(string $ip, string $reason): void
    {
        // Try to add to fail2ban's recidive jail (for repeat offenders)
        $result = ReadonlyCommand::run("sudo fail2ban-client set recidive banip {$ip} 2>/dev/null");
        
        if (!$result['success']) {
            // Try sshd jail as fallback
            ReadonlyCommand::run("sudo fail2ban-client set sshd banip {$ip} 2>/dev/null");
        }
    }
}
