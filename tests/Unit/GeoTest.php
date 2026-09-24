<?php

namespace Tests\Unit;

use App\Support\Geo;
use PHPUnit\Framework\TestCase;

class GeoTest extends TestCase
{
    public function test_it_calculates_a_short_lagos_distance(): void
    {
        $km = Geo::distanceKm(6.5244, 3.3792, 6.5350, 3.3450);

        $this->assertGreaterThan(1, $km);
        $this->assertLessThan(10, $km);
    }
}
