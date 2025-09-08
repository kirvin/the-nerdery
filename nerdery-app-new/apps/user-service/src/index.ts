import { ApolloServer } from '@apollo/server';
import { startStandaloneServer } from '@apollo/server/standalone';
import pool from './db';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';

const JWT_SECRET = 'your-super-secret-key'; // Replace with a proper secret management strategy

const typeDefs = `#graphql
  type User @key(fields: "id") {
    id: ID!
    username: String!
    email: String!
    cool_points: Int!
  }

  type Query {
    _entities(representations: [_Any!]!): [_Entity]!
    me: User
  }

  type Mutation {
    register(username: String!, email: String!, password: String!): AuthPayload!
    login(email: String!, password: String!): AuthPayload!
  }

  type AuthPayload {
    token: String!
    user: User!
  }

  scalar _Any
  union _Entity = User
`;

const resolvers = {
    Query: {
        me: async (_, __, context) => {
            if (!context.userId) throw new Error('Not authenticated');
            const res = await pool.query('SELECT * FROM users WHERE id = $1', [context.userId]);
            return res.rows[0];
        }
    },
    Mutation: {
        register: async (_, { username, email, password }) => {
            const hashedPassword = await bcrypt.hash(password, 10);
            const res = await pool.query(
                'INSERT INTO users (username, email, password_hash) VALUES ($1, $2, $3) RETURNING *',
                [username, email, hashedPassword]
            );
            const user = res.rows[0];
            const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '1h' });
            return { token, user };
        },
        login: async (_, { email, password }) => {
            const res = await pool.query('SELECT * FROM users WHERE email = $1', [email]);
            const user = res.rows[0];
            if (!user) throw new Error('Invalid credentials');

            const valid = await bcrypt.compare(password, user.password_hash);
            if (!valid) throw new Error('Invalid credentials');

            const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '1h' });
            return { token, user };
        }
    }
  };

const server = new ApolloServer({
    typeDefs,
    resolvers,
  });
  
  const { url } = await startStandaloneServer(server, {
    context: async ({ req }) => {
        const token = req.headers.authorization?.split(' ')?.[1];
        if (token) {
            try {
                const { userId } = jwt.verify(token, JWT_SECRET) as { userId: string };
                return { userId };
            } catch (e) {
                // Invalid token
            }
        }
        return {};
    },
    listen: { port: 4001 },
  });
  
  console.log(`🚀  Server ready at: ${url}`);