<?php

use App\Championship;
use App\Tournament;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Concerns\AttachJwtToken;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;

class ChampionshipSettingsTest extends TestCase
{

    use DatabaseTransactions, AttachJwtToken;
    protected $user;

    /** @test */
    public function it_create_setting_for_championship()
    {
        $tournament = factory(Tournament::class)->create();
        $championship = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 2]);
        $setting = factory(ChampionshipSettings::class)->make(['championship_id' => $championship->id]);
        $this->call('POST', '/championships/' . $championship->id . '/settings', $setting->toArray());
        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->seeInDatabase('championship_settings', $setting->toArray());
        $this->assertHasJson($setting->toArray());
//        return response()->json($settings, HttpResponse::HTTP_CREATED);
    }

    /** @test */
    public function it_update_setting_for_championship()
    {
        $tournament = factory(Tournament::class)->create();
        $championship = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 2]);
        $setting = factory(ChampionshipSettings::class)->create(['championship_id' => $championship->id]);
        $this->call('PUT', '/championships/' . $championship->id . '/settings/' . $setting->id, $setting->toArray());
        $this->assertResponseOk();
        unset($setting->updated_at);
        $this->seeInDatabase('championship_settings', $setting->toArray());
    }

    /** @test */
    public function it_create_setting_for_championship_and_fails_with_500()
    {
        $tournament = factory(Tournament::class)->create();
        $championship = factory(Championship::class)->create(['tournament_id' => $tournament->id, 'category_id' => 2]);
        $setting = factory(ChampionshipSettings::class)->make(['another_field' => 0]);
        $this->call('POST', '/championships/' . $championship->id . '/settings', $setting->toArray());
        $this->assertResponseStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
