import { ApolloServer } from '@apollo/server';
import { startStandaloneServer } from '@apollo/server/standalone';

const typeDefs = `#graphql
  type Query {
    _service: String
  }
`;

const resolvers = {
    Query: {
      _service: () => "Content Service"
    },
  };

const server = new ApolloServer({
    typeDefs,
    resolvers,
  });
  
  const { url } = await startStandaloneServer(server, {
    listen: { port: 4002 },
  });
  
  console.log(`🚀  Content service ready at: ${url}`);