<?php
// app/Http/Controllers/Admin/LivraisonController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivraisonController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Delivery::with(['client.user', 'assignedStaff.user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    public function show($id)
    {
        $livraison = \App\Models\Delivery::with(['client.user', 'assignedStaff.user', 'statusHistories', 'items'])->findOrFail($id);
        return response()->json($livraison);
    }

    public function update(Request $request, $id)
    {
        $livraison = \App\Models\Delivery::findOrFail($id);
        
        $oldStatus = $livraison->status;
        $updates = $request->only(['assigned_staff_id', 'status', 'internal_notes']);

        if ($request->has('assigned_staff_id')) {
            $livraison->assigned_staff_id = $request->assigned_staff_id;
        }

        if ($request->has('status')) {
            $livraison->status = $request->status;
        }

        if ($request->has('internal_notes')) {
            $livraison->internal_notes = $request->internal_notes;
        }

        if ($livraison->isDirty()) {
            $livraison->save();

            // Record history
            $livraison->statusHistories()->create([
                'status' => $livraison->status,
                'updated_by' => auth()->id(),
                'notes' => $request->history_note ?? "Mise à jour par l'administrateur",
            ]);
        }

        return response()->json($livraison->load(['client.user', 'assignedStaff.user', 'statusHistories']));
    }

    public function destroy($id)
    {
        $livraison = \App\Models\Delivery::findOrFail($id);
        $livraison->delete();
        return response()->json(['message' => 'Livraison supprimée']);
    }
}
