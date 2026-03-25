<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::with('client')->latest()->get());
    }

    public function pending()
    {
        return response()->json(
            User::where('status', 'pending_admin')->with('client')->get()
        );
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        
        // Generate a 6-digit verification code
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->update([
            'status' => 'pending_verification',
            'verification_code' => $code
        ]);

        // Send the real email (will be logged in local log due to .env)
        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $code));
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$user->email}: " . $e->getMessage());
        }

        return response()->json([
            'message' => "Utilisateur approuvé. Code de vérification envoyé à {$user->email}.",
            'code_debug' => $code // Optionnel pour le dev
        ]);
    }

    public function disable($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => false, 'status' => 'rejected']);
        
        return response()->json([
            'message' => "Utilisateur $id désactivé"
        ]);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => "Utilisateur $id supprimé"
        ]);
    }
}
