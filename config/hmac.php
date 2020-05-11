<?php
declare(strict_types = 1);

return [
    /** алгоритм кодирования, md2 самый быстрый */
    'algo'      => env('HMAC_ALGO', 'md2'),
    /** приватный ключ */
    'key'       => env('HMAC_KEY', null),
    /** время жизни подписи */
    'ttl'       => (int)env('HMAC_TTL', 60),
    /** наименование поля в подписью */
    'field'     => env('HMAC_FIELD', 'hmac_signature'),
];
