<?php
# START 7d2b4f1e9c8a / System disk usage widget fetcher
# Hash: 7d2b4f1e9c8a
# Purpose: Fetch disk usage data using ReadonlyCommand

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SystemDiskFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'system.disk';
    
    /**
     * Refresh interval: 120 seconds (disk usage changes slowly)
     */
    protected int $refreshIntervalSeconds = 120;
    
    /**
     * Fetch disk usage data
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        return [
            'timestamp' => Carbon::now()->toIso8601String(),
            'filesystems' => $this->getFilesystemUsage(),
            'inodes' => $this->getInodeUsage(),
        ];
    }
    
    /**
     * Get filesystem usage from df -B1
     * 
     * @return array
     */
    protected function getFilesystemUsage(): array
    {
        $result = ReadonlyCommand::run('df -B1');
        
        if (!$result['success']) {
            Log::warning('Failed to run df -B1', ['error' => $result['error']]);
            return [];
        }
        
        $lines = explode("\n", trim($result['output']));
        $filesystems = [];
        
        // Skip header line
        array_shift($lines);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            // Parse df output: Filesystem 1B-blocks Used Available Use% Mounted on
            $parts = preg_split('/\s+/', trim($line));
            
            if (count($parts) < 6) {
                continue;
            }
            
            $filesystem = $parts[0];
            $size = (int) $parts[1];
            $used = (int) $parts[2];
            $available = (int) $parts[3];
            $usePercent = (int) rtrim($parts[4], '%');
            $mountPoint = $parts[5];
            
            // Skip pseudo filesystems
            if ($this->shouldSkipFilesystem($filesystem, $mountPoint)) {
                continue;
            }
            
            $filesystems[] = [
                'filesystem' => $filesystem,
                'mount_point' => $mountPoint,
                'size' => $size,
                'used' => $used,
                'available' => $available,
                'use_percent' => $usePercent,
                'formatted' => [
                    'size' => $this->formatBytes($size),
                    'used' => $this->formatBytes($used),
                    'available' => $this->formatBytes($available),
                ],
            ];
        }
        
        return $filesystems;
    }
    
    /**
     * Get inode usage from df -i
     * 
     * @return array
     */
    protected function getInodeUsage(): array
    {
        $result = ReadonlyCommand::run('df -i');
        
        if (!$result['success']) {
            Log::warning('Failed to run df -i', ['error' => $result['error']]);
            return [];
        }
        
        $lines = explode("\n", trim($result['output']));
        $inodes = [];
        
        // Skip header line
        array_shift($lines);
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            // Parse df -i output: Filesystem Inodes IUsed IFree IUse% Mounted on
            $parts = preg_split('/\s+/', trim($line));
            
            if (count($parts) < 6) {
                continue;
            }
            
            $filesystem = $parts[0];
            $total = (int) $parts[1];
            $used = (int) $parts[2];
            $available = (int) $parts[3];
            $usePercent = (int) rtrim($parts[4], '%');
            $mountPoint = $parts[5];
            
            // Skip pseudo filesystems
            if ($this->shouldSkipFilesystem($filesystem, $mountPoint)) {
                continue;
            }
            
            $inodes[$mountPoint] = [
                'filesystem' => $filesystem,
                'mount_point' => $mountPoint,
                'total' => $total,
                'used' => $used,
                'available' => $available,
                'use_percent' => $usePercent,
            ];
        }
        
        return $inodes;
    }
    
    /**
     * Determine if filesystem should be skipped (pseudo filesystems)
     * 
     * @param string $filesystem
     * @param string $mountPoint
     * @return bool
     */
    protected function shouldSkipFilesystem(string $filesystem, string $mountPoint): bool
    {
        // Skip tmpfs, devtmpfs, proc, sys, etc.
        $skipTypes = ['tmpfs', 'devtmpfs', 'proc', 'sysfs', 'devfs', 'cgroup', 'overlay'];
        
        foreach ($skipTypes as $type) {
            if (str_starts_with($filesystem, $type)) {
                return true;
            }
        }
        
        // Skip special mount points
        $skipMounts = ['/dev', '/sys', '/proc', '/run', '/dev/shm'];
        
        foreach ($skipMounts as $mount) {
            if (str_starts_with($mountPoint, $mount)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format bytes to human-readable string
     * 
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);
        
        $value = $bytes / pow(1024, $power);
        
        return round($value, 2) . ' ' . $units[$power];
    }
}
# END 7d2b4f1e9c8a
