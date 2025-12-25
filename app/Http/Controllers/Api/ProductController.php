<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        return response()->json(['data' => ProductResource::collection(Product::all())], 200);
    }
    public function show(Request $request, $id)
    {
        $product = Product::with('images')->findOrFail($id);
        return response()->json(['data' => ProductResource::make($product)], 200);
    }
    public function store(Request $request)
    {
        $this->authorize('createProduct', User::class);
        $atts = $request->validate([
            'name' => 'required_without:s_name|string|max:255',
            's_name' => 'required_without:name|string|max:255',
            'description' => 'nullable|string',
            's_description' => 'nullable|string',
            'sku'=>'nullable|string|unique:products,sku',
            'price' => 'required|numeric',
            'cost'=> 'required|numeric',
            'category' => 'required|string',
            'stock_quantity' => 'nullable|integer',
            'delivery_option' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|max:100|min:0',
            'product_rate' => 'nullable|numeric|max:5|min:0',
            'discount_rate' => 'nullable|numeric|max:100|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048|mimes:jpeg,png,jpg,webp',
        ]);
        try {
            $product = DB::transaction(function () use ($atts, &$product) {
                $category_id = Category::where('name', $atts['category'])->orWhere('s_name', $atts['category'])->first()->id;
                if (!$category_id) {
                    $category_id = Category::create([
                        'name' => $atts['category'],
                        's_name' => $atts['category'],
                        'enabled' => true,
                    ])->id;
                }
                if(!isset($atts['sku'])){
                $atts['sku'] = 'SKU-' . strtoupper(uniqid());}
                return Product::create([
                    'name' => $atts['name'] ?? $atts['s_name'],
                    's_name' => $atts['s_name'] ?? $atts['name'],
                    'sku'=> $atts['sku'],
                    'description' => $atts['description'] ?? null,
                    's_description' => $atts['s_description'] ?? null,
                    'price' => $atts['price'],
                    'cost' => $atts['cost'],
                    'product_rate' => $atts['product_rate'] ?? 0,
                    'status' => $atts['status'] ?? 'instock',
                    'category_id' => $category_id,
                    'stock_quantity' => $atts['stock_quantity'] ?? 0,
                    'delivery_option' => $atts['delivery_option'] ?? null,
                    'tax_rate' => $atts['tax_rate'] ?? 0,
                    'discount_rate' => $atts['discount_rate'] ?? 0,
                ]);
            });
            if ($request->hasFile('images')) {
                try {
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('products', 'public');
                        $product->images()->create([
                            'path' => $path,
                        ]);
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Product created but failed to upload the images or one of them you can re upload by updating the product and only sending the images',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product',
                'error' => $e->getMessage(),
            ], 500);
        }
        return response()->json([
            'message' => 'Product created successfully',
            'product' =>ProductResource::make($product->load('images')),
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
            'sku'=>'nullable|string|unique:products,sku,'.$product->id,
            'product_rate' => 'nullable|numeric',
            'status' => 'nullable|string',
            'price' => 'nullable|numeric',
            'cost'=> 'nullable|numeric',
            'category' => 'nullable|string',
            'stock_quantity' => 'nullable|integer',
            'delivery_option' => 'nullable|string',
            'tax_rate' => 'nullable|numeric',
            'discount_rate' => 'nullable|numeric',
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
