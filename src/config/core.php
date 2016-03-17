<?php

return [
        'api' => env('STENTLE_API', 'http://api.dev.stentle.com/rest-dev/picnik-rest/'),
        'headers' => ['Content-Type' => 'application/json','X-Domain'=>env('X_DOMAIN','demo')]
];
