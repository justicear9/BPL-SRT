<?php

namespace Tests\Feature;

use Tests\TestCase;

class FilamentFontAssetsTest extends TestCase
{
    public function test_filament_inter_latin_font_exists_in_public(): void
    {
        $path = public_path('fonts/filament/filament/inter/inter-latin-wght-normal-NRMW37G5.woff2');

        $this->assertFileExists(
            $path,
            'Filament font files are missing from public/. Run `php artisan filament:assets` (also runs after `composer install` via post-autoload-dump).',
        );
    }
}
