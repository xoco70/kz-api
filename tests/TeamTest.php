<?php

use App\Competitor;
use App\Team;
use App\Tournament;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Concerns\AttachJwtToken;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Illuminate\Http\Response as HttpResponse;

/**
 * List of User Test
 *
 * it_add_a_user_to_tournament_category()
 * it_removes_a_user_from_tournament_category()
 * you_must_own_tournament_or_be_superuser_to_add_or_remove_user_from_tournament
 * you_can_confirm_a_user
 *
 * User: juliatzin
 * Date: 10/11/2015
 * Time: 23:14
 */
class TeamTest extends TestCase
{
    use DatabaseTransactions, AttachJwtToken;

    protected $user, $users, $addUser, $editUser, $root, $simpleUser;
    protected $tournament, $championship, $team1, $team2, $competitor;


    public function setUp()
    {
        parent::setUp();
        $this->root = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_SUPERADMIN')]);
        Auth::login($this->root);
        // Create a championship that goes with teams
        $this->tournament = Tournament::first();
//        dd(Tournament::all()->toArray(), $this->tournament->championships->toArray());
        $this->championship = $this->tournament->championships->get(2); // Championship that has Teams ( id = 7 )
    }

    /** @test */
    public function it_retrieve_all_necessary_data()
    {
        // Create a random number of competitors between 5 and 10
        $teams = factory(Team::class, rand(1, 3))->create([
            'championship_id' => $this->championship
        ]);
        // Assign 2 competitor to first team
        $this->json('POST', '/teams/' . $teams->get(0)->id . '/competitors/' . $this->championship->competitors->get(0)->id . '/add');
        $this->json('POST', '/teams/' . $teams->get(0)->id . '/competitors/' . $this->championship->competitors->get(1)->id . '/add');

        // get the data to build team screen
        $this->json('GET', '/tournaments/' . $this->tournament->slug . '/teams');
        $result = json_decode($this->response->content(), true);
        $tournament = $result['tournament'];
        $championships = $result['championships'];
        $this->assertEquals(count($championships[0]['assignedCompetitors']), 2);
        $this->assertEquals(count($championships[0]['freeCompetitors']), count($this->championship->competitors) - 2);
        $this->assertEquals(count($championships[0]['teams']), count($this->championship->teams));

        $this->assertEquals(count($tournament['championships']), 1);
        $this->assertEquals(count($tournament['championships'][0]['competitors']), count($this->championship->competitors));
        $this->assertEquals(count($tournament['championships'][0]['teams']), count($this->championship->teams));
        $this->assertEquals($tournament['championships'][0]['category']['isTeam'], 1);

    }
}
