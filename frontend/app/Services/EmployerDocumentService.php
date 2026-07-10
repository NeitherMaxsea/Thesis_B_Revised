<?php

namespace App\Services;

use App\Models\EmployerDocument;
use App\Models\User;

class EmployerDocumentService
{
    public const REQUIRED_DOCUMENT_TYPES = [
        'DOLE Certificate',
        'BIR Certificate',
        'Business Permit',
        'DTI Certificate',
    ];

    public function refreshStatus(User $employer): string
    {
        $documents = EmployerDocument::query()->where('user_id', $employer->id)->get();
        $today = today();
        $documentsByType = $documents->keyBy('document_type');
        $hasAllRequiredDocuments = collect(self::REQUIRED_DOCUMENT_TYPES)
            ->every(fn (string $type) => $documentsByType->has($type));
        $hasExpiredDocument = $documents->contains(
            fn (EmployerDocument $document) => ! $document->expires_at || $document->expires_at->lte($today)
        );
        $status = $hasExpiredDocument || ! $hasAllRequiredDocuments ? 'expired' : 'valid';

        $documents->each(function (EmployerDocument $document) use ($today) {
            $documentStatus = ! $document->expires_at || $document->expires_at->lte($today)
                ? 'expired'
                : 'valid';

            if ($document->status !== $documentStatus) {
                $document->update(['status' => $documentStatus]);
            }
        });

        if ($employer->employer_document_status !== $status) {
            $employer->update(['employer_document_status' => $status]);
        }

        return $status;
    }
}
