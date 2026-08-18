<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateNumberSequence;
use App\Models\Lga;
use Illuminate\Support\Facades\DB;

/**
 * SRD 15.2 - locked, transactional certificate sequencing.
 * Format: {LGA_CODE}-{YEAR}-{SEQUENCE}, e.g. DAM-2026-000001.
 */
class CertificateNumberService
{
    public function allocate(Lga $lga, ?string $prefix = null): string
    {
        $prefix = $prefix ?? $this->lgaCode($lga);
        $year = now()->year;

        return DB::transaction(function () use ($lga, $prefix, $year) {
            $sequence = CertificateNumberSequence::query()
                ->where('lga_id', $lga->id)
                ->where('year', $year)
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = CertificateNumberSequence::create([
                    'lga_id' => $lga->id,
                    'year' => $year,
                    'prefix' => $prefix,
                    'next_value' => 1,
                    'padding' => 6,
                ]);
            }

            $value = $sequence->next_value;
            $sequence->next_value = $value + 1;
            $sequence->save();

            $number = $prefix.'-'.$year.'-'.str_pad((string) $value, $sequence->padding, '0', STR_PAD_LEFT);

            // Belt-and-braces: the unique constraint on certificates.certificate_number is authoritative.
            if (Certificate::where('certificate_number', $number)->exists()) {
                throw new \RuntimeException('Certificate number collision detected; retry allocation.');
            }

            return $number;
        });
    }

    public function lgaCode(Lga $lga): string
    {
        $map = [
            'Damboa' => 'DAM',
        ];

        if (isset($map[$lga->name])) {
            return $map[$lga->name];
        }

        $words = preg_split('/[\s\-]+/', strtoupper($lga->name));
        $code = '';

        foreach ($words as $word) {
            $code .= $word[0] ?? '';
        }

        return $code ?: strtoupper(substr($lga->code, 0, 3));
    }
}

