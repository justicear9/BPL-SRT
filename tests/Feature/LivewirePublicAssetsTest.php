<?php

namespace Tests\Feature;

use Tests\TestCase;

class LivewirePublicAssetsTest extends TestCase
{
    public function test_livewire_frontend_is_published_for_subdirectory_urls(): void
    {
        $manifest = public_path('vendor/livewire/manifest.json');

        $this->assertFileExists(
            $manifest,
            'Livewire JS is missing from public/. Run `php artisan vendor:publish --tag=livewire:assets --force` (also runs after `composer install`).',
        );
    }
}
