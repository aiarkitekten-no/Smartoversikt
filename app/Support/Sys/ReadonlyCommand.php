<?php
# START a3f9c2e1b5d8 / Read-only command wrapper
# Hash: a3f9c2e1b5d8
# Purpose: Sikker wrapper for read-only OS-kommandoer

namespace App\Support\Sys;

use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReadonlyCommand
{
    /**
     * Whitelist av tillatte kommandoer (kun read-only)
     * 
     * @var array
     */
    protected static array $whitelist = [
        'cat /proc/loadavg',
        'cat /proc/meminfo',
        'cat /proc/uptime',
        'cat /proc/stat',
        'cat /proc/cpuinfo',
        'cat /proc/diskstats',
        'cat /proc/net/dev',
        'df -B1',
        'df -h',
        'df -i',
        'uptime',
        'free -b',
        'iostat',
        'nproc',
        'grep -c ^processor /proc/cpuinfo',
        'ps aux',
        'ls -t /boot/vmlinuz-*',
        'cat /sys/class/thermal/thermal_zone0/temp',
        'cat /sys/class/thermal/thermal_zone1/temp',
        'cat /sys/class/thermal/thermal_zone2/temp',
        'find /sys/class/thermal',
        'postqueue -p',
        'fail2ban-client status',
        'mdadm --detail',
        'smartctl -H',
        'openssl s_client',
        'php -v',
        'systemctl status',
        'journalctl',
        'grep -r "server_name" /etc/nginx/sites-enabled/',
        'tail -n',
        'test -f',
        'grep',
        'ls -1 /var/www/vhosts',
        'plesk bin site -l',
        'plesk bin certificate -l',
        'plesk bin certificate -i',
        'echo |',
        'sudo iptables -N',
        'sudo iptables -C',
        'sudo iptables -I',
        'sudo iptables -D',
        'sudo fail2ban-client set',
        'at now + 2 hours',
    ];
    
    /**
     * Blacklist av farlige mønstre (destruktive operasjoner)
     * 
     * @var array
     */
    protected static array $blacklist = [
        'rm',
        'delete',
        'drop',
        'truncate',
        'mv',
        'chmod',
        'chown',
        '>',
        '>>',
        ';',
        '&&',
        '||',
        '`',
        '$(',
        'eval',
        'exec',
        'system',
    ];
    
    /**
     * Timeout for kommandoer (sekunder)
     * 
     * @var int
     */
    protected static int $timeout = 10;
    
    /**
     * Kjør en whitelisted read-only kommando
     * 
     * @param string $command
     * @param array $args
     * @return array ['success' => bool, 'output' => string, 'error' => string|null]
     */
    public static function run(string $command, array $args = []): array
    {
        // Valider at kommandoen er whitelisted
        if (!self::isWhitelisted($command)) {
            Log::warning('ReadonlyCommand: Blokkert ikke-whitelisted kommando', [
                'command' => $command,
                'args' => $args,
            ]);
            
            return [
                'success' => false,
                'output' => '',
                'error' => 'Command not whitelisted',
            ];
        }
        
        // Sjekk for farlige mønstre
        $fullCommand = $command . ' ' . implode(' ', $args);
        if (self::containsBlacklisted($fullCommand)) {
            Log::warning('ReadonlyCommand: Blokkert farlig mønster', [
                'command' => $fullCommand,
            ]);
            
            return [
                'success' => false,
                'output' => '',
                'error' => 'Dangerous pattern detected',
            ];
        }
        
        // Sanitér argumenter
        $sanitizedArgs = array_map(function ($arg) {
            return escapeshellarg($arg);
        }, $args);
        
        $fullCommand = $command . ' ' . implode(' ', $sanitizedArgs);
        
        // Kjør kommandoen med timeout
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        
        $process = proc_open($fullCommand, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            return [
                'success' => false,
                'output' => '',
                'error' => 'Failed to start process',
            ];
        }
        
        // Les output med timeout
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        
        $output = '';
        $error = '';
        $start = time();
        
        while (time() - $start < self::$timeout) {
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            
            usleep(100000); // 100ms
        }
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $returnCode = proc_close($process);
        
        $success = $returnCode === 0;
        
        if (!$success) {
            Log::info('ReadonlyCommand: Kommando feilet', [
                'command' => $command,
                'return_code' => $returnCode,
                'error' => substr($error, 0, 200),
            ]);
        }
        
        return [
            'success' => $success,
            'output' => $output,
            'error' => $error ?: null,
        ];
    }
    
    /**
     * Sjekk om kommando er whitelisted
     * 
     * @param string $command
     * @return bool
     */
    protected static function isWhitelisted(string $command): bool
    {
        foreach (self::$whitelist as $allowed) {
            if (str_starts_with($command, $allowed)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sjekk om kommando inneholder blacklisted mønstre
     * 
     * @param string $command
     * @return bool
     */
    protected static function containsBlacklisted(string $command): bool
    {
        // Allow pipes for specific safe commands (openssl, grep chains)
        $safePipeCommands = [
            'openssl s_client',
            'openssl x509',
            'echo |',
            'grep |',
            'tail |',
        ];
        
        $hasSafePipe = false;
        foreach ($safePipeCommands as $safeCmd) {
            if (str_contains($command, $safeCmd)) {
                $hasSafePipe = true;
                break;
            }
        }
        
        // Remove safe redirects before checking (stderr to /dev/null is safe)
        $cleanCommand = str_replace('2>/dev/null', '', $command);
        $cleanCommand = str_replace('2>&1', '', $cleanCommand);
        
        foreach (self::$blacklist as $dangerous) {
            // Skip pipe check if this is a safe piped command
            if ($dangerous === '|' && $hasSafePipe) {
                continue;
            }
            
            // Check cleaned command (without safe redirects)
            if (str_contains($cleanCommand, $dangerous)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Hent whitelisted kommandoer (for debugging)
     * 
     * @return array
     */
    public static function getWhitelist(): array
    {
        return self::$whitelist;
    }
}
# SLUTT a3f9c2e1b5d8
