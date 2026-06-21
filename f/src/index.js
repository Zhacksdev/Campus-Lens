import 'dotenv/config';
import cors from 'cors';
import express from 'express';
import { ApolloServer } from '@apollo/server';
import { expressMiddleware } from '@apollo/server/express4';
import { db } from './db/index.js';
import { resolvers } from './graphql/resolvers.js';
import { typeDefs } from './graphql/typeDefs.js';
import { startConsumer } from './messaging/consumer.js';
import { routes } from './rest/routes.js';

const restPort = Number(process.env.REST_PORT) || 8000;
const graphqlPort = Number(process.env.GRAPHQL_PORT) || 4000;

async function startRestServer() {
  const app = express();

  app.use(cors());
  app.use(express.json());
  app.get('/health', (_, res) => res.json({ status: 'ok' }));
  app.use(routes);
  app.use((error, _req, res, _next) => {
    console.error(error);
    res.status(500).json({ message: error.message || 'Internal server error' });
  });

  app.listen(restPort, () => {
    console.log(`REST API listening on http://localhost:${restPort}`);
  });
}

async function startGraphqlServer() {
  const app = express();
  const server = new ApolloServer({ typeDefs, resolvers });

  await server.start();

  app.use(cors());
  app.use(express.json());
  app.use('/graphql', expressMiddleware(server, {
    context: async () => ({ db })
  }));

  app.listen(graphqlPort, () => {
    console.log(`GraphQL API listening on http://localhost:${graphqlPort}/graphql`);
  });
}

async function main() {
  await startRestServer();
  await startGraphqlServer();
  await startConsumer();
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
