<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;

class ImportProductsFromItemsSpreadsheet extends Command
{
    protected $signature = 'products:import-items
                            {path? : Path to Items.xlsx (default: Items.xlsx in project root)}';

    protected $description = 'Import or update products from Items.xlsx (No., Description, Base Unit of Measure, Item Category Code).';

    public function handle(): int
    {
        $path = $this->argument('path') ?: base_path('Items.xlsx');

        if (! is_readable($path)) {
            $this->error('Spreadsheet not found or not readable: '.$path);

            return self::FAILURE;
        }

        $reader = new Reader;
        $reader->open($path);

        $batch = [];
        $batchSize = 250;
        $processed = 0;
        $skipped = 0;
        $now = now()->format('Y-m-d H:i:s');

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                if ($this->shouldSkipHeaderOrEmpty($row)) {
                    $skipped++;

                    continue;
                }

                $cells = $row->toArray();
                $sku = $this->cell($cells, 0);
                $name = $this->cell($cells, 1);

                if ($sku === '' || $name === '') {
                    $skipped++;

                    continue;
                }

                $batch[] = [
                    'sku' => $sku,
                    'name' => $name,
                    'unit_of_measure' => $this->nullableCell($cells, 2),
                    'item_category_code' => $this->nullableCell($cells, 3),
                    'default_unit_price' => 0,
                    'can_be_sampled' => 1,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $processed++;

                if (count($batch) >= $batchSize) {
                    $this->flushBatch($batch);
                    $batch = [];
                }
            }

            break;
        }

        $reader->close();

        if ($batch !== []) {
            $this->flushBatch($batch);
        }

        $this->info("Imported/updated {$processed} rows (skipped {$skipped} empty/header rows).");

        return self::SUCCESS;
    }

    /**
     * @param  array<int, mixed>  $cells
     */
    protected function nullableCell(array $cells, int $index): ?string
    {
        $v = $this->cell($cells, $index);

        return $v === '' ? null : $v;
    }

    /**
     * @param  array<int, mixed>  $cells
     */
    protected function cell(array $cells, int $index): string
    {
        if (! array_key_exists($index, $cells) || $cells[$index] === null) {
            return '';
        }

        return trim((string) $cells[$index]);
    }

    protected function shouldSkipHeaderOrEmpty(Row $row): bool
    {
        $cells = $row->toArray();
        $first = $this->cell($cells, 0);

        if ($first === '') {
            return true;
        }

        return strcasecmp($first, 'No.') === 0 || strcasecmp($first, 'No') === 0;
    }

    /**
     * @param  list<array<string, mixed>>  $batch
     */
    protected function flushBatch(array $batch): void
    {
        Product::query()->upsert(
            $batch,
            ['sku'],
            ['name', 'unit_of_measure', 'item_category_code', 'updated_at'],
        );
    }
}
