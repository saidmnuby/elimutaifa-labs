<?php

namespace Database\Seeders;

use App\Models\ServiceModule;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        ServiceModule::upsert([
            [
                'key' => 'github-deploy',
                'name' => 'GitHub deployment',
                'version' => '1.0.0',
                'description' => 'Deploy approved repository changes to the assigned hosting folder.',
                'is_enabled' => true,
            ],
            [
                'key' => 'database',
                'name' => 'Project database',
                'version' => '1.0.0',
                'description' => 'A dedicated database and database user assigned to one project.',
                'is_enabled' => true,
            ],
        ], ['key'], ['name', 'version', 'description', 'is_enabled', 'updated_at']);
    }
}
