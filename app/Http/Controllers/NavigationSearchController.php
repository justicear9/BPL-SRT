<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Services\SalesSidebarMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $menu = app(SalesSidebarMenu::class)->forUser($user);

        $navigation = [];

        foreach ($menu as $row) {
            if (isset($row['submenu']) && is_array($row['submenu'])) {
                $section = (string) ($row['name'] ?? 'Menu');
                $items = [];
                foreach ($row['submenu'] as $sub) {
                    $items[] = [
                        'name' => (string) ($sub['name'] ?? ''),
                        'icon' => 'tabler-corner-down-right',
                        'url' => $this->normalizeUrlPath((string) ($sub['url'] ?? '')),
                    ];
                }
                if ($items !== []) {
                    $navigation[$section] = $items;
                }
            } else {
                $item = [
                    'name' => (string) ($row['name'] ?? ''),
                    'icon' => $this->parseTablerIcon((string) ($row['icon'] ?? '')),
                    'url' => $this->normalizeUrlPath((string) ($row['url'] ?? '')),
                ];
                if (! isset($navigation['Menu'])) {
                    $navigation['Menu'] = [];
                }
                $navigation['Menu'][] = $item;
            }
        }

        $suggestions = [
            'Shortcuts' => [
                [
                    'name' => __('Visits'),
                    'icon' => 'tabler-map-pin',
                    'url' => 'workspace/visits',
                ],
                [
                    'name' => __('Customers'),
                    'icon' => 'tabler-building-store',
                    'url' => 'workspace/customers',
                ],
            ],
        ];

        return response()->json([
            'navigation' => $navigation,
            'suggestions' => $suggestions,
            'customers' => $this->customersForSearch($user),
        ]);
    }

    /**
     * @return list<array{name: string, subtitle: string, icon: string, url: string}>
     */
    protected function customersForSearch(User $user): array
    {
        $query = Customer::query()->orderBy('name')->limit(400);

        if (! $user->canManageAllVisits()) {
            $query->where('assigned_user_id', $user->id);
        }

        return $query->get()->map(fn (Customer $c): array => [
            'name' => $c->name,
            'subtitle' => $c->type?->label() ?? '',
            'icon' => 'tabler-building-store',
            'url' => 'workspace/customers/'.$c->id.'/edit',
        ])->all();
    }

    protected function normalizeUrlPath(string $url): string
    {
        $url = trim($url, '/');

        return $url === '' ? '' : $url;
    }

    protected function parseTablerIcon(string $menuIconClasses): string
    {
        if (preg_match('/\b(tabler-[\w-]+)\b/', $menuIconClasses, $m)) {
            return $m[1];
        }

        return 'tabler-circle';
    }
}
