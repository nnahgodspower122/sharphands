<?php

namespace App\Console\Commands;

use Database\Seeders\NearbyWorkersSeeder;
use Illuminate\Console\Command;

class SeedNearbyWorkersCommand extends Command
{
    protected $signature = 'sharphand:seed-nearby
                            {--lat=4.8156 : Map center latitude}
                            {--lng=7.0498 : Map center longitude}';

    protected $description = 'Seed verified available workers close to a map location';

    public function handle(NearbyWorkersSeeder $seeder): int
    {
        $lat = (float) $this->option('lat');
        $lng = (float) $this->option('lng');

        $seeder->setCommand($this);
        $seeder->run($lat, $lng);

        $this->info("Seeded nearby workers around {$lat}, {$lng}.");

        return self::SUCCESS;
    }
}
