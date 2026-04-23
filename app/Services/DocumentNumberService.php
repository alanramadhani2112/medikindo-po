<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

/**
 * DocumentNumberService
 *
 * Generates consistent, sequential, human-readable document numbers.
 * Uses database-level locking (lockForUpdate) to prevent duplicates
 * under concurrent requests.
 *
 * Formats:
 *   PO  → PO/{ORG}/{YYYY}/{MM}/{SEQ}       e.g. PO/RSU/2026/04/0001
 *   GR  → GR/{ORG}/{YYYY}/{MM}/{PO-SEQ}/{GR-SEQ}  e.g. GR/RSU/2026/04/0001/001
 *   INV → INV/{ORG}/{YYYY}/{MM}/{SEQ}      e.g. INV/RSU/2026/04/0001
 *
 * SCOPE: Medikindo → RS/Klinik only.
 * Supplier Invoice numbers (SI-) are NOT managed here — those come from distributors.
 */
class DocumentNumberService
{
    private const DOC_PO  = 'PO';
    private const DOC_GR  = 'GR';
    private const DOC_INV = 'INV';

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    /**
     * Generate PO number.
     * Format: PO/{ORG}/{YYYY}/{MM}/{SEQ}
     */
    public function generatePONumber(int $organizationId): string
    {
        $orgCode = $this->resolveOrgCode($organizationId);
        $seq     = $this->nextSequence(self::DOC_PO, $orgCode);

        return sprintf(
            'PO/%s/%s/%s/%s',
            $orgCode,
            now()->format('Y'),
            now()->format('m'),
            str_pad($seq, 4, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Generate GR number.
     * Format: GR/{ORG}/{YYYY}/{MM}/{PO-SEQ}/{GR-SEQ}
     *
     * PO-SEQ is extracted from the PO number (last segment).
     * GR-SEQ is a monthly sequence per org.
     */
    public function generateGRNumber(int $organizationId, string $poNumber): string
    {
        $orgCode = $this->resolveOrgCode($organizationId);
        $poSeq   = $this->extractPoSeq($poNumber);
        $grSeq   = $this->nextSequence(self::DOC_GR, $orgCode);

        return sprintf(
            'GR/%s/%s/%s/%s/%s',
            $orgCode,
            now()->format('Y'),
            now()->format('m'),
            $poSeq,
            str_pad($grSeq, 3, '0', STR_PAD_LEFT),
        );
    }

    /**
     * Generate Customer Invoice (AR) number.
     * Format: INV/{ORG}/{YYYY}/{MM}/{SEQ}
     */
    public function generateInvoiceNumber(int $organizationId): string
    {
        $orgCode = $this->resolveOrgCode($organizationId);
        $seq     = $this->nextSequence(self::DOC_INV, $orgCode);

        return sprintf(
            'INV/%s/%s/%s/%s',
            $orgCode,
            now()->format('Y'),
            now()->format('m'),
            str_pad($seq, 4, '0', STR_PAD_LEFT),
        );
    }

    // -----------------------------------------------------------------------
    // Core: atomic sequence increment with row-level lock
    // -----------------------------------------------------------------------

    /**
     * Atomically increment and return the next sequence number.
     * Uses INSERT ... ON DUPLICATE KEY UPDATE + lockForUpdate to prevent
     * race conditions under concurrent requests.
     *
     * Must be called inside a DB::transaction().
     */
    private function nextSequence(string $docType, string $orgCode): int
    {
        // Ensure this is called within a transaction
        if (DB::transactionLevel() === 0) {
            throw new \RuntimeException('nextSequence() must be called within a database transaction');
        }

        $year  = (int) now()->format('Y');
        $month = (int) now()->format('m');

        // Upsert the row — creates if not exists, increments if exists
        DB::statement("
            INSERT INTO document_sequences (doc_type, org_code, year, month, last_number, created_at, updated_at)
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                last_number = last_number + 1,
                updated_at  = NOW()
        ", [$docType, $orgCode, $year, $month]);

        // Read back the current value with a lock to ensure consistency
        $row = DB::table('document_sequences')
            ->where('doc_type', $docType)
            ->where('org_code', $orgCode)
            ->where('year', $year)
            ->where('month', $month)
            ->lockForUpdate()
            ->value('last_number');

        return (int) $row;
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Resolve organization code from ID.
     * Falls back to 'ORG' if code is not set.
     */
    private function resolveOrgCode(int $organizationId): string
    {
        $code = Organization::withoutGlobalScopes()
            ->where('id', $organizationId)
            ->value('code');

        if (! $code) {
            return 'ORG';
        }

        // Uppercase, max 10 chars, alphanumeric + hyphen only
        return strtoupper(preg_replace('/[^A-Z0-9\-]/i', '', $code));
    }

    /**
     * Extract the sequence segment from a PO number.
     * Handles both new format (PO/ORG/YYYY/MM/SEQ) and legacy (PO-YYYYMMDD-NNNN).
     */
    private function extractPoSeq(string $poNumber): string
    {
        // New format: PO/ORG/YYYY/MM/0001 → last segment
        if (str_contains($poNumber, '/')) {
            $parts = explode('/', $poNumber);
            return end($parts);
        }

        // Legacy format: PO-20260422-0001 → last segment after last dash
        $parts = explode('-', $poNumber);
        return end($parts);
    }
}
