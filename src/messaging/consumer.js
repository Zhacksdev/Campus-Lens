import amqp from 'amqplib';
import { refreshFromEvent } from '../services/recommendationService.js';

const consumedEvents = ['ReviewSubmitted', 'DifficultyScoreUpdated'];

export async function startConsumer() {
  if (!process.env.RABBITMQ_URL) {
    console.warn('RABBITMQ_URL not set, consumer disabled');
    return;
  }

  try {
    const connection = await amqp.connect(process.env.RABBITMQ_URL);
    const channel = await connection.createChannel();

    await channel.assertExchange(process.env.RABBITMQ_EXCHANGE, 'topic', { durable: true });
    const queue = await channel.assertQueue('recommendation-service-events', { durable: true });

    for (const eventName of consumedEvents) {
      await channel.bindQueue(queue.queue, process.env.RABBITMQ_EXCHANGE, eventName);
    }

    await channel.consume(queue.queue, async (message) => {
      if (!message) {
        return;
      }

      const eventName = message.fields.routingKey;

      try {
        const payload = JSON.parse(message.content.toString());
        await refreshFromEvent(payload.event || eventName, payload);
        channel.ack(message);
      } catch (error) {
        console.error(`Failed to process ${eventName}: ${error.message}`);
        channel.nack(message, false, false);
      }
    });

    console.log(`RabbitMQ consumer listening for ${consumedEvents.join(', ')}`);
  } catch (error) {
    console.warn(`RabbitMQ consumer unavailable: ${error.message}`);
  }
}
