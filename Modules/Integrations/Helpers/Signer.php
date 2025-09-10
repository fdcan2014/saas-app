<?php
namespace Modules\Integrations\Helpers;

class Signer
{
    public static function sign(string $secret, string $body, int $ts): string
    {
        $msg = $ts . '.' . $body;
        $mac = hash_hmac('sha256', $msg, $secret);
        return 'sha256=' . $mac;
    }
}
