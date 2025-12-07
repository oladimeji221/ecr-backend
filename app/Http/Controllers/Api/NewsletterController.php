<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendGeneralNewsletter;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Get subscriber count
     */
    public function subscriberCount()
    {
        // Only admins can view subscriber count
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can view subscriber count.'
            ], 403);
        }

        $count = NewsletterSubscription::count();

        return response()->json([
            'count' => $count
        ], 200);
    }
    /**
     * Send newsletter to all subscribers
     */
    public function send(Request $request)
    {
        // Only admins can send newsletters
        if (!auth()->user() || !auth()->user()->is_admin) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can send newsletters.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Dispatch job to send newsletter
        SendGeneralNewsletter::dispatch(
            $request->subject,
            $request->content,
            $request->image
        );

        return response()->json([
            'message' => 'Newsletter is being sent to all subscribers.'
        ], 200);
    }
}
