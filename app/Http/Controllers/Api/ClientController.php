<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(Request $request)
    {
        $client = $request->user()->client;

        if (!$client) {
            return response()->json(['message' => 'Client profile not found'], 404);
        }

        return response()->json($client);
    }

    public function update(Request $request)
    {
        $client = $request->user()->client;

        if (!$client) {
            return response()->json(['message' => 'Client profile not found'], 404);
        }

        $request->validate([
            'company_name'    => 'sometimes|string|max:255',
            'tax_id'          => 'sometimes|nullable|string',
            'billing_address' => 'sometimes|nullable|string',
            'shipping_address'=> 'sometimes|nullable|string',
            'contact_person'  => 'sometimes|nullable|string',
            'contact_email'   => 'sometimes|nullable|email',
            'contact_phone'   => 'sometimes|nullable|string',
            'payment_terms'   => 'sometimes|nullable|string',
        ]);

        $client->update($request->only([
            'company_name',
            'tax_id',
            'billing_address',
            'shipping_address',
            'contact_person',
            'contact_email',
            'contact_phone',
            'payment_terms',
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'client'  => $client,
        ]);
    }
}