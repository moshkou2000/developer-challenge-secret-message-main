<?php

namespace App\Repository;

use app\Entity\MessageEntity;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function create(MessageEntity $message): int;
    public function find(int $identifier) : MessageEntity;
    public function findAll(int $sender, int $receiver, int $page, int $size) : LengthAwarePaginator;
    public function search(MessageEntity $message);
    public function delete(int $identifier) : bool;
}