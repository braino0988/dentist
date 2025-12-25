<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ImageController extends Controller
{
    use AuthorizesRequests;
    public function destroy(Request $request,$id){
    $this->authorize('createProduct',User::class);
    $image=Image::findOrFail($id);
    if($image){
        try {
            // Delete file from storage
            Storage::disk('public')->delete($image->path);

            // Delete DB record
            $image->delete();

            return response()->json([
                'message' => 'Image deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    }
    public function store(Request $request){
        $this->authorize('createProduct', User::class);
        $atts=$request->validate([
            'product_id'=>'required|exists:products,id',
            'image'=> 'image|max:2048|mimes:jpeg,png,jpg,webp'
        ]);
        $product=Product::findOrFail($atts['product_id']);
        try{
        if($product && $request->hasFile('image')){
            $image=$request->file('image');
            $path=$image->store('products','public');
            $product->images()->create([
                'path' => $path,
            ]);
        }
        return response()->json(['message'=>'image stored'],201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'image not stored something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
