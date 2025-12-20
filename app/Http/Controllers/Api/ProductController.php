<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $request->validate([
            'lan' => 'string|in:en,sw'
        ]);
        return response()->json(['data' => ProductResource::collection(Product::all())], 200);
    }
    public function store(Request $request)
    {
        $this->authorize('createProduct', User::class);
        $atts = $request->validate([
            'name' => 'required_without:s_name|string|max:255',
            's_name' => 'required_without:name|string|max:255',
            'description' => 'nullable|string',
            's_description' => 'nullable|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'stock_quantity' => 'nullable|integer',
            'delivery_option' => 'nullable|string',
            'price_after_discount' => 'nullable|numeric',
        ]);
        try {
            DB::transaction(function () use ($atts, &$product) {
                $category_id = Category::where('name', $atts['category'])->orWhere('s_name', $atts['category'])->first()->id;
                if (!$category_id) {
                    $category_id = Category::create([
                        'name' => $atts['category'],
                        's_name' => $atts['category'],
                        'enabled' => true,
                    ])->id;
                }
                $product = Product::create([
                    'name' => $atts['name'] ?? $atts['s_name'],
                    's_name' => $atts['s_name'] ?? $atts['name'],
                    'description' => $atts['description'] ?? null,
                    's_description' => $atts['s_description'] ?? null,
                    'price' => $atts['price'],
                    'category_id' => $category_id,
                    'stock_quantity' => $atts['stock_quantity'] ?? 0,
                    'delivery_option' => $atts['delivery_option'] ?? null,
                    'price_after_discount' => $atts['price_after_discount'] ?? null,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product,
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $this->authorize('createProduct', User::class);
        $product = Product::findOrFail($id);
        $atts = $request->validate([
            'name' => 'nullable|string|max:255',
            's_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            's_description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category' => 'nullable|string',
            'stock_quantity' => 'nullable|integer',
            'delivery_option' => 'nullable|string',
            'price_after_discount' => 'nullable|numeric',
        ]);
        try {
            DB::transaction(function () use ($atts, &$product) {
                if (isset($atts['category'])) {
                    $category_id = Category::where('name', $atts['category'])->orWhere('s_name', $atts['category'])->first()->id;
                    if (!$category_id) {
                            $category_id = Category::create([
                                'name' => $atts['category'],
                                's_name' => $atts['category'],
                                'enabled' => true,
                            ])->id;
                            $atts['category_id'] = $category_id;
                    }
                    }
                $product->update($atts);
                $product->save();
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }
    public function destroy($id)
    {
        //do we need to create a policy for deleting products?
        $this->authorize('createProduct', User::class);
        $product = Product::findOrFail($id);
        try {
            $product->delete();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    }

}
