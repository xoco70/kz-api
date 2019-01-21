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
class CompetitorTeamTest extends TestCase
{
    use DatabaseTransactions, AttachJwtToken;

    protected $user, $users, $addUser, $editUser, $root, $simpleUser;


    public function setUp()
    {
        parent::setUp();
        $this->root = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_SUPERADMIN')]);
        Auth::login($this->root);
    }

    /** @test */
    public function it_add_competitor_to_team()
    {
        // Create a championship that goes with teams
        $tournament = Tournament::first();
        $championship = $tournament->championships->get(0);
        $setting = new ChampionshipSettings(['isTeam' => 1]);
        $championship->settings = $setting;
        $competitor = factory(Competitor::class)->create(['championship_id' => $championship->id]);
        $team = new Team;
        $team->name = "myTeam";
        $team->championship_id = $championship;
        $team->save();
        // Assign competitor to team
//        $teamId, $competitorId

        $this->json('POST', '/teams/' . $team->id . '/competitors/' . $competitor->id . '/add')
            ->assertResponseOk();
        $this->seeInDatabase('competitor_team', ['team_id' => $team->id, 'competitor_id' => $competitor->id]);
    }

    /** @test */
    public function it_removes_competitor_from_team()
    {

    }

    /** @test */
    public function it_moves_competitor_from_team1_to_team2()
    {

    }

//    /** @test */
//    public function it_reorder_competitor_inside_same_team()
//    {
//
//    }
}
