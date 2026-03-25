<?php
// app/Http/Controllers/Admin/LivreurController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivreurController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\Staff::with('user')->get());
    }

    public function show($id)
    {
        $staff = \App\Models\Staff::with('user')->findOrFail($id);
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        // Typically staff is created from a User
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'employee_id' => 'required|string|unique:staff,employee_id',
            'hire_date' => 'nullable|date',
        ]);

        $staff = \App\Models\Staff::create($validated);
        return response()->json($staff->load('user'), 201);
    }

    public function update(Request $request, $id)
    {
        $staff = \App\Models\Staff::findOrFail($id);
        $staff->update($request->all());
        return response()->json($staff->load('user'));
    }

    public function destroy($id)
    {
        $staff = \App\Models\Staff::findOrFail($id);
        $staff->delete();
        return response()->json(['message' => 'Livreur supprimé']);
    }

    public function getBest()
    {
        $best = \App\Models\Staff::with('user')
            ->withCount(['assignedDeliveries as livraisons_reussies' => function ($query) {
                $query->where('status', 'delivered');
            }])
            ->orderBy('livraisons_reussies', 'desc')
            ->first();

        return response()->json($best);
    }
}
