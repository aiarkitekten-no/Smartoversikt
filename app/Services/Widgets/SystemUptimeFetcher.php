<?php
# START 5f8a2c9e7b4d / System uptime widget fetcher
# Hash: 5f8a2c9e7b4d
# Purpose: Fetch system uptime data using ReadonlyCommand

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemUptimeFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.uptime';
    
    /**
     * Refresh interval: 60 seconds
     */
    protected int $refreshIntervalSeconds = 60;
    
    /**
     * Fetch uptime data from /proc/uptime and uptime command
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        $data = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'uptime' => $this->getUptime(),
            'boot_time' => null,
            'load_average' => $this->getLoadAverage(),
            'cpu_cores' => $this->getCpuCores(),
            'reboot_required' => $this->checkRebootRequired(),
        ];
        
        // Calculate boot time
        if ($data['uptime']['seconds']) {
            $data['boot_time'] = Carbon::now()
                ->subSeconds($data['uptime']['seconds'])
                ->toIso8601String();
        }
        
        return $data;
    }
    
    /**
     * Get system uptime
     * 
     * @return array
     */
    protected function getUptime(): array
    {
        $result = ReadonlyCommand::run('cat /proc/uptime');
        
        if (!$result['success']) {
            Log::warning('Failed to read /proc/uptime', ['error' => $result['error']]);
            return [
                'seconds' => 0,
                'formatted' => 'Ukjent',
            ];
        }
        
        // /proc/uptime format: "123456.78 123456.78"
        // First number is total uptime in seconds
        $parts = explode(' ', trim($result['output']));
        $seconds = (int) floor((float) $parts[0]);
        
        return [
            'seconds' => $seconds,
            'formatted' => $this->formatUptime($seconds),
        ];
    }
    
    /**
     * Get load average from /proc/loadavg
     * 
     * @return array
     */
    protected function getLoadAverage(): array
    {
        $result = ReadonlyCommand::run('cat /proc/loadavg');
        
        if (!$result['success']) {
            Log::warning('Failed to read /proc/loadavg', ['error' => $result['error']]);
            return [
                '1min' => 0.0,
                '5min' => 0.0,
                '15min' => 0.0,
            ];
        }
        
        // /proc/loadavg format: "0.52 0.58 0.59 1/234 12345"
        $parts = explode(' ', trim($result['output']));
        
        return [
            '1min' => (float) ($parts[0] ?? 0.0),
            '5min' => (float) ($parts[1] ?? 0.0),
            '15min' => (float) ($parts[2] ?? 0.0),
        ];
    }
    
    /**
     * Format uptime seconds to human-readable string
     * 
     * @param int $seconds
     * @return string
     */
    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $parts = [];
        
        if ($days > 0) {
            $parts[] = $days . ' dag' . ($days > 1 ? 'er' : '');
        }
        
        if ($hours > 0) {
            $parts[] = $hours . ' time' . ($hours > 1 ? 'r' : '');
        }
        
        if ($minutes > 0 || empty($parts)) {
            $parts[] = $minutes . ' minutt' . ($minutes > 1 ? 'er' : '');
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Get number of CPU cores
     * 
     * @return int
     */
    protected function getCpuCores(): int
    {
        $result = ReadonlyCommand::run('nproc');
        
        if (!$result['success']) {
            // Fallback: try counting from /proc/cpuinfo
            $cpuInfoResult = ReadonlyCommand::run('grep -c ^processor /proc/cpuinfo');
            if ($cpuInfoResult['success']) {
                return (int) trim($cpuInfoResult['output']);
            }
            
            Log::warning('Failed to get CPU core count', ['error' => $result['error']]);
            return 2; // Default fallback
        }
        
        return (int) trim($result['output']);
    }
    
    /**
     * Check if system requires reboot
     * 
     * @return array
     */
    protected function checkRebootRequired(): array
    {
        $rebootRequired = false;
        $reason = null;
        $details = [];
        
        // Check for /var/run/reboot-required (Debian/Ubuntu)
        if (file_exists('/var/run/reboot-required')) {
            $rebootRequired = true;
            $reason = 'Systemoppdateringer';
            
            // Try to read the reason file
            if (file_exists('/var/run/reboot-required.pkgs')) {
                $packages = file_get_contents('/var/run/reboot-required.pkgs');
                $pkgList = array_filter(explode("\n", trim($packages)));
                $details = array_slice($pkgList, 0, 5); // First 5 packages
            }
        }
        
        // Check for kernel update (current running vs installed)
        $runningKernel = php_uname('r'); // Running kernel version
        
        // Try to get latest installed kernel
        $result = ReadonlyCommand::run('ls -t /boot/vmlinuz-* 2>/dev/null | head -1');
        if ($result['success'] && !empty(trim($result['output']))) {
            $latestKernel = basename(trim($result['output']));
            $latestKernel = str_replace('vmlinuz-', '', $latestKernel);
            
            if ($latestKernel !== $runningKernel) {
                $rebootRequired = true;
                $reason = 'Ny kjerne installert';
                $details[] = "Kjørende: {$runningKernel}";
                $details[] = "Ny versjon: {$latestKernel}";
            }
        }
        
        return [
            'required' => $rebootRequired,
            'reason' => $reason,
            'details' => $details,
            'running_kernel' => $runningKernel,
        ];
    }
}
# END 5f8a2c9e7b4d
