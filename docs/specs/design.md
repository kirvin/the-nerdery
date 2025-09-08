# Technical Design Specification

This document outlines the technical design for the social media application. It is based on the requirements in `requirements.md` and the technology stack defined in `../steering/tech.md`.

## 1. Proof-of-Concept (PoC) Design

Based on the 83% confidence score from the ANALYZE phase, the initial development will focus on a Proof-of-Concept (PoC) to address the highest-risk areas before proceeding with full feature implementation.

### PoC Goals

1.  **Validate Data Migration:** Successfully migrate user data (usernames, emails, hashed passwords) from the legacy MySQL database to the new PostgreSQL database.
2.  **Validate Federated Architecture:** Implement a federated GraphQL API using Apollo Gateway with at least two underlying services (`UserService` and `ContentService`) to prove the viability of the architecture.
3.  **Validate Core End-to-End Functionality:** Implement a single, critical user flow: user registration and login, from a client application through the gateway to the database and back.

## 2. High-Level Architecture

The system will be composed of the following components, as specified in `tech.md`:

*   **Clients:**
    *   A **Remix (React)** web application.
    *   A **React Native** iOS application.
*   **API Gateway:**
    *   An **Apollo Gateway** that orchestrates requests to the backend services.
*   **Backend Services (Federated):**
    *   A collection of independent TypeScript microservices running Apollo Server. For the PoC, we will create a `UserService` and a placeholder `ContentService`.
*   **Database:**
    *   A **PostgreSQL** database to serve as the single source of truth for all data.
*   **Infrastructure:**
    *   All services will be containerized and run locally via **Orbstack** and **Docker Compose**.
    *   Deployment will be to **AWS ECS**.

(Note: Detailed interaction diagrams will be added as the design is refined.)

## 3. Data Model (PoC)

The PoC will focus on the `users` table in the PostgreSQL database. The schema will be managed by migration scripts.

**Table: `users`**

| Column Name       | Data Type             | Constraints                               |
| ----------------- | --------------------- | ----------------------------------------- |
| `id`              | `UUID`                | Primary Key, Default: `gen_random_uuid()` |
| `username`        | `VARCHAR(255)`        | Not Null, Unique                          |
| `email`           | `VARCHAR(255)`        | Not Null, Unique                          |
| `password_hash`   | `VARCHAR(255)`        | Not Null                                  |
| `cool_points`     | `INTEGER`             | Not Null, Default: 0                      |
| `created_at`      | `TIMESTAMPTZ`         | Not Null, Default: `now()`                |
| `updated_at`      | `TIMESTAMPTZ`         | Not Null, Default: `now()`                |

## 4. API Schema (GraphQL - PoC)

The following GraphQL schema will be implemented by the `UserService` to support the PoC.

```graphql
# Federated User Type
type User @key(fields: "id") {
  id: ID!
  username: String!
  email: String!
  cool_points: Int!
}

# Queries
type Query {
  # Fetches a user by their ID (for federation)
  _entities(representations: [_Any!]!): [_Entity]!

  # Fetches the currently logged-in user
  me: User
}

# Mutations
type Mutation {
  # Registers a new user
  register(username: String!, email: String!, password: String!): AuthPayload!

  # Logs in a user
  login(email: String!, password: String!): AuthPayload!
}

# Payloads
type AuthPayload {
  token: String!
  user: User!
}
```

## 5. Error Handling Strategy

The GraphQL API will use a standardized approach to error handling:

*   **Authentication Errors:** For issues like invalid credentials or missing/expired tokens, the API will return a `401 Unauthorized` status code.
*   **Authorization Errors:** If a user attempts an action they do not have permission for, the API will return a `403 Forbidden` status code.
*   **Validation Errors:** For invalid input, such as an invalid email format or a password that is too short, the API will return a `400 Bad Request` status code with a structured list of the specific validation failures.
*   **Server Errors:** For unexpected server-side issues, the API will return a generic `500 Internal Server Error`. Detailed error information and stack traces will be logged via **LogLayer** for debugging but will not be exposed to the client.

## 6. Testing Strategy (PoC)

As per `tech.md`, the testing strategy for the PoC will be:

*   **Backend (`UserService`):**
    *   Unit tests will be written for critical business logic, specifically user authentication, password hashing, and session management.
    *   Integration tests will be written to verify the `register` and `login` GraphQL mutations.
*   **Data Migration:**
    *   A dedicated set of tests will be created to verify the correctness and completeness of the user data migration script.
*   **Frontend (Remix & React Native):**
    *   Frontend testing is optional and will not be part of the PoC.
