<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:80',
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

        $user->tokens()->delete();

        // Create a new token for the current session
        $token = $user->createToken('diversitree_token')->plainTextToken;
        $user->update(['last_logged_in_device' => $request->userAgent()]);

        return $this->api_response_success('Login successful', [
            "user" => $user,
            "token" => $token,
        ]);
    }

    public function editProfile(Request $request)
    {
        // Ensure the user is authenticated
        $user = Auth::user();
        if (!$user) {
            return $this->api_response_error('Unauthorized', [], []);
        }

        // Determine if the email needs to be unique or not
        $emailRule = 'required|email';
        if ($request->email && $request->email !== $user->email) {
            $emailRule .= '|unique:users,email'; // Only apply unique validation if email is different
        }

        // Validate the request input
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:80',  // Ensure valid name
            'email' => $emailRule,  // Dynamically apply email validation
            'password' => 'nullable|min:8',  // Optional password update with minimum length
            'confirmation_password' => 'nullable|same:password',  // Ensure password confirmation matches
        ]);

        if ($validator->fails()) {
            return $this->api_response_error('Validation failed', $validator->errors()->all(), $validator->errors()->keys());
        }

        // Update user profile with the validated data
        $input = $request->only(['name', 'email', 'password']); // Only allow these fields
        if ($request->has('password')) {
            // Hash the password before saving it
            $input['password'] = bcrypt($input['password']);
        }

        // Update user profile
        $user->update($input);

        // Return the updated user data
        return $this->api_response_success('Profile updated successfully', [
            'user' => $user,
        ]);
    }
}
