<?php

namespace App\Livewire;

use Livewire\Mechanisms\HandleRequests\HandleRequests;

/**
 * Livewire's default update URI is generated with toRoute(..., $absolute = false), which strips
 * the application's subdirectory from the path. Browsers then POST to /livewire/update on the
 * host root instead of under e.g. /sales/public. Using an absolute URL fixes subdirectory installs.
 */
class AbsoluteUriHandleRequests extends HandleRequests
{
    public function getUpdateUri(): string
    {
        $route = $this->updateRoute ?? $this->findUpdateRoute();

        return (string) app('url')->toRoute($route, [], true);
    }
}
