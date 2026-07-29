<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDnsConfig extends Command
{
    protected $signature = 'email:check-dns {domain=latestdeal.in : Domain name to validate}';

    protected $description = 'Validate SPF, DKIM, and DMARC DNS deliverability records for sending domain';

    public function handle()
    {
        $domain = $this->argument('domain');
        $this->info("Checking Email Deliverability DNS Records for domain: {$domain}\n");

        $rows = [];

        // 1. Check SPF
        $spfFound = false;
        $spfValue = 'Missing';
        $txtRecords = @dns_get_record($domain, DNS_TXT) ?: [];
        foreach ($txtRecords as $rec) {
            if (isset($rec['txt']) && str_contains($rec['txt'], 'v=spf1')) {
                $spfFound = true;
                $spfValue = $rec['txt'];
                break;
            }
        }
        $rows[] = ['SPF Record', 'Required', $spfFound ? 'PASS' : 'FAIL', substr($spfValue, 0, 50)];

        // 2. Check DMARC
        $dmarcFound = false;
        $dmarcValue = 'Missing';
        $dmarcTxt = @dns_get_record("_dmarc.{$domain}", DNS_TXT) ?: [];
        foreach ($dmarcTxt as $rec) {
            if (isset($rec['txt']) && str_contains($rec['txt'], 'v=DMARC1')) {
                $dmarcFound = true;
                $dmarcValue = $rec['txt'];
                break;
            }
        }
        $rows[] = ['DMARC Record', 'Recommended', $dmarcFound ? 'PASS' : 'WARN', substr($dmarcValue, 0, 50)];

        // 3. Check DKIM (default selector)
        $dkimFound = false;
        $dkimValue = 'Missing';
        $dkimTxt = @dns_get_record("default._domainkey.{$domain}", DNS_TXT) ?: [];
        foreach ($dkimTxt as $rec) {
            if (isset($rec['txt'])) {
                $dkimFound = true;
                $dkimValue = $rec['txt'];
                break;
            }
        }
        $rows[] = ['DKIM Record (default)', 'Required', $dkimFound ? 'PASS' : 'WARN', substr($dkimValue, 0, 50)];

        // 4. Reverse DNS Guidance
        $rows[] = ['Reverse DNS (PTR)', 'Informational', 'INFO', 'Verify host PTR record matches SMTP IP on server'];

        $this->table(['Check', 'Severity', 'Status', 'Details'], $rows);

        return 0;
    }
}
