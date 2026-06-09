<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class RabbitMQPublisher
{
    public function publish(string $event, array $data): bool
    {
        if (! config('services.rabbitmq.enabled')) {
            return false;
        }

        $connection = null;
        $channel = null;

        try {
            $connection = new AMQPStreamConnection(
                config('services.rabbitmq.host'), config('services.rabbitmq.port'),
                config('services.rabbitmq.user'), config('services.rabbitmq.password')
            );
            $channel = $connection->channel();
            $exchange = config('services.rabbitmq.exchange');
            $channel->exchange_declare($exchange, 'topic', false, true, false);
            $message = new AMQPMessage(json_encode([
                'event' => $event,
                'timestamp' => now()->utc()->toIso8601String(),
                'data' => $data,
            ], JSON_THROW_ON_ERROR), [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ]);
            $channel->basic_publish($message, $exchange, $event);

            return true;
        } catch (Throwable $exception) {
            Log::warning('RabbitMQ publish failed', ['event' => $event, 'message' => $exception->getMessage()]);

            return false;
        } finally {
            $channel?->close();
            $connection?->close();
        }
    }
}
