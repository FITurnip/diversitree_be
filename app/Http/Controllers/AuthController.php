<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',  // Validate email uniqueness
            'password' => 'required|min:8',  // Ensure minimum password length for security
            'confirmation_password' => 'required|same:password',
        ]);

        // If validation fails, return the error response
        if ($validator->fails()) {
            return $this->api_response_error('Validation failed', $validator->errors()->all(), $validator->errors()->keys());
        }

        // Gather user input and remove confirmation password
        $input = $request->all();
        unset($input['confirmation_password']);

        // Hash the password (considering MongoDB stores it correctly)
        $input['password'] = bcrypt($input['password']);  // You can also use `Hash::make($input['password'])`

        // Create the user
        $user = User::create($input);

        // Create an API token for the user
        $token = $user->createToken('diversitree')->plainTextToken;

        // Return a successful response with user data and token
        return $this->api_response_success('User registered successfully', [
            "user" => $user,
            "token" => $token,
        ]);
    }

    public function login(Request $request) {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->api_response_error('Validation failed', $validator->errors()->all(), $validator->errors()->keys());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->api_response_error('Invalid credentials', [], []);
        }

        // Revoke previous tokens (but keep them in the database)
        foreach ($user->tokens as $token) {
            $token->update([
                'revoked' => true,
            ]); // Mark token as revoked
        }

        // Create a new token for the current session
        $token = $user->createToken('auth_token')->plainTextToken;
        $token->update(['last_logged_in_device' => $request->userAgent()]);

        return $this->api_response_success('Login successful', [
            "user" => $user,
            "token" => $token,
        ]);
    }
}
