<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Support\Modules;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Modules::catalogue() as $key => $meta) {
            Module::firstOrCreate(
                ['key' => $key],
                ['name' => $meta['label'], 'enabled' => true]
            );
        }

        Modules::forget();
    }
}
