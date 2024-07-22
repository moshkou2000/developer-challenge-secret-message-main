<?php

namespace App\Service;

use Illuminate\Support\Str;

class MessageEncryptionService
{
    public static function encryptMessage($message)
    {
        $decryptionKey = Str::random(32);
        $encryptedMessage = openssl_encrypt($message, 'aes-256-cbc', $decryptionKey, 0, Str::random(16));
        return [$encryptedMessage, $decryptionKey];
    }

    public static function decryptMessage($encryptedMessage, $decryptionKey)
    {
        return openssl_decrypt($encryptedMessage, 'aes-256-cbc', $decryptionKey, 0, Str::random(16));
    }
}