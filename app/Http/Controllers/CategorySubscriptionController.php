<?php

namespace App\Http\Controllers;

use App\Models\CategorySubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategorySubscriptionController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'category_id' => 'required|exists:categories,id',
            ]);

            // Check for existing subscription to prevent duplicates
            $existingSubscription = CategorySubscription::where('email', $validatedData['email'])
                                                        ->where('category_id', $validatedData['category_id'])
                                                        ->first();

            if ($existingSubscription) {
                return response()->json([
                    'message' => 'You are already subscribed to this category!'
                ], 409); // Conflict
            }

            $subscription = CategorySubscription::create($validatedData);

            return response()->json([
                'message' => 'Subscription successful!',
                'subscription' => $subscription
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
