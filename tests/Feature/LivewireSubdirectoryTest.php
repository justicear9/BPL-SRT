<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class LivewireSubdirectoryTest extends TestCase
{
    public function test_livewire_update_uri_is_absolute_and_preserves_app_subdirectory(): void
    {
        config(['app.url' => 'http://localhost/sales/public']);
        URL::forceRootUrl('http://localhost/sales/public');

        $uri = app('livewire')->getUpdateUri();

        $this->assertStringStartsWith('http://', $uri);
        $this->assertStringContainsString('/sales/public/livewire/update', $uri);
    }
}
