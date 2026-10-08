<?php

namespace App\Services;

use App\Models\BusinessTagCode;
use Illuminate\Support\Facades\DB;

class BusinessTagCodeService
{
    public const PREFIX = 'BT';
    public const REFERENCE_PREFIX = 'REF';
    public const MAX_ATTEMPTS = 10;

    /**
     * Generate $quantity unique tag codes for a business tag.
     * Format: BT + 8 digits  e.g. BT04829175
     *
     * Candidates are built in memory, then inserted with insertOrIgnore so the
     * unique index on tag_code rejects any collision (including concurrent
     * requests). Whatever was not inserted is regenerated until the quantity is met.
     *
     * @return array the codes that were created
     */
    public function generate(int $businessTagId, int $quantity): array
    {
        $created = [];

        DB::transaction(function () use ($businessTagId, $quantity, &$created) {
            $attempts = 0;

            while (count($created) < $quantity) {
                if (++$attempts > self::MAX_ATTEMPTS) {
                    throw new \RuntimeException('Unable to generate enough unique tag codes.');
                }

                $needed = $quantity - count($created);

                // Build unique tag codes and unique reference numbers in memory.
                $candidates = [];
                while (count($candidates) < $needed) {
                    $candidates[$this->randomCode()] = true;
                }
                $candidates = array_keys($candidates);

                $references = [];
                while (count($references) < $needed) {
                    $references[$this->randomReference()] = true;
                }
                $references = array_keys($references);

                // Drop candidates whose code or reference already exists, so we can tell which ones we inserted.
                $existingCodes = BusinessTagCode::whereIn('tag_code', $candidates)->pluck('tag_code')->all();
                $existingRefs = BusinessTagCode::whereIn('reference_no', $references)->pluck('reference_no')->all();

                $rows = [];
                $now = now();
                foreach ($candidates as $i => $code) {
                    if (in_array($code, $existingCodes, true) || in_array($references[$i], $existingRefs, true)) {
                        continue;
                    }
                    $rows[$code] = [
                        'business_tag_id' => $businessTagId,
                        'tag_code' => $code,
                        'reference_no' => $references[$i],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $fresh = array_keys($rows);

                foreach (array_chunk(array_values($rows), 500) as $chunk) {
                    BusinessTagCode::insertOrIgnore($chunk);
                }

                // Confirm which of the fresh codes actually belong to this tag now.
                $inserted = BusinessTagCode::where('business_tag_id', $businessTagId)
                    ->whereIn('tag_code', $fresh)
                    ->pluck('tag_code')
                    ->all();

                $created = array_merge($created, $inserted);
            }
        });

        return $created;
    }

    protected function randomCode(): string
    {
        return self::PREFIX . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    /**
     * Public identification number printed under the QR instead of the tag code.
     * Format: REF + 8 digits  e.g. REF48291736 (unrelated to the tag code).
     */
    protected function randomReference(): string
    {
        return self::REFERENCE_PREFIX . str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }
}
