<?php

namespace app\Http\Controllers;

use app\Entity\MessageEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use app\Repository\MessageRepository;
use App\Service\MessageEncryptionService;
use OpenApi\Annotations as OA;

/**
 * REST API route base class.
 *
 * @OA\Info(
 *     title="Secret Message API REST",
 *     version="0.0.1",
 *     description="It is in progress"
 * )
 */
class MessageController extends Controller
{ 
    private $repository;

    public function __construct()
    {
        $this->repository = new MessageRepository();
    }
    
    /**
     * @OA\Get(
     *     path="/api/messages",
     *     summary="Retrieve all messages",
     *     @OA\Parameter(
     *         name="sender",
     *         in="query",
     *         required=false,
     *         description="The sender of the messages",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="recipient",
     *         in="query",
     *         required=true,
     *         description="The recipient of the messages",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="decryption_key",
     *         in="query",
     *         required=false,
     *         description="The decryption key for the messages",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="messages",
     *                 type="array",
     *                 @OA\Items(type="object", @OA\Property(property="message", type="string"))
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="array",
     *                 @OA\Items(type="object", @OA\Property(property="error", type="string"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters",
     *     ),
     * )
     */
    public function readAll(Request $request)
    {
        $request->validate([
            'sender' => 'sender|string',
            'recipient' => 'required|string'
        ]);

        $messages = $this->repository->findAll($request->sender, $request->recipient);

        $responses = [];
        $errors = [];

        foreach ($messages as $message) {
            if ($message->expires_at && $message->expires_at->isPast()) {
                $this->repository->delete($message->id);
                $errors[] = ['Message [' . $message->identifier . '] has expired.'];
                continue;
            }

            if ($request->input('decryption_key') !== $message->decryption_key) {
                $errors[] = ['Invalid decryption key for [' . $message->identifier . '].'];
                continue;
            }

            $decryptedMessage = MessageEncryptionService::decryptMessage($message->message, $message->decryption_key);
            $this->repository->delete($message->id);

            $responses[] = ['message' => $decryptedMessage];
        }

        return response()->json(['messages' => $responses, 'error' => $errors]);
    }
/**
     * @OA\Get(
     *     path="/api/messages/{identifier}",
     *     summary="Retrieve a single message",
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="The unique identifier of the message",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="decryption_key",
     *         in="query",
     *         required=true,
     *         description="The decryption key for the message",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid decryption key",
     *     ),
     *     @OA\Response(
     *         response=410,
     *         description="Message has expired",
     *     ),
     * )
     */
    public function read($identifier, Request $request)
    {
        $message = $this->repository->find($identifier);

        if ($message->expires_at && $message->expires_at->isPast()) {
            $this->repository->delete($identifier);
            return response()->json(['error' => 'Message has expired.'], 410);
        }

        if ($request->input('decryption_key') !== $message->decryption_key) {
            return response()->json(['error' => 'Invalid decryption key.'], 403);
        }

        $decryptedMessage = MessageEncryptionService::decryptMessage($message->message, $message->decryption_key);

        $this->repository->delete($identifier);

        return response()->json(['message' => $decryptedMessage]);
    }

    /**
     * @OA\Post(
     *     path="/api/messages",
     *     summary="Send a new message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello World"),
     *             @OA\Property(property="sender", type="string", example="sender@example.com"),
     *             @OA\Property(property="recipient", type="string", example="recipient@example.com"),
     *             @OA\Property(property="expiry", type="integer", example=60),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="identifier", type="string"),
     *             @OA\Property(property="decryption_key", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request parameters",
     *     ),
     * )
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'sender' => 'sender|string',
            'recipient' => 'required|string',
            'expiry' => 'nullable|integer'
        ]);

        $identifier = Str::uuid();
        $expiresAt = $request->expiry ? Carbon::now()->addMinutes($request->expiry) : null;
        [$encryptedMessage, $decryptionKey] = MessageEncryptionService::encryptMessage($request->message);

        $message = new MessageEntity($identifier, 
            $request->sender, 
            $request->recipient, 
            $encryptedMessage, 
            $decryptionKey, 
            $expiresAt,
            Auth::id(),
            Auth::id(),
        );

        $this->repository->create($message);

        return response()->json([
            'identifier' => $identifier,
            'decryption_key' => $decryptionKey,
        ]);
    }

     /**
     * @OA\Delete(
     *     path="/api/messages/{identifier}",
     *     summary="Delete a message",
     *     @OA\Parameter(
     *         name="identifier",
     *         in="path",
     *         required=true,
     *         description="The unique identifier of the message to delete",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Message deleted successfully.")
     *         )
     *     ),
     * )
     */
    public function delete($identifier)
    {
        $this->repository->delete($identifier);

        return response()->json(['message' => 'Message deleted successfully.']);
    }

}
