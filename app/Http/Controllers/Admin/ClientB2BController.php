<?php
// app/Http/Controllers/Admin/ClientB2BController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientB2BController extends Controller
{
    public function index()
    {
        return response()->json(\App\Models\Client::with('user')->get());
    }

    public function show($id)
    {
        $client = \App\Models\Client::with('user')->findOrFail($id);
        return response()->json($client);
    }

    public function update(Request $request, $id)
    {
        $client = \App\Models\Client::findOrFail($id);
        $client->update($request->all());
        return response()->json($client->load('user'));
    }

    public function destroy($id)
    {
        $client = \App\Models\Client::findOrFail($id);
        $client->delete();
        return response()->json(['message' => 'Client supprimé']);
    }
}
