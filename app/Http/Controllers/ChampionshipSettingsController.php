<?php

namespace App\Http\Controllers;

use App\FightersGroup;
use DaveJamesMiller\Breadcrumbs\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Xoco70\LaravelTournaments\Models\ChampionshipSettings;

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
            return response()->json(['settings' => $settings, 'msg' => trans('msg.category_create_successful'), 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'status' => 'error']);
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
            return response()->json(['setting' => $setting, 'msg' => trans('msg.category_update_successful'), 'status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage(), 'status' => 'error']);
        }
    }

}
