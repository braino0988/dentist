<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockMovment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAnyOrder', User::class);
        return response()->json(['data' => OrderResource::collection(Order::all())], 200);
    }
    public function show($id)
    {
        $this->authorize('viewAnyOrder', User::class);
        $order = Order::findOrFail($id);
        $relatedProducts = $order->products;
        return response()->json(['data' => $relatedProducts], 200);
    }
    public function store(Request $request)
    {
        // $this->authorize('createOrder', User::class);
        $atts = $request->validate([
            'currency' => 'nullable|string|max:3',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'string|nullable',
            'products' => 'nullable|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);
        try {
            $order = DB::transaction(function () use ($request, $atts, &$order) {
                $atts['order_number'] = 'ORD-' . strtoupper(uniqid());
                Log::error('here 2 ');
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'currency' => $atts['currency'] ?? 'SEK',
                    'order_number' => $atts['order_number'],
                    'payment_method' => $atts['payment_method'] ?? null,
                    'notes' => $atts['notes'] ?? null,
                    'subtotal' => 0,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => 0,
                    'order_date' => now(),
                    'status' => 'pending',
                ]);
                if (!$request->has('products') || count($atts['products']) === 0) {
                    return $order;
                }
                $order->number_of_items = count($atts['products']);
                Log::error('here 3 ');
                foreach ($atts['products'] as $productData) {
                    Log::error('i made it here 2');
                    $product = Product::findOrFail($productData['id']);
                    Log::error('i made it here');
                    $discountAmount = 0;
                    if ($product->discount_rate) {
                        $discountAmount = ($product->price * ($product->discount_rate / 100));
                    }
                    $quantity = $productData['quantity'];
                    $lineSubtotal = $product->price * $quantity;
                    $lineTax = $lineSubtotal * ($product->tax_rate ?? 0 / 100);
                    $order->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'unit_price' => $product->price,
                        'tax_rate' => $product->tax_rate,
                        'discount_amount' => $discountAmount,
                        'subtotal' => $lineSubtotal,
                        'tax_amount' => $lineTax,
                    ]);
                    $order->discount_amount += $discountAmount;
                    $order->subtotal += $lineSubtotal;
                    $order->tax_amount += $lineTax;
                }
                $order->total_amount = ($order->subtotal + $order->tax_amount) - $order->discount_amount;
                $order->save();
                return $order;
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order creation failed', 'error' => $e->getMessage()], 500);
        }
        return response()->json(['data' => $order, 'details' => $order->products()->get()]);
    }
    public function destroy($id)
    {
        //add the policy later on
        $this->authorize('updateOrder', User::class);
        $order = Order::findOrFail($id);
        $order->products()->detach();
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
    public function confirm($id)
    {
        //add the policy later on
        $this->authorize('updateOrder', User::class);
        $order = Order::findOrFail($id);
        if($order->status == 'confirmed'){
            return response()->json(['message' => 'you cannot confirm the same order twice'], 400);
        }
        Log::error('order products count ' . $order->products);
        foreach ($order->products as $productData) {
            Log::error('ordered quantity ' . $productData['pivot']->quantity);
            $product = Product::findOrFail($productData->id);
            $projectedQty = $product->stock_quantity - $productData['pivot']->quantity;
            Log::error('product quantity ' . $product->stock_quantity);
            Log::error('projected qty ' . $projectedQty);
            if ($projectedQty < 0) {
                $product->status = 'alertstock';
            } elseif ($projectedQty <= 0) {
                $product->status = 'outofstock';
            } elseif ($projectedQty < 10) {
                $product->status = 'lowstock';
            } else {
                $product->status = 'instock';
            }
            $product->stock_quantity = $projectedQty;
            $productsstatus[$product->name] = $product->status;
            $product->save();
            $order->status = 'confirmed';
            $order->save();
            StockMovment::create([
                'product_id' => $product->id,
                'related_type' => 'order',
                'related_id' => $order->id,
                'type' => 'out',
                'quantity_ordered' => $productData['pivot']->quantity,
                'quantity_in_stock' => $projectedQty,
                'return' => false,
            ]);
        }
        return response()->json(['message' => 'Order confirmed successfully', 'producsstatus' => $productsstatus], 200);
    }
    public function cancel($id)
    {
        //add the policy later on
        $this->authorize('updateOrder', User::class);
        $order = Order::findOrFail($id);
        $order->status = 'canceled';
        $order->save();
        return response()->json(['message' => 'Order canceled successfully'], 200);
    }
}
