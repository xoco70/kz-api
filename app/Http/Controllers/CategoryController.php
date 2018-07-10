<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class CategoryController extends Controller
{

//    /**
//     * Show the form for creating a new resource.
//     *
//     * @return array
//     */
    public function index()
    {
        // returns array with ikf, ekf, clak
        return response()->json(
            Category::take(10)->orderBy('id', 'asc')->select('id', 'name')->get(), HttpResponse::HTTP_OK
        );
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Model
     */

    public function store(Request $request)
    {
        $category = new Category;
        $category->name = $request->name;
        $category->isTeam = $request->isTeam;
        $category->gender = $request->gender;
        $category->ageCategory = $request->ageCategory;
        $category->ageMin = $request->ageMin;
        $category->ageMax = $request->ageMax;
        $category->gradeCategory = $request->gradeCategory;
        $category->gradeMin = $request->gradeMin;
        $category->gradeMax = $request->gradeMax;

        $newCategoryName = Category::firstOrCreate($category->toArray());

        return response()->json($newCategoryName, HttpResponse::HTTP_CREATED);
    }
}
