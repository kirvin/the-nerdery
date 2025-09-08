# Content Service Design

This document outlines the design for the `ContentService`, which will be responsible for managing all content-related features of the application.

## Database Schema

### `discussions` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `user_id` | `uuid` | Foreign key to `users` table |
| `title` | `text` | The title of the discussion |
| `body` | `text` | The main body of the discussion |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

### `discussion_comments` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `discussion_id` | `uuid` | Foreign key to `discussions` table |
| `user_id` | `uuid` | Foreign key to `users` table |
| `parent_comment_id` | `uuid` | Foreign key to `discussion_comments` table for threaded comments |
| `body` | `text` | The body of the comment |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

### `lists` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `user_id` | `uuid` | Foreign key to `users` table |
| `title` | `text` | The title of the list |
| `description` | `text` | An optional description of the list |
| `is_public` | `boolean` | Whether the list is public or private |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

### `list_items` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `list_id` | `uuid` | Foreign key to `lists` table |
| `user_id` | `uuid` | Foreign key to `users` table |
| `body` | `text` | The body of the list item |
| `upvotes` | `integer` | The number of upvotes the item has received |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

### `list_members` table

| Column | Type | Description |
| --- | --- | --- |
| `list_id` | `uuid` | Foreign key to `lists` table |
| `user_id` | `uuid` | Foreign key to `users` table |

### `news` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `user_id` | `uuid` | Foreign key to `users` table (author) |
| `title` | `text` | The title of the news article |
| `body` | `text` | The rich-text body of the news article |
| `created_at` | `timestamp` | |
| `updated_at` | `timestamp` | |

### `reactions` table

| Column | Type | Description |
| --- | --- | --- |
| `id` | `uuid` | Primary key |
| `user_id` | `uuid` | Foreign key to `users` table |
| `content_id` | `uuid` | The ID of the content being reacted to |
| `content_type` | `text` | The type of content being reacted to (e.g., 'discussion', 'comment', 'list_item', 'news') |
| `emoji` | `text` | The emoji used for the reaction |
| `created_at` | `timestamp` | |

## GraphQL Schema

### Types

```graphql
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
```

### Queries

```graphql
type Query {
  discussions: [Discussion!]!
  discussion(id: ID!): Discussion
  lists: [List!]!
  list(id: ID!): List
  news: [News!]!
  newsArticle(id: ID!): News
}
```

### Mutations

```graphql
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
```