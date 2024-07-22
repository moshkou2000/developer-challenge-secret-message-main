<?php

namespace app\Repository;

use app\Entity\MessageEntity;
use App\Models\Message;
use App\Repository\MessageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageRepository implements MessageRepositoryInterface
{
    public const DEFAULT_PAGE_SIZE = 10;

    public function create(MessageEntity $message): int {
        $newMessage = Message::create([
            'identifier' => $message->identifier,
            'sender' => $message->sender,
            'recipient' => $message->recipient,
            'message' => $message->message,
            'decryption_key' => $message->decryption_key,
            'expires_at' => $message->expires_at,
            'created_by' => $message->created_by,
            'updated_by' => $message->updated_by,
        ]);

        return $newMessage->id;
    }
    
    public function find(int $identifier) : MessageEntity {
        return Message::where('identifier', $identifier)->firstOrFail();
    }

    public function findAll(int $sender, int $receiver, int $currentPage = 0, int $perPage = self::DEFAULT_PAGE_SIZE) : LengthAwarePaginator {
        return Message::paginate($currentPage, $perPage);
    }

    public function search(MessageEntity $message)
    {
        $query = Message::query();
        
        if ($message->id) {
            $query->where('id', '=', $message->id);
        }
        if ($message->sender) {
            $query->where('sender', '=', $message->sender);
        }
        if ($message->recipient) {
            $query->where('recipient', '=', $message->recipient);
        }
        if ($message->message) {
            $query->where('message', '=', $message->message);
        }
        if ($message->expires_at) {
            $query->where('expires_at', '=', $message->expires_at);
        }

        $messageModels = $query->get();
        $messages = $messageModels->map(function ($messageModel) {
            return new MessageEntity(
                $messageModel->id,
                $messageModel->identifier,
                $messageModel->sender,
                $messageModel->recipient,
                $messageModel->message,
                $messageModel->decryption_key,
                $messageModel->expires_at
            );
        });
    
        return $messages->all(); 
    }

    public function delete(int $identifier) : bool {
        return Message::where('identifier', $identifier)->firstOrFail()->delete();
    }

    public function deleteExpired() {
        return Message::where('expires_at', '<', Carbon::now())->delete();
    }
}
