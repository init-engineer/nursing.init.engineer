<?php

namespace Database\Seeders\Social;

use App\Domains\Social\Models\Platform;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

/**
 * Class SocialPlatformTableSeeder.
 *
 * @extends Seeder
 */
class SocialPlatformTableSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();

        Platform::create([
            'name' => '本地平台',
            'action' => Platform::ACTION_INACTION,
            'type' => Platform::TYPE_LOCAL,
            'active' => true,
        ]);

        $this->enableForeignKeys();
    }
}
