<?php

use App\Championship;
use App\Competitor;
use App\Tournament;
use App\User;
use App\Venue;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response as HttpResponse;
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
}
