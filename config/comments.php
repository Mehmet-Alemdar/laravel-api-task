<?php

return [
    'banned_keywords' => env('BANNED_KEYWORDS', 'spam,badword'),
    'cache_ttl'       => env('COMMENTS_CACHE_TTL', 60),
];
