<?php

namespace App\Http\Controllers;

use App\FightersGroup;
use Illuminate\Http\Request;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;
use Illuminate\Http\Response as HttpResponse;


class ChampionshipSettingsController extends Controller
{

    protected $defaultSettings;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $championshipId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $championshipId)
    {
        try {
            $request->request->add(['championship_id' => $championshipId]);
            $settings = ChampionshipSettings::create($request->all());
            return response()->json($settings, HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param $championshipId
     * @param $championshipSettingsId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $championshipId, $championshipSettingsId)
    {
        try {
            $setting = ChampionshipSettings::findOrFail($championshipSettingsId)->fill($request->all());

            // If we changed one of those data, remove tree
            if ($setting->isDirty('hasPreliminary') || $setting->isDirty('hasPreliminary') || $setting->isDirty('treeType')) {
                FightersGroup::where('championship_id', $championshipId)->delete();
            }
            $setting->save();
            return response()->json($setting, HttpResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
