<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user()->client;

        $stats = [
            'total'      => Delivery::where('client_id', $client->id)->count(),
            'pending'    => Delivery::where('client_id', $client->id)->where('status', 'pending')->count(),
            'in_transit' => Delivery::where('client_id', $client->id)->where('status', 'in_transit')->count(),
            'delivered'  => Delivery::where('client_id', $client->id)->where('status', 'delivered')->count(),
            'failed'     => Delivery::where('client_id', $client->id)->where('status', 'failed')->count(),
        ];

        return response()->json([
            'message' => 'Dashboard stats',
            'stats'   => $stats,
        ]);
    }
}