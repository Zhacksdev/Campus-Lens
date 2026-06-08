import amqp from 'amqplib';

let channelPromise;

async function getChannel() {
  if (!channelPromise) {
    channelPromise = (async () => {
      const connection = await amqp.connect(process.env.RABBITMQ_URL);
      const channel = await connection.createChannel();
      await channel.assertExchange(process.env.RABBITMQ_EXCHANGE, 'topic', { durable: true });
      return channel;
    })();
  }

  return channelPromise;
}

export async function publishRecommendationUpdated({ studentId, suggestions, triggerEvent, targetCareer }) {
  if (!process.env.RABBITMQ_URL) {
    return;
  }

  try {
    const channel = await getChannel();
    const message = {
      event: 'RecommendationUpdated',
      timestamp: new Date().toISOString(),
      data: {
        student_id: studentId,
        new_suggestions: suggestions,
        trigger_event: triggerEvent,
        target_career: targetCareer
      }
    };

    channel.publish(
      process.env.RABBITMQ_EXCHANGE,
      'RecommendationUpdated',
      Buffer.from(JSON.stringify(message)),
      { persistent: true, contentType: 'application/json' }
    );
  } catch (error) {
    console.warn(`Failed to publish RecommendationUpdated: ${error.message}`);
  }
}
