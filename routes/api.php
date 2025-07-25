<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/order-count-by-order-id/{id}', function (Request $request, $id) {
    if (!$id) {
        return response()->json(['error' => 'id is required'], 400);
    }

    $shop = "8b4e7e-9b.myshopify.com";
    $token = env('SHOPIFY_API_TOKEN');

    // Step 1: Get the order to extract customer ID
    $orderResponse = Http::withHeaders([
        'X-Shopify-Access-Token' => $token,
        'Content-Type' => 'application/json',
    ])->get("https://$shop/admin/api/2023-07/orders/$id.json");

    if (!$orderResponse->ok()) {
        return response()->json(['error' => 'Failed to fetch order'], 500);
    }

    $customerId = $orderResponse->json('order.customer.id');

    if (!$customerId) {
        return response()->json(['error' => 'Customer ID not found in order'], 404);
    }

    // Step 2: Fetch all orders for this customer
    $ordersResponse = Http::withHeaders([
        'X-Shopify-Access-Token' => $token,
        'Content-Type' => 'application/json',
    ])->get("https://$shop/admin/api/2023-07/orders.json?customer_id=$customerId");

    if (!$ordersResponse->ok()) {
        return response()->json(['error' => 'Failed to fetch orders'], 500);
    }

    $orders = $ordersResponse->json()['orders'] ?? [];

    return response()->json([
        'orderCount' => count($orders),
    ]);
});

Route::get('get-sso/{token}', function ($token) {
    $response = Http::get('https://saml-sso.improvlearning.com/gettoken');

    if (!$response->ok()) {
        return response()->json(['error' => 'Failed to fetch token'], 500);
    }

    return response()->json([
        'token' => $response->body(),
    ]);
});
