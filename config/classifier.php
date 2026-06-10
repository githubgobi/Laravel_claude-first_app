<?php

return [
    /*
    | The system prompt is built dynamically in TicketClassifier::buildSystemPrompt()
    | so it can embed the per-request nonce for Layer 1 delimiter fencing.
    | Settings below are available for tuning without touching the service class.
    */
    'temperature' => env('CLASSIFIER_TEMPERATURE', 0.1),
    'max_tokens'  => env('CLASSIFIER_MAX_TOKENS', 256),
];
