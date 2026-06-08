import 'dotenv/config';
import amqp from 'amqplib';

const eventName = process.argv[2] || 'ReviewSubmitted';
const exchange = process.env.RABBITMQ_EXCHANGE || 'student-survival-hub';
const rabbitUrl = process.env.RABBITMQ_URL || 'amqp://guest:guest@localhost:5672/';

const payload = {
  event: eventName,
  timestamp: new Date().toISOString(),
  data: {
    student_id: process.argv[3] || 'uuid-mahasiswa',
    course_id: 'IF-101',
    difficulty_score: 4
  }
};

const connection = await amqp.connect(rabbitUrl);
const channel = await connection.createChannel();

await channel.assertExchange(exchange, 'topic', { durable: true });
channel.publish(exchange, eventName, Buffer.from(JSON.stringify(payload)), {
  persistent: true,
  contentType: 'application/json'
});

console.log(`Published ${eventName} to ${exchange}`);

await channel.close();
await connection.close();
