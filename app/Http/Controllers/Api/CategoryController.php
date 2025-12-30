<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        return response()->json(['data'=>Category::where('enabled',true)->get()],200);
    }
    public function show($id)
    {
        $category=Category::findOrFail($id);
        return response()->json(['data'=>$category,'related_products_number'=>Product::where('category_id',$category->id)->count()],200);
    }
    public function store(Request $request)
    {
        $atts=$request->validate([
            'name'=>'required_without:s_name|string|max:255',
            's_name'=>'required_without:name|string|max:50',
            'enabled'=> 'nullable|boolean',
        ]);
        $this->authorize('createCategory',User::class);
        $category=Category::create([
            'name'=>$atts['name'],
            's_name'=>$atts['s_name'],
            'enabled'=>$atts['enabled'] ?? true,
        ]);
        return response()->json([
            'message'=>'Category created successfully',
            'category'=>$category,
        ],201);
    }
    public function updateState(Request $request, $id)
    {
        $this->authorize('updateCategory',User::class);
        $category=Category::findOrFail($id);
        $atts=$request->validate([
            'enabled'=>'required|boolean',
        ]);
        $category->enabled=$atts['enabled'];
        $category->save();
        return response()->json([
            'message'=>'Category state updated successfully',
            'category'=>$category,
        ],200);
    }
    public function destroy($id)
    {
        $this->authorize('updateCategory',User::class);
        $category=Category::findOrFail($id);
        $category->delete();
        return response()->json([
            'message'=>'Category deleted successfully',
        ],200);
    }
}
