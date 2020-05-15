<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Product;
use App\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\Product as ProductCol;
use App\Http\Resources\ProductResources;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->per_page;
        $sortBy = $request->sort_by;
        $orderBy = $request->order_by;
        return response()->json([
            'products' => new ProductResources(Product::with('categories')->orderBy($sortBy, $orderBy)->paginate($per_page)),
            'categories' => Category::pluck('title')->all()
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function idformater($i, $c)
    {
        return $i;
    }
    public function store(Request $request)
    {
        $category = Category::where('title', $request->category)->first();
        $ext = $request->photo->extension();
        $image = Str::random(20) . '.' . $ext;
        $product = new Product([
            'name' => $request->name,
            'thumbnail' => 'images/' . $image,
            'price' => $request->price,
            'discount' => $request->discount,
            'description' => $request->description,
        ]);
        $product->categories()->associate($category);
        $product->save();
        $request->photo->move(public_path('/images'), $image);
        return response()->json(['product' => new ProductCol($product)], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $products = Product::where('name', 'LIKE', '%' . $id . '%')->paginate();
        return response()->json(['products' => new ProductResources($products)], 200);
    }
    public function update(Request $request, $id)
    {
        $new_cat = Category::where('title', $request->categories)->first();
        $product = Product::find($id);
        $product->name = $request->name;
        $product->price = $request->price;
        $product->discount = $request->discount;
        $product->description = $request->description;
        $product->categories()->dissociate($product->categories);
        $product->categories()->associate($new_cat);
        $product->save();
        return response()->json(['product' => new ProductCol($product)], 200);
    }
    public function changePhoto(Request $request)
    {
        $product = Product::where('id', $request->product)->first();
        $ext = $request->photo->extension();
        $image = Str::random(20) . '.' . $ext;
        $photo = $request->photo->move(public_path('/images'), $image);
        $product->thumbnail = 'images/' . $image;
        $product->save();
        return response()->json(['product' => new ProductCol($product)], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['product'=>new ProductCol($product)], 200);
    }
}
