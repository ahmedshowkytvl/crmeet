<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    /**
     * Reset password for a specific user
     */
    public function resetPassword(Request $request)
    {
        try {
            $email = 'admin@stafftobia.com';
            $password = 'ahmed1no2have';
            
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => "User with email '{$email}' not found."
                ], 404);
            }

            // Update the password
            $user->password = Hash::make($password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => "Password for user '{$email}' has been successfully reset.",
                'data' => [
                    'email' => $email,
                    'user_id' => $user->id,
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
