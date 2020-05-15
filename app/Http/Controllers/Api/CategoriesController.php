<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Category;
use App\Http\Resources\Category as CategoryCol;
use App\Http\Resources\CategoryResources;
use Illuminate\Http\Request;
class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page=$request->per_page;
        $sortBy=$request->sort_by;
        $orderBy=$request->order_by;
        return response()->json(['categories'=>new CategoryResources(Category::orderBy($sortBy,$orderBy)->paginate($per_page))], 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Category=Category::create([
            'title'=>$request->title,
            'description'=>$request->description
        ]);
        return response()->json(['category'=>new CategoryCol($Category)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Categorys=Category::where('title','LIKE','%'.$id.'%')->paginate();
        return response()->json(['categories'=>new CategoryResources($Categorys)], 200);
    }
    public function verifyTitle(Request $request)
    {
        if($request->id){
            $request->validate([
                'title' => 'required|unique:categories,title,'.$request->id,
            ]);
        }else{
            $request->validate([
                'title' => 'required|unique:categories',
            ]);
        }
        
        return response()->json(['message'=>'Title available'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $Category=Category::find($id);
        $Category->title=$request->title;
        $Category->description=$request->description;
        $Category->save();
        return response()->json(['category'=>new CategoryCol($Category)], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $Category=Category::find($id);
        $Category->delete();
        return response()->json(['category'=>new CategoryCol($Category)], 200);
    }
}
