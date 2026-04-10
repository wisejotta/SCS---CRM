<?php

return [
    'secret' => env('STRIPE_SECRET_KEY', null),
    'endpoint_secret' => env('STRIPE_ENDPOINT_SECRET', null),
    'publishable' => env('STRIPE_PUBLISHABLE_KEY', null),
];