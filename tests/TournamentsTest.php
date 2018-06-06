<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class TournamentsTest extends TestCase
{
    use DatabaseMigrations;

    public function testTournamentIndex_200()
    {
        $response = $this->call('GET', '/tournaments');

        $this->assertEquals(200, $response->status());
    }
}
