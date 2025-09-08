import { ApolloServer } from '@apollo/server';
import { startStandaloneServer } from '@apollo/server/standalone';
import { Pool } from 'pg';

const typeDefs = `#graphql
  type Discussion {
    id: ID!
    title: String!
    body: String!
    author: User!
    comments: [Comment!]!
    createdAt: String!
    updatedAt: String!
  }

  type Comment {
    id: ID!
    body: String!
    author: User!
    parentComment: Comment
    createdAt: String!
    updatedAt: String!
  }

  type List {
    id: ID!
    title: String!
    description: String
    author: User!
    isPublic: Boolean!
    items: [ListItem!]!
    members: [User!]!
    createdAt: String!
    updatedAt: String!
  }

  type ListItem {
    id: ID!
    body: String!
    author: User!
    upvotes: Int!
    list: List!
    createdAt: String!
    updatedAt: String!
  }

  type News {
    id: ID!
    title: String!
    body: String!
    author: User!
    createdAt: String!
    updatedAt: String!
  }

  type Reaction {
    id: ID!
    emoji: String!
    user: User!
  }

  type Query {
    discussions: [Discussion!]!
    discussion(id: ID!): Discussion
    lists: [List!]!
    list(id: ID!): List
    news: [News!]!
    newsArticle(id: ID!): News
  }

  type Mutation {
    createDiscussion(title: String!, body: String!): Discussion!
    createComment(discussionId: ID!, body: String!, parentCommentId: ID): Comment!
    createList(title: String!, description: String, isPublic: Boolean!): List!
    addListItem(listId: ID!, body: String!): ListItem!
    upvoteListItem(id: ID!): ListItem!
    addListMember(listId: ID!, userId: ID!): List!
    createNews(title: String!, body: String!): News!
    createReaction(contentId: ID!, contentType: String!, emoji: String!): Reaction!
  }
`;

const pgPool = new Pool({
    user: 'nerdery_db',
    host: 'localhost',
    database: 'nerdery',
    password: 'nerdery_password',
    port: 5432,
});

const resolvers = {
  Query: {
    discussions: async () => {
        const { rows } = await pgPool.query('SELECT * FROM discussions');
        return rows;
    },
    discussion: async (_, { id }) => {
        const { rows } = await pgPool.query('SELECT * FROM discussions WHERE id = $1', [id]);
        return rows[0];
    },
    lists: async () => {
        const { rows } = await pgPool.query('SELECT * FROM lists');
        return rows;
    },
    list: async (_, { id }) => {
        const { rows } = await pgPool.query('SELECT * FROM lists WHERE id = $1', [id]);
        return rows[0];
    },
    news: async () => {
        const { rows } = await pgPool.query('SELECT * FROM news');
        return rows;
    },
    newsArticle: async (_, { id }) => {
        const { rows } = await pgPool.query('SELECT * FROM news WHERE id = $1', [id]);
        return rows[0];
    },
  },
  Mutation: {
    createDiscussion: async (_, { title, body }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO discussions (user_id, title, body) VALUES ($1, $2, $3) RETURNING *',
            [user.id, title, body]
        );
        return rows[0];
    },
    createComment: async (_, { discussionId, body, parentCommentId }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO discussion_comments (discussion_id, user_id, body, parent_comment_id) VALUES ($1, $2, $3, $4) RETURNING *',
            [discussionId, user.id, body, parentCommentId]
        );
        return rows[0];
    },
    createList: async (_, { title, description, isPublic }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO lists (user_id, title, description, is_public) VALUES ($1, $2, $3, $4) RETURNING *',
            [user.id, title, description, isPublic]
        );
        return rows[0];
    },
    addListItem: async (_, { listId, body }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO list_items (list_id, user_id, body) VALUES ($1, $2, $3) RETURNING *',
            [listId, user.id, body]
        );
        return rows[0];
    },
    upvoteListItem: async (_, { id }) => {
        const { rows } = await pgPool.query(
            'UPDATE list_items SET upvotes = upvotes + 1 WHERE id = $1 RETURNING *',
            [id]
        );
        return rows[0];
    },
    addListMember: async (_, { listId, userId }) => {
        await pgPool.query(
            'INSERT INTO list_members (list_id, user_id) VALUES ($1, $2)',
            [listId, userId]
        );
        const { rows } = await pgPool.query('SELECT * FROM lists WHERE id = $1', [listId]);
        return rows[0];
    },
    createNews: async (_, { title, body }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO news (user_id, title, body) VALUES ($1, $2, $3) RETURNING *',
            [user.id, title, body]
        );
        return rows[0];
    },
    createReaction: async (_, { contentId, contentType, emoji }, { user }) => {
        const { rows } = await pgPool.query(
            'INSERT INTO reactions (user_id, content_id, content_type, emoji) VALUES ($1, $2, $3, $4) RETURNING *',
            [user.id, contentId, contentType, emoji]
        );
        return rows[0];
    },
  },
};

const server = new ApolloServer({
  typeDefs,
  resolvers,
});

const { url } = await startStandaloneServer(server, {
  listen: { port: 4002 },
  context: async ({ req }) => {
    // TODO: get user from token
    return { user: { id: 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11' } };
  }
});

console.log(`🚀  Content service ready at: ${url}`);