<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;


class PresetsController extends Controller // I should get rid of this one soon
{

    /**
     * Show the form for creating a new resource.
     *
     * @return array
     */
    public function index()
    {
        // returns array with ikf, ekf, clak
        return config('options.presets'); // Should be present on front end only
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param Request|CategoryRequest $request
     * @return \Illuminate\Database\Eloquent\Model
     */

//    public function store(CategoryRequest $request)
//    {
//        $category = $request->getCategoryByFilters();
//
//        $category->isTeam = $request->isTeam;
//        $category->gender = $request->gender;
//        $category->ageCategory = $request->ageCategory;
//        $category->ageMin = $request->ageMin;
//        $category->ageMax = $request->ageMax;
//        $category->gradeCategory = $request->gradeCategory;
//        $category->gradeMin = $request->gradeMin;
//        $category->gradeMax = $request->gradeMax;
//        $category->name = $category->buildName();
//
//        $newCategoryName = Category::firstOrCreate($category->toArray());
//
//        return $newCategoryName;
//    }
}
