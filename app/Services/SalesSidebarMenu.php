<?php

namespace App\Services;

use App\Models\User;
use stdClass;

/**
 * Role-based Vuexy vertical / horizontal menu (see MenuServiceProvider view composer).
 */
class SalesSidebarMenu
{
    public function forUser(?User $user): array
    {
        if (! $user instanceof User) {
            return $this->guestMenu();
        }

        if ($user->isSalesRep()) {
            return $this->salesRepMenu();
        }

        return $this->staffMenu();
    }

    /**
     * @return array{0: stdClass, 1: stdClass}
     */
    public function menuDataPair(?User $user): array
    {
        $menu = $this->forUser($user);
        $wrapped = $this->wrap($menu);

        return [$wrapped, $wrapped];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function wrap(array $items): stdClass
    {
        $obj = new stdClass;
        $obj->menu = array_map(fn (array $row): stdClass => $this->menuRowToObject($row), $items);

        return $obj;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function menuRowToObject(array $row): stdClass
    {
        $submenu = $row['submenu'] ?? null;
        unset($row['submenu']);

        $out = (object) $row;

        if (is_array($submenu)) {
            $out->submenu = array_map(
                fn (array $sub): stdClass => (object) $sub,
                $submenu,
            );
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function guestMenu(): array
    {
        return [
            $this->item('Dashboard', 'tabler-smart-home', 'dashboard-sales', '/'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function salesRepMenu(): array
    {
        return [
            $this->item('Dashboard', 'tabler-layout-dashboard', 'dashboard-sales', '/'),
            $this->item('Customers', 'tabler-building-store', 'workspace.customers', 'workspace/customers'),
            $this->item('Visits', 'tabler-map-pin', 'workspace.visits', 'workspace/visits'),
            $this->salesRepsGroupForRep(),
            $this->reportsGroup(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function staffMenu(): array
    {
        $user = auth()->user();

        $items = [
            $this->item('Dashboard', 'tabler-layout-dashboard', 'dashboard-sales', '/'),
            $this->item('Customers', 'tabler-building-store', 'workspace.customers', 'workspace/customers'),
        ];

        if ($user instanceof User && ($user->role?->canManageCatalog() ?? false)) {
            $items[] = $this->item('Products', 'tabler-package', 'workspace.products', 'workspace/products');
        }

        $items[] = $this->item('Orders', 'tabler-receipt', 'workspace.orders', 'workspace/orders');
        $items[] = $this->item('Visits', 'tabler-map-pin', 'workspace.visits', 'workspace/visits');
        $items[] = $this->salesRepsGroupForStaff();
        $items[] = $this->reportsGroup();

        if ($user instanceof User && $user->isAdmin()) {
            $items[] = $this->item('Settings', 'tabler-settings', 'workspace.settings', 'workspace/settings');
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesRepsGroupForStaff(): array
    {
        return [
            'name' => 'Sales reps',
            'icon' => 'menu-icon icon-base ti tabler-users',
            'slug' => 'workspace.users',
            'submenu' => [
                $this->subItem('List', 'workspace.users.index', 'workspace/users'),
                $this->subItem('Add', 'workspace.users.create', 'workspace/users/create'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function salesRepsGroupForRep(): array
    {
        return [
            'name' => 'Sales reps',
            'icon' => 'menu-icon icon-base ti tabler-users',
            'slug' => 'workspace.profile',
            'submenu' => [
                $this->subItem('My profile', 'workspace.profile.edit', 'workspace/profile'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reportsGroup(): array
    {
        return [
            'name' => 'Reports',
            'icon' => 'menu-icon icon-base ti tabler-report-analytics',
            'slug' => 'reports',
            'submenu' => [
                $this->subItem('Visit Report', 'reports.visits', 'reports/visits'),
                $this->subItem('Order Report', 'reports.orders', 'reports/orders'),
                $this->subItem('Sample Report', 'reports.samples', 'reports/samples'),
                $this->subItem('Collections Report', 'reports.collections', 'reports/collections'),
                $this->subItem('Alpha Report', 'reports.alpha', 'reports/alpha'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function item(string $name, string $tablerIcon, string $slug, string $urlPath): array
    {
        return [
            'name' => $name,
            'icon' => 'menu-icon icon-base ti '.$tablerIcon,
            'slug' => $slug,
            'url' => $urlPath,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function subItem(string $name, string $slug, string $urlPath): array
    {
        return [
            'name' => $name,
            'slug' => $slug,
            'url' => $urlPath,
        ];
    }
}
