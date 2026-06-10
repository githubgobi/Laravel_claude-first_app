<?php

namespace App\Http\Controllers;

use App\Services\TicketClassifier;
use Illuminate\Http\{JsonResponse, Request};

class TicketClassifierController extends Controller
{
    public function __construct(private readonly TicketClassifier $classifier) {}

    public function classify(Request $request): JsonResponse
    {
        $data = $request->validate(['ticket' => 'required|string|max:5000']);

        $r = $this->classifier->classify($data['ticket']);

        // Serialize enums to their string values for the JSON response
        $r['category']  = $r['category']->value;
        $r['priority']  = $r['priority']->value;
        $r['sentiment'] = $r['sentiment']->value;

        return response()->json($r);
    }
}
