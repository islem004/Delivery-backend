<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $deliveries = \App\Models\Delivery::all();

        // Monthly stats (Database agnostic grouping in PHP)
        $statsParMois = \App\Models\Delivery::latest()
            ->limit(100)
            ->get()
            ->groupBy(function($d) {
                return $d->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'total' => $group->count(),
                    'delivered' => $group->where('status', 'delivered')->count(),
                ];
            })
            ->take(6);

        // Stats by status
        $synthese = [
            'livraisonsTotales' => $deliveries->count(),
            'livraisonsLivrees' => $deliveries->where('status', 'delivered')->count(),
            'livraisonsEnCours' => $deliveries->whereIn('status', ['pending', 'picked_up'])->count(),
            'livraisonsAnnulees' => $deliveries->where('status', 'cancelled')->count(),
        ];

        // Best staff
        $bestStaff = \App\Models\Staff::with('user')
            ->withCount(['assignedDeliveries as delivered_count' => function ($query) {
                $query->where('status', 'delivered');
            }])
            ->orderBy('delivered_count', 'desc')
            ->first();

        return response()->json([
            'statsParMois' => $statsParMois,
            'meilleurLivreur' => $bestStaff ? $bestStaff->user->first_name . ' ' . $bestStaff->user->last_name : 'N/A',
            'synthese' => $synthese,
        ]);
    }
}
