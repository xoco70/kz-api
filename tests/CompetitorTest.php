<?php

use App\Championship;
use App\Competitor;
use App\Tournament;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Concerns\AttachJwtToken;

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
class CompetitorTest extends TestCase
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
    public function it_get_competitors_data()
    {
        $tournament = factory(Tournament::class)->create(['user_id' => $this->root->id]);
        factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]);
        $this->json('GET', '/tournaments/' . $tournament->slug . '/competitors');
        $this->assertHasJson($tournament->competitors->toArray());

    }
    /** @test */
    public function it_add_a_user_to_championship()
    {
        $tournament = factory(Tournament::class)->create(['user_id' => $this->root->id]);
        factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]);

        $existingUser = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_USER')]);

        $newUser = clone $existingUser;
        $newUser->email = "new@email.com";

        $competitors = [$existingUser]; // , $deletedUser
        foreach ($tournament->championships as $championship) {
            $this->addCompetitorsToChampionship($championship, $competitors);
        }
    }


    /** @test */
    public function it_removes_a_competitor_from_tournament()
    {
        // Given
        $tournament = factory(Tournament::class)->create(['user_id' => $this->root->id]);
        $championship = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]); // Single
        $user = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_USER')]);
        $competitor = factory(Competitor::class)->create(['championship_id' => $championship->id, 'user_id' => $user->id]);
        $this->seeInDatabase('competitor', $competitor->toArray());
        $this->call('DELETE', '/competitors/' . $competitor->id);
        $this->notSeeInDatabase('competitor', ['championship_id' => $championship->id, 'user_id' => $user->id]);
    }



    /** @test */
    public function it_add_competitor_and_get_error()
    {
        $tournament = factory(Tournament::class)->create(['user_id' => $this->root->id]);
        $championship = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 2]);
        factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 1]);

        $existingUser = factory(User::class)->create();
        $this->call('POST', '/championships/' . $championship->id . '/competitors/',
            ['competitors' => $existingUser]); // BAD PARAM INPUT
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

    }
//
//    /** @test */
//    public function a_competitor_always_has_the_same_short_id_in_a_tournament()
//    {
//        Artisan::call('db:seed', ['--class' => 'CountriesSeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'TournamentLevelSeeder', '--database' => 'sqlite']);
//        Artisan::call('db:seed', ['--class' => 'CategorySeeder', '--database' => 'sqlite']);
//        $tournament = factory(Tournament::class)->create(['user_id' => $this->root->id]);
//        $championship1 = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 3]); // Single
//        $championship2 = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 4]); // Single
//        $this->addCompetitorToChampionship($championship1, $this->root);
//        $this->addCompetitorToChampionship($championship2, $this->root);
//
//
//        $competitor1 = Competitor::where('championship_id', $championship1->id)
//            ->where('user_id', $this->root->id)->select('short_id')->first();
//
//
//        $competitor2 = Competitor::where('championship_id', $championship2->id)
//            ->where('user_id', $this->root->id)->select('short_id')->first();
//
//
//        $this->assertEquals($competitor1->short_id, $competitor2->short_id);
//
//    }
    public function addCompetitorsToChampionship($championship, $competitors)
    {
        $this->call('POST', '/championships/' . $championship->id . '/competitors/',
            ['competitors' => $competitors]);
        foreach ($competitors as $competitor) {
            $myUser = User::where('email', $competitor->email)
                ->where('firstname', $competitor->firstname)
                ->where('lastname', $competitor->lastname)
                ->firstOrFail();

            $this->seeInDatabase('competitor',
                ['championship_id' => $championship->id,
                    'user_id' => $myUser->id,
                ]);
        }
    }
}
