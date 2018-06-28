<?php

use App\Tournament;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response as HttpResponse;
use Tests\Concerns\AttachJwtToken;

class TournamentsTest extends TestCase
{

    use DatabaseTransactions, AttachJwtToken;
    protected $initialTournamentNum = 6;
    protected $defaultPagintation = 25;
    protected $user;


    /** @test */
    public function user_can_see_tournament_list()
    {
        $response = $this
            ->call('GET', '/tournaments');
        $this->assertEquals(HttpResponse::HTTP_OK, $response->status());
    }

    /** @test */
    public function tournament_index_pagination_metadata()
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

    /** @test */
    public function it_create_tournament_manually()
    {
        $faker = Faker\Factory::create();
        $dateIni['year'] = $faker->year;
        $dateIni['month'] = $faker->month;
        $dateIni['day'] = $faker->dayOfMonth;
        $payload = [
            'name' => $faker->word,
            'rule_id' => 0,
            'dateIni' => $dateIni,
            'dateFin' => $dateIni,
            'categoriesSelected' => [2, 3, 4],
        ];
        $this->call('POST', '/tournaments', $payload);
        $this->assertResponseOk();
        $this->seeInDatabase('tournament', ['name' => $payload['name']]);
        $tournament = Tournament::where('name', $payload['name'])->first();
        $this->seeInDatabase('championship', ['tournament_id' => $tournament->id, 'category_id' => 2]);
        $this->seeInDatabase('championship', ['tournament_id' => $tournament->id, 'category_id' => 3]);
        $this->seeInDatabase('championship', ['tournament_id' => $tournament->id, 'category_id' => 4]);

    }

    /** @test */
    public function it_denies_creating_an_empty_tournament()
    {
        $payload = [
            'name' => '',
            'rule_id' => 0,
            'categoriesSelected' => [2, 3, 4],
        ];
        $response = $this->call('POST', '/tournaments', $payload);
        $this->assertEquals(HttpResponse::HTTP_UNPROCESSABLE_ENTITY, $response->status());
    }


    /** @test */
    public function it_create_ikf_tournament()
    {
        $faker = Faker\Factory::create();
        $dateIni['year'] = $faker->year;
        $dateIni['month'] = $faker->month;
        $dateIni['day'] = $faker->dayOfMonth;
        $payload = [
            'name' => $faker->word,
            'rule_id' => 1,
            'dateIni' => $dateIni,
            'dateFin' => $dateIni,
            'categoriesSelected' => [''],

        ];
        $this->call('POST', '/tournaments', $payload);
        $this->assertResponseOk();
        $this->seeInDatabase('tournament', ['name' => $payload['name']]);
        $tournament = Tournament::where('name', $payload['name'])->first();
        $this->assertEquals(4,$tournament->championships->count());
        $this->assertEquals(4,$tournament->championshipSettings->count());
    }
//
//    /** @test
//     * @param null $tournament
//     */
//    public function it_edit_tournament($tournament = null)
//    {
//
//        Artisan::call('db:seed', ['--class' => 'CategorySeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'CountriesSeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//
//        $tournament = $tournament ?? factory(Tournament::class)->create(['name' => 'MyTournament']);
//
//        $this->visit('/tournaments/' . $tournament->slug . '/edit')
//            ->type('MyTournamentXXX', 'name')
//            ->type('2015-12-15', 'dateIni')
//            ->type('2015-12-15', 'dateFin')
//            ->type('2015-12-16', 'registerDateLimit')
//            ->type('1', 'type')
//            ->type('2', 'level_id')
//            ->press('saveTournament')
//            ->seeInDatabase('tournament',
//                ['name' => 'MyTournamentXXX',
//                    'dateIni' => '2015-12-15 00:00:00',
//                    'dateFin' => '2015-12-15 00:00:00',
//                    'registerDateLimit' => '2015-12-16 00:00:00',
//                    'type' => '1',
//                    'level_id' => '2',
//                ]);
//    }
//
//
//    /** @test */
//    public function you_must_own_tournament_or_be_superuser_to_edit_it()
//    {
//        Artisan::call('db:seed', ['--class' => 'CategorySeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//        $root = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_SUPERADMIN')]);
//        $user = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_USER')]);
//        $otherUser = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_USER')]);
//        $this->logWithUser($root);
//
//        $myTournament = factory(Tournament::class)->create(['user_id' => $root->id]);
//
//        //add categories
//
//        factory(Championship::class)->create(['tournament_id' => $myTournament->id]);
//
//        $this->it_edit_tournament($myTournament); // it must be OK because tournament is mine
//        $hisTournament = factory(Tournament::class)->create(['user_id' => $user->id]);
//        // 1 is SuperUser so it should be OK
//        $this->visit('/tournaments/' . $hisTournament->slug . '/edit')
//            ->see(trans_choice('core.tournament', 2));
//        $this->logWithUser($otherUser);
//        $this->visit('/tournaments/' . $hisTournament->slug . '/edit')
//            ->see("403");
//    }
//
//    /** @test */
//    public function it_delete_tournament()
//    {
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//
//        $tournament = factory(Tournament::class)->create(['user_id' => $request->auth->id]);
//        $championship1 = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]);
//        $championship2 = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 2]);
//        factory(ChampionshipSettings::class)->create(['championship_id' => $championship1->id]);
//        factory(Competitor::class)->create(['championship_id' => $championship1->id]);
//
//        // Check that tournament is gone
//        $this->visit("/tournaments")
//            ->see(trans_choice('core.tournament', 2))
//            ->press("delete_" . $tournament->slug)
//            ->seeIsSoftDeletedInDatabase('tournament', ['id' => $tournament->id])
//            ->seeIsSoftDeletedInDatabase('championship', ['id' => $championship1->id])
//            ->seeIsSoftDeletedInDatabase('championship', ['id' => $championship2->id]);
////            ->seeIsSoftDeletedInDatabase('category_settings', ['championship_id' => $championship1->id])
////            ->seeIsSoftDeletedInDatabase('competitor', ['championship_id' => $championship1->id]);
//
//    }
//
//    /** @test */
//    public function it_restore_tournament()
//    {
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//        $tournament = factory(Tournament::class)->create([
//            'user_id' => $request->auth->id,
//            'deleted_at' => '2015-12-12']);
//
//        $this->actingAs($this->root, 'api')
//            ->json('POST', '/api/v1/tournaments/' . $tournament->slug . '/restore')
//            ->seeInDatabase('tournament', [
//                'id' => $tournament->id,
//                'deleted_at' => null
//            ]);
//    }
//
//
//    /** @test */
//    public function update_general_info_in_tournament()
//    {
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//
//        $tournament = factory(Tournament::class)->create();
//        factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]);
//        $newTournament = factory(Tournament::class)->make(['user_id' => $tournament->user_id]);
//        $arrNewTournament = json_decode(json_encode($newTournament), true);
//
//        $response = $this->json('PUT', '/tournaments/' . $tournament->slug, $arrNewTournament)
//            ->seeInDatabase('tournament', $arrNewTournament);
//
//    }
//
//    /** @test */
//    public function update_venue_info_in_tournament()
//    {
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'CountriesSeeder', '--database' => 'sqlite']);
//        $tournament = factory(Tournament::class)->create();
//        $venue = factory(Venue::class)->make();
//        $arrVenue = json_decode(json_encode($venue), true);
//        $this->json('PUT', '/tournaments/' . $tournament->slug, $arrVenue)
//            ->seeInDatabase('venue', $arrVenue);
//    }
//
//
//    /** @test
//     */
//    public function user_can_see_tournament_info_but_cannot_edit_it()
//    {
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//        $owner = factory(User::class)->create(['name' => 'AnotherUser']);
//        $simpleUser = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_USER')]);
//
//        $tournament = factory(Tournament::class)->create(['user_id' => $owner->id]);
//
//        $this->logWithUser($simpleUser);
//
//        $this->visit('/tournaments/' . $tournament->slug)
//            ->dontSee("403.png")
//            ->visit('/tournaments/' . $tournament->slug . '/edit')
//            ->see("403.png");
//    }
}
