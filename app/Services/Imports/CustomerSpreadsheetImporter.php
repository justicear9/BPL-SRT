<?php

namespace App\Services\Imports;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use Throwable;

final class CustomerSpreadsheetImporter
{
    private const int MAX_ROWS = 5000;

    /**
     * Normalized header (snake_case) => Customer attribute name.
     *
     * @var array<string, string>
     */
    private const HEADER_SYNONYMS = [
        'customer_name' => 'name',
        'business_name' => 'name',
        'company' => 'name',
        'customer_type' => 'type',
        'kind' => 'type',
        'address' => 'address_line',
        'street' => 'address_line',
        'latitude' => 'shop_latitude',
        'longitude' => 'shop_longitude',
        'rep_email' => 'assigned_user_email',
        'assigned_rep_email' => 'assigned_user_email',
        'sales_rep_email' => 'assigned_user_email',
        'assigned_username' => 'assigned_user_email',
        'assigned_user' => 'assigned_user_email',
        'assignee' => 'assigned_user_email',
    ];

    /**
     * Normalized assignee label (lowercased trimmed) => user id.
     *
     * @var array<string, int>
     */
    private array $assigneeKeyToUserId = [];

    private int $importUsersCreated = 0;

    /**
     * Usernames reserved during this import (avoid duplicate creates before flush).
     *
     * @var array<string, true>
     */
    private array $claimedImportUsernames = [];

    /**
     * @return array{created: int, users_created: int, errors: list<array{line: int, message: string}>}
     */
    public function importFromPath(string $absolutePath): array
    {
        $errors = [];
        $rows = $this->readRows($absolutePath);

        if ($rows === []) {
            return ['created' => 0, 'users_created' => 0, 'errors' => [['line' => 1, 'message' => __('The file is empty.')]]];
        }

        $headerRow = array_shift($rows);
        $columnMap = $this->buildColumnMap($headerRow);

        if (! isset($columnMap['name'], $columnMap['type'])) {
            return [
                'created' => 0,
                'users_created' => 0,
                'errors' => [['line' => 1, 'message' => __('The sheet must include columns named "name" and "type".')]],
            ];
        }

        $created = 0;
        $usersCreated = 0;

        try {
            DB::transaction(function () use ($rows, $columnMap, &$errors, &$created, &$usersCreated): void {
                $this->assigneeKeyToUserId = [];
                $this->claimedImportUsernames = [];
                $this->importUsersCreated = 0;

                $lineNumber = 2;
                $validPayloads = [];
                $nonBlankRowCount = 0;

                foreach ($rows as $cells) {
                    if ($this->rowIsBlank($cells)) {
                        $lineNumber++;

                        continue;
                    }

                    if ($nonBlankRowCount >= self::MAX_ROWS) {
                        $errors[] = ['line' => $lineNumber, 'message' => __('Row limit exceeded (:max data rows).', ['max' => self::MAX_ROWS])];
                        break;
                    }

                    $assoc = $this->rowToAssoc($cells, $columnMap);
                    $validation = $this->validateRow($assoc, $lineNumber);

                    if ($validation['errors'] !== []) {
                        foreach ($validation['errors'] as $message) {
                            $errors[] = ['line' => $lineNumber, 'message' => $message];
                        }
                    } else {
                        $validPayloads[] = $validation['data'];
                    }

                    $nonBlankRowCount++;
                    $lineNumber++;
                }

                foreach ($validPayloads as $data) {
                    Customer::query()->create($data);
                    $created++;
                }

                $usersCreated = $this->importUsersCreated;
            });
        } catch (Throwable $e) {
            return [
                'created' => 0,
                'users_created' => 0,
                'errors' => [['line' => 0, 'message' => __('Import failed: :msg', ['msg' => $e->getMessage()])]],
            ];
        }

        return ['created' => $created, 'users_created' => $usersCreated, 'errors' => $errors];
    }

    /**
     * @return list<list<mixed>>
     */
    private function readRows(string $absolutePath): array
    {
        try {
            $reader = ReaderFactory::createFromFile($absolutePath);
        } catch (UnsupportedTypeException) {
            return [];
        }

        $rows = [];

        try {
            $reader->open($absolutePath);

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $cells = [];
                    foreach ($row->getCells() as $cell) {
                        $cells[] = $cell->getValue();
                    }
                    $rows[] = $cells;
                }

                break;
            }
        } catch (Throwable) {
            return [];
        } finally {
            $reader->close();
        }

        return $rows;
    }

    /**
     * @param  list<mixed>  $headerRow
     * @return array<string, int> attribute => column index
     */
    private function buildColumnMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $raw) {
            $normalized = $this->normalizeHeader((string) $raw);
            if ($normalized === '') {
                continue;
            }

            $field = self::HEADER_SYNONYMS[$normalized] ?? $normalized;
            $map[$field] = (int) $index;
        }

        return $map;
    }

    private function normalizeHeader(string $raw): string
    {
        $s = trim(mb_strtolower($raw));
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
        $s = str_replace([' ', '-'], '_', $s);

        return preg_replace('/_+/', '_', $s) ?? $s;
    }

    /**
     * @param  list<mixed>  $cells
     */
    private function rowIsBlank(array $cells): bool
    {
        foreach ($cells as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<mixed>  $cells
     * @param  array<string, int>  $columnMap
     * @return array<string, mixed>
     */
    private function rowToAssoc(array $cells, array $columnMap): array
    {
        $out = [];

        foreach ($columnMap as $field => $index) {
            $out[$field] = $cells[$index] ?? null;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $assoc
     * @return array{data: array<string, mixed>|null, errors: list<string>}
     */
    private function validateRow(array $assoc, int $lineNumber): array
    {
        $name = isset($assoc['name']) ? trim((string) $assoc['name']) : '';

        if ($name === '') {
            return [
                'data' => null,
                'errors' => [__('Line :line: a customer name is required.', ['line' => $lineNumber])],
            ];
        }

        $typeEnum = $this->parseType($assoc['type'] ?? null);

        if ($typeEnum === null && ($assoc['type'] ?? null) !== null && trim((string) $assoc['type']) !== '') {
            return [
                'data' => null,
                'errors' => [__('Line :line: invalid type ":raw" (use pharmacy, hospital, wholesaler, chemical_shop, wholesale retailer, or chemical shop).', [
                    'line' => $lineNumber,
                    'raw' => (string) $assoc['type'],
                ])],
            ];
        }

        $assignedUserId = null;
        $assigneeRaw = isset($assoc['assigned_user_email']) ? trim((string) $assoc['assigned_user_email']) : '';

        if ($assigneeRaw !== '' && $typeEnum !== null) {
            [$assignedUserId, $assigneeErrors] = $this->resolveAssigneeUserId($assigneeRaw, $lineNumber);

            if ($assigneeErrors !== []) {
                return ['data' => null, 'errors' => $assigneeErrors];
            }
        }

        $data = [
            'name' => $name,
            'type' => $typeEnum?->value,
            'phone' => $this->nullableString($assoc['phone'] ?? null, 64),
            'address_line' => $this->nullableString($assoc['address_line'] ?? null, 255),
            'city' => $this->nullableString($assoc['city'] ?? null, 120),
            'region' => $this->nullableString($assoc['region'] ?? null, 120),
            'shop_latitude' => $this->nullableFloat($assoc['shop_latitude'] ?? null),
            'shop_longitude' => $this->nullableFloat($assoc['shop_longitude'] ?? null),
            'assigned_user_id' => $assignedUserId,
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CustomerType::class)],
            'phone' => ['nullable', 'string', 'max:64'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'shop_latitude' => ['nullable', 'numeric'],
            'shop_longitude' => ['nullable', 'numeric'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if ($validator->fails()) {
            $messages = [];
            foreach ($validator->errors()->all() as $message) {
                $messages[] = __('Line :line: :msg', ['line' => $lineNumber, 'msg' => $message]);
            }

            if ($typeEnum === null && ($assoc['type'] ?? null) !== null && trim((string) $assoc['type']) !== '') {
                $messages[] = __('Line :line: invalid type ":raw" (use pharmacy, hospital, wholesaler, or chemical_shop).', [
                    'line' => $lineNumber,
                    'raw' => (string) $assoc['type'],
                ]);
            }

            return ['data' => null, 'errors' => $messages !== [] ? $messages : [__('Line :line: invalid row.', ['line' => $lineNumber])]];
        }

        $out = $validator->validated();
        $out['type'] = CustomerType::from($out['type']);

        return ['data' => $out, 'errors' => []];
    }

    private function parseType(mixed $raw): ?CustomerType
    {
        if ($raw === null) {
            return null;
        }

        if (is_float($raw) || is_int($raw)) {
            return null;
        }

        $s = strtolower(trim((string) $raw));
        $s = str_replace([' ', '-'], '_', $s);
        $s = preg_replace('/_+/', '_', $s) ?? $s;

        $try = CustomerType::tryFrom($s);
        if ($try !== null) {
            return $try;
        }

        return match ($s) {
            'wholesale_reseller', 'wholesale', 'reseller', 'wholesale_retailer' => CustomerType::Wholesaler,
            'chemical', 'chem_shop' => CustomerType::ChemicalShop,
            default => null,
        };
    }

    /**
     * @return array{0: int|null, 1: list<string>}
     */
    private function resolveAssigneeUserId(string $label, int $lineNumber): array
    {
        $key = mb_strtolower($label);

        if ($key === '') {
            return [null, []];
        }

        if (isset($this->assigneeKeyToUserId[$key])) {
            return [$this->assigneeKeyToUserId[$key], []];
        }

        $user = User::query()
            ->where(function ($q) use ($key): void {
                $q->whereRaw('LOWER(username) = ?', [$key])
                    ->orWhereRaw('LOWER(email) = ?', [$key]);
            })
            ->first();

        if ($user !== null) {
            $this->assigneeKeyToUserId[$key] = $user->id;

            return [$user->id, []];
        }

        if (str_contains($label, '@')) {
            return [
                null,
                [__('Line :line: no user found with email :email.', ['line' => $lineNumber, 'email' => $label])],
            ];
        }

        $username = $this->allocateUniqueUsernameFromLabel($label);
        $email = $this->allocateUniqueImportedEmail($username);

        $user = User::query()->create([
            'name' => $label,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => UserRole::SalesRep,
        ]);

        if ($user->wasRecentlyCreated) {
            $this->importUsersCreated++;
        }

        $this->assigneeKeyToUserId[$key] = $user->id;

        return [$user->id, []];
    }

    private function usernameBaseFromLabel(string $label): string
    {
        $s = mb_strtolower(trim($label));
        $s = preg_replace('/[^a-z0-9]+/', '_', $s) ?? '';
        $s = trim($s, '_');

        if ($s === '' || ! preg_match('/^[a-z0-9]/', $s)) {
            $s = 'rep_'.substr(sha1($label), 0, 10);
        }

        return mb_substr($s, 0, 64);
    }

    private function allocateUniqueUsernameFromLabel(string $label): string
    {
        $base = $this->usernameBaseFromLabel($label);
        $candidate = $base;
        $n = 0;

        while (
            User::query()->where('username', $candidate)->exists()
            || isset($this->claimedImportUsernames[$candidate])
        ) {
            $suffix = '_'.(++$n);
            $candidate = mb_substr($base, 0, max(1, 64 - mb_strlen($suffix))).$suffix;
        }

        $this->claimedImportUsernames[$candidate] = true;

        return $candidate;
    }

    private function allocateUniqueImportedEmail(string $username): string
    {
        $domain = 'customers.import';
        $local = mb_substr($username, 0, 60);
        $candidate = "{$local}@{$domain}";
        $n = 0;

        while (User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($candidate)])->exists()) {
            $n++;
            $suffix = '.'.$n;
            $candidate = mb_substr($local, 0, max(1, 60 - mb_strlen($suffix))).$suffix.'@'.$domain;
        }

        return $candidate;
    }

    private function nullableString(mixed $raw, int $max): ?string
    {
        if ($raw === null) {
            return null;
        }

        $s = trim((string) $raw);

        if ($s === '') {
            return null;
        }

        return mb_substr($s, 0, $max);
    }

    private function nullableFloat(mixed $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            return (float) $raw;
        }

        return null;
    }
}
