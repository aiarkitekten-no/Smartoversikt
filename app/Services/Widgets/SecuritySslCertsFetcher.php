<?php

namespace App\Services\Widgets;

use App\Support\Sys\ReadonlyCommand;
use Carbon\Carbon;

class SecuritySslCertsFetcher extends BaseWidgetFetcher
{
    protected string $widgetKey = 'security.ssl-certs';

    public function fetchData(): array
    {
        return [
            'certificates' => $this->getSslCertificates(),
            'summary' => $this->getSummary(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getSslCertificates(): array
    {
        $domains = $this->getDomainsToCheck();
        $certificates = [];

        foreach ($domains as $domain) {
            $certInfo = $this->checkSslCertificate($domain);
            if ($certInfo) {
                $certificates[] = $certInfo;
            }
        }

        // Sort by days remaining (critical first)
        usort($certificates, fn($a, $b) => $a['days_remaining'] <=> $b['days_remaining']);

        return $certificates;
    }

    protected function getDomainsToCheck(): array
    {
        // Priority 1: Manual configuration
        $domains = config('widgets.ssl_domains', []);
        
        if (!empty($domains)) {
            return $domains;
        }

        // Priority 2: Try to get from Plesk vhosts directory
        $vhostDomains = $this->getDomainsFromVhosts();
        if (!empty($vhostDomains)) {
            return $vhostDomains;
        }

        // Priority 3: Fallback to Nginx config
        return $this->getDomainsFromNginx();
    }

    protected function getDomainsFromVhosts(): array
    {
        $domains = [];

        // Read from /var/www/vhosts directory (Plesk structure)
        $result = ReadonlyCommand::run('ls -1 /var/www/vhosts 2>/dev/null');
        
        if (!$result['success'] || empty($result['output'])) {
            return $domains;
        }

        $vhostDirs = explode("\n", trim($result['output']));
        
        foreach ($vhostDirs as $dir) {
            $dir = trim($dir);
            
            // Skip system directories
            if (in_array($dir, ['default', 'chroot', 'fs', 'system', '.skel'])) {
                continue;
            }

            // Skip if not a valid domain format
            if (!str_contains($dir, '.')) {
                continue;
            }

            // Check if domain has SSL certificate by checking for conf/cert.pem
            $certCheck = ReadonlyCommand::run("test -f /var/www/vhosts/{$dir}/conf/cert.pem && echo 'exists'");
            
            if ($certCheck['success'] && trim($certCheck['output']) === 'exists') {
                $domains[] = $dir;
            }
        }

        // Limit to avoid performance issues
        return array_slice($domains, 0, 20);
    }

    protected function getDomainsFromPlesk(): array
    {
        // Plesk requires root access, not feasible from web context
        // Use vhosts directory approach instead
        return $this->getDomainsFromVhosts();
    }

    protected function getDomainsFromNginx(): array
    {
        $domains = [];

        // Fallback: Try to read from nginx config
        $result = ReadonlyCommand::run('grep -r "server_name" /etc/nginx/sites-enabled/ 2>/dev/null | grep -v "#" | grep -oP "server_name\s+\K[^;]+" | tr " " "\n" | grep -v "^$" | sort -u');
        
        if ($result['success'] && !empty(trim($result['output']))) {
            $lines = explode("\n", trim($result['output']));
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && $line !== '_' && !str_starts_with($line, '*')) {
                    $domains[] = $line;
                }
            }
        }

        // Limit to first 10 domains to avoid performance issues
        return array_slice($domains, 0, 10);
    }

    protected function getDomainsFromServerConfig(): array
    {
        // Deprecated - use getDomainsFromPlesk or getDomainsFromNginx
        return $this->getDomainsFromNginx();
    }

    protected function checkSslCertificate(string $domain): ?array
    {
        // Use OpenSSL for reliable certificate checking
        return $this->getOpenSslCertificateInfo($domain);
    }

    protected function getPleskCertificateInfo(string $domain): ?array
    {
        // Plesk requires root - not feasible from web context
        // Kept for reference if running via cron as root
        return null;
    }

    protected function getOpenSslCertificateInfo(string $domain): ?array
    {
        // Use openssl to check certificate
        $command = "echo | openssl s_client -servername {$domain} -connect {$domain}:443 2>/dev/null | openssl x509 -noout -dates -subject -issuer 2>/dev/null";
        $result = ReadonlyCommand::run($command);

        if (!$result['success'] || empty($result['output'])) {
            return [
                'domain' => $domain,
                'status' => 'error',
                'error' => 'Kunne ikke hente sertifikat',
                'days_remaining' => -999,
                'source' => 'openssl',
            ];
        }

        $output = $result['output'];
        
        // Parse expiry date
        if (preg_match('/notAfter=(.+)/', $output, $matches)) {
            $expiryDate = Carbon::parse($matches[1]);
            $daysRemaining = now()->diffInDays($expiryDate, false);
            
            // Determine status
            if ($daysRemaining < 0) {
                $status = 'expired';
            } elseif ($daysRemaining <= 7) {
                $status = 'critical';
            } elseif ($daysRemaining <= 14) {
                $status = 'warning';
            } elseif ($daysRemaining <= 30) {
                $status = 'attention';
            } else {
                $status = 'ok';
            }

            // Parse issuer
            $issuer = 'Unknown';
            if (preg_match('/issuer=.*?O\s*=\s*([^,\/]+)/', $output, $issuerMatches)) {
                $issuer = trim($issuerMatches[1]);
            }

            // Parse subject (certificate name)
            $subject = $domain;
            if (preg_match('/subject=.*?CN\s*=\s*([^,\/]+)/', $output, $subjectMatches)) {
                $subject = trim($subjectMatches[1]);
            }

            // Detect if Let's Encrypt (auto-renew)
            $isLetsEncrypt = str_contains($issuer, "Let's Encrypt") || 
                            str_contains($output, "Let's Encrypt");

            return [
                'domain' => $domain,
                'subject' => $subject,
                'issuer' => $issuer,
                'expiry_date' => $expiryDate->toIso8601String(),
                'expiry_formatted' => $expiryDate->format('d.m.Y'),
                'days_remaining' => (int)$daysRemaining,
                'status' => $status,
                'source' => 'openssl',
                'auto_renew' => $isLetsEncrypt,
            ];
        }

        return null;
    }

    protected function getSummary(): array
    {
        $certs = $this->getSslCertificates();
        
        $summary = [
            'total' => count($certs),
            'ok' => 0,
            'attention' => 0,
            'warning' => 0,
            'critical' => 0,
            'expired' => 0,
            'error' => 0,
        ];

        foreach ($certs as $cert) {
            if (isset($cert['status'])) {
                $summary[$cert['status']]++;
            }
        }

        return $summary;
    }
}
