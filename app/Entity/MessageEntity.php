<?php

namespace app\Entity;

class MessageEntity
{
    public $id;
    public $identifier;
    public $sender;
    public $recipient;
    public $message;
    public $decryption_key;
    public $expires_at;
    public $created_by;
    public $updated_by;
    public $created_at;
    public $updated_at;
}