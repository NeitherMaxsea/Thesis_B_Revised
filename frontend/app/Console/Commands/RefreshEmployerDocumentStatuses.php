<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmployerDocumentService;
use Illuminate\Console\Command;

class RefreshEmployerDocumentStatuses extends Command
{
    protected $signature = 'employer-documents:refresh-statuses';

    protected $description = 'Refresh employer-document expiry statuses and hide expired employers from job matching.';

    public function handle(EmployerDocumentService $documents): int
    {
        $processed = 0;

        User::query()
            ->where('account_type', 'employer')
            ->orderBy('id')
            ->chunkById(100, function ($employers) use ($documents, &$processed) {
                foreach ($employers as $employer) {
                    $documents->refreshStatus($employer);
                    $processed++;
                }
            });

        $this->components->info("Refreshed document statuses for {$processed} employer account(s).");

        return self::SUCCESS;
    }
}
