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
use Illuminate\Http\Response;

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
    protected $tournament, $championship, $team1, $team2, $competitor;


    public function setUp()
    {
        parent::setUp();
        $this->root = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_SUPERADMIN')]);
        Auth::login($this->root);
        // Create a championship that goes with teams
        $this->tournament = Tournament::first();
        $this->championship = $this->tournament->championships->get(0);
        $setting = new ChampionshipSettings(['isTeam' => 1]);
        $this->championship->settings = $setting;
        $this->competitor = factory(Competitor::class)->create(['championship_id' => $this->championship->id]);
        $this->team1 = factory(Team::class)->make(['championship_id' => $this->championship]);
        $this->team1->championship_id = $this->championship;
        $this->team1->save();
    }

    /** @test */
    public function it_add_competitor_to_team()
    {

        // Assign competitor to team

        $this->json('POST', '/teams/' . $this->team1->id . '/competitors/' . $this->competitor->id . '/add')
            ->assertResponseOk();
        $this->seeInDatabase('competitor_team', ['team_id' => $this->team1->id, 'competitor_id' => $this->competitor->id]);
    }

    /** @test */
    public function it_removes_competitor_from_team()
    {
        $this->it_add_competitor_to_team();
        $this->json('POST', '/teams/' . $this->team1->id . '/competitors/' . $this->competitor->id . '/remove');
        $this->assertResponseOk();
        $this->assertEquals($this->response->content(), "{}");
        $this->missingFromDatabase('competitor_team', ['team_id' => $this->team1->id, 'competitor_id' => $this->competitor->id]);
    }

    /** @test */
    public function it_moves_competitor_from_team1_to_team2()
    {
        $this->team2 = new Team;
        $this->team2->name = "myOtherTeam";
        $this->team2->championship_id = $this->championship;
        $this->team2->save();

        $this->it_add_competitor_to_team();
        // move
        $this->json('POST', '/teams/' . $this->team1->id . '/' . $this->team2->id . '/competitors/' . $this->competitor->id . '/move')
            ->assertResponseOk();
        $this->seeInDatabase('competitor_team', ['team_id' => $this->team2->id, 'competitor_id' => $this->competitor->id]);
        $this->missingFromDatabase('competitor_team', ['team_id' => $this->team1->id, 'competitor_id' => $this->competitor->id]);

    }

//    /** @test */
//    public function it_reorder_competitor_inside_same_team()
//    {
//
//    }
}
