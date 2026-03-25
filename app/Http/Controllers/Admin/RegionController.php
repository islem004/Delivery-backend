<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RegionController extends Controller
{
    private $regions = [
        ['id' => 1, 'nom' => 'Grand Tunis'],
        ['id' => 2, 'nom' => 'Cap Bon'],
        ['id' => 3, 'nom' => 'Sahel + Kairouan'],
        ['id' => 4, 'nom' => 'Nord'],
        ['id' => 5, 'nom' => 'Sud']
    ];

    // Voir toutes les régions
    public function index()
    {
        return response()->json($this->regions);
    }

    // Ajouter région
    public function store($nom)
    {
        return response()->json([
            'message' => "Région $nom ajoutée (simulation)"
        ]);
    }

    // Modifier région
    public function update($id, $nom)
    {
        return response()->json([
            'message' => "Région $id modifiée en $nom (simulation)"
        ]);
    }

    // Supprimer région
    public function delete($id)
    {
        return response()->json([
            'message' => "Région $id supprimée (simulation)"
        ]);
    }
}
