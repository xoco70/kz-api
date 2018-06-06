<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class TournamentsTest extends TestCase
{
    use DatabaseTransactions;
    protected $initialTournamentNum = 6;
    protected $defaultPagintation = 25;

    public function testTournamentIndex_pagination_metadata()
    {
        $numTournaments = 25;
        factory('App\Tournament', $numTournaments)->create();
        $total = $numTournaments + $this->initialTournamentNum;
        $response = $this->call('GET', '/tournaments');
        $this->assertResponseOk();
        $json = json_decode($response->getContent());
        $this->assertEquals($json->meta->last_page, ceil($total / $this->defaultPagintation));
        $this->assertEquals($json->meta->total, $total);
        $this->assertEquals($json->links->first, $this->baseUrl . "/tournaments?page=1");
        $this->assertEquals($json->links->last, $this->baseUrl . "/tournaments?page=2");

    }
}
