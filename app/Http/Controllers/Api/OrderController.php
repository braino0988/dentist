<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class OrderController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAnyOrder',User::class);
        return response()->json(['data'=>OrderResource::collection(Order::all())],200);
    }
    public function show($id)
    {
        $this->authorize('viewAnyOrder',User::class);
        $order=Order::findOrFail($id);
        $relatedProducts=$order->products;
    }
}