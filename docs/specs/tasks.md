# Implementation Plan: Proof-of-Concept (PoC)

This document outlines the sequential tasks required to implement the Proof-of-Concept (PoC) as defined in the Technical Design Specification.

## Summary of Tasks

*   `[x] Task 1: Project & Environment Setup`
*   `[x] Task 2: Database Schema Migration`
*   `[x] Task 3: UserService Implementation`
*   `[x] Task 4: Apollo Gateway Implementation`
*   `[ ] Task 5: Legacy Data Migration Script`
*   `[ ] Task 6: Web Client (Remix) Implementation`
*   `[ ] Task 7: PoC Final Validation`

---

### Task 1: Project & Environment Setup
*   **Description:** Initialize the monorepo and set up the local development environment.
*   **Sub-tasks:**
    *   1.1: Initialize a Turborepo monorepo.
    *   1.2: Create placeholder directories for the Remix web app, React Native mobile app, and backend services (`gateway`, `user-service`, `content-service`).
    *   1.3: Create a `docker-compose.yml` file to define and run a PostgreSQL container via Orbstack.
*   **Expected Outcome:** A runnable `docker-compose` environment with a PostgreSQL database ready for connections.

---

### Task 2: Database Schema Migration
*   **Description:** Create the initial database schema for the `users` table.
*   **Sub-tasks:**
    *   2.1: Set up a migration tool (e.g., `node-pg-migrate`).
    *   2.2: Write the initial migration script to create the `users` table as specified in `design.md`.
*   **Expected Outcome:** The `users` table is created successfully in the PostgreSQL database when the migration script is run.

---

### Task 3: `UserService` Implementation
*   **Description:** Develop the core `UserService` to handle user authentication and management.
*   **Sub-tasks:**
    *   3.1: Initialize a new TypeScript Apollo Server application within the `user-service` directory.
    *   3.2: Implement the GraphQL schema for the `User` type, `AuthPayload`, and the `register` and `login` mutations.
    *   3.3: Implement the business logic for the resolvers, including password hashing (using `bcrypt`) and JWT generation.
    *   3.4: Configure the service to connect to the PostgreSQL database.
*   **Expected Outcome:** A runnable, standalone GraphQL service that can register and authenticate users against the database.

---

### Task 4: Apollo Gateway Implementation
*   **Description:** Set up the Apollo Gateway to create a federated API.
*   **Sub-tasks:**
    *   4.1: Initialize a new TypeScript application for the Apollo Gateway.
    *   4.2: Configure the gateway to federate the `UserService` and the placeholder `ContentService`.
*   **Expected Outcome:** The gateway is able to introspect the underlying service schemas and expose a single, unified GraphQL endpoint.

---

### Task 5: Legacy Data Migration Script
*   **Description:** Create and test the script to migrate users from the legacy database.
*   **Sub-tasks:**
    *   5.1: Write a script (e.g., in TypeScript with `mysql2` and `pg` clients) to connect to both the legacy MySQL and new PostgreSQL databases.
    *   5.2: Implement the ETL (Extract, Transform, Load) logic to read users from the legacy `Users` table, hash their passwords appropriately for the new system, and insert them into the new `users` table.
    *   5.3: Write validation tests to ensure all users are migrated correctly.
*   **Expected Outcome:** A runnable script that successfully and verifiably migrates all user data.

---

### Task 6: Web Client (Remix) Implementation
*   **Description:** Build the client-side UI for the user registration and login PoC.
*   **Sub-tasks:**
    *   6.1: Initialize a new Remix application in the web app directory.
    *   6.2: Create the UI components for registration and login forms.
    *   6.3: Set up an Apollo Client instance to communicate with the GraphQL gateway.
    *   6.4: Connect the UI to the `register` and `login` mutations.
*   **Expected Outcome:** A user can visit the web application in a browser, register a new account, and log in successfully.

---

### Task 7: PoC Final Validation
*   **Description:** Perform an end-to-end test of the entire PoC.
*   **Sub-tasks:**
    *   7.1: Run the data migration script.
    *   7.2: Start all services (`docker-compose up`).
    *   7.3: In the web client, attempt to log in with a migrated user account.
    *   7.4: In the web client, register a completely new user.
*   **Expected Outcome:** All steps complete successfully, proving the viability of the architecture and migration plan.
