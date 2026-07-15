<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class CsvImportService
{
    public function parse(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');
        $headers = null;

        while (($data = fgetcsv($handle)) !== false) {
            if ($headers === null) {
                $headers = array_map('trim', $data);

                continue;
            }

            if (count($data) !== count($headers)) {
                continue;
            }

            $row = array_combine($headers, array_map('trim', $data));
            $rows[] = $row;
        }

        fclose($handle);

        return [
            'headers' => $headers ?? [],
            'rows' => $rows,
            'total' => count($rows),
        ];
    }

    public function preview(UploadedFile $file, array $mapping): array
    {
        $parsed = $this->parse($file);
        $sample = array_slice($parsed['rows'], 0, 5);
        $mapped = [];

        foreach ($sample as $row) {
            $entry = [];
            foreach ($mapping as $field => $column) {
                if ($column && isset($row[$column])) {
                    $entry[$field] = $row[$column];
                }
            }
            $mapped[] = $entry;
        }

        return [
            'headers' => $parsed['headers'],
            'sample' => $mapped,
            'total' => $parsed['total'],
        ];
    }

    public function import(int $userId, UploadedFile $file, array $mapping, bool $hasHeader = true): array
    {
        $parsed = $this->parse($file);
        $imported = 0;
        $errors = [];

        $rows = $hasHeader ? $parsed['rows'] : $this->parseWithoutHeader($file);

        foreach ($rows as $index => $row) {
            try {
                $amount = $this->extractAmount($row, $mapping);
                $date = $this->extractDate($row, $mapping);
                $category = isset($mapping['category'], $row[$mapping['category']]) ? $row[$mapping['category']] : null;
                $description = isset($mapping['description'], $row[$mapping['description']]) ? $row[$mapping['description']] : null;

                if ($amount === null || $date === null) {
                    $errors[] = 'Row '.($index + 1).': Missing amount or date';

                    continue;
                }

                if ($amount >= 0) {
                    Income::create([
                        'user_id' => $userId,
                        'amount' => $amount,
                        'date' => $date,
                        'category' => $category,
                        'description' => $description,
                    ]);
                } else {
                    Expense::create([
                        'user_id' => $userId,
                        'amount' => abs($amount),
                        'date' => $date,
                        'category' => $category,
                        'description' => $description,
                    ]);
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Row '.($index + 1).': '.$e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
            'total' => count($rows),
        ];
    }

    private function parseWithoutHeader(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        while (($data = fgetcsv($handle)) !== false) {
            $rows[] = array_map('trim', $data);
        }

        fclose($handle);

        return $rows;
    }

    private function extractAmount(array $row, array $mapping): ?float
    {
        if (! isset($mapping['amount']) || ! isset($row[$mapping['amount']])) {
            return null;
        }

        $raw = preg_replace('/[^0-9.\-]/', '', $row[$mapping['amount']]);

        if ($raw === '' || $raw === '-' || $raw === '.') {
            return null;
        }

        return (float) $raw;
    }

    private function extractDate(array $row, array $mapping): ?Carbon
    {
        if (! isset($mapping['date']) || ! isset($row[$mapping['date']])) {
            return null;
        }

        $raw = $row[$mapping['date']];

        // Carbon::parse('') returns 'now' instead of throwing, so check explicitly
        if ($raw === '' || $raw === null) {
            return null;
        }

        try {
            return Carbon::parse($raw);
        } catch (\Exception) {
            return null;
        }
    }
}
