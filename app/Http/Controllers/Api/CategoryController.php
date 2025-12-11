<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        //
    }
    public function show($id)
    {
        //
    }
    public function store(Request $request)
    {
        $atts=$request->validate([
            'name'=>'required|string|max:255',
            's_name'=>'required|string|max:50',
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
}
