# Technology Stack

## Source Code Management

Use github for version control.  After each set of changes, make sure that changes are committed with descriptive messages.  After each notable feature or refactor is completed, create a branch and Github Pull Request for that set of changes.

## Languages & Frameworks

- **TypeScript**: API services with Apollo server, shared type definitions
- **React & Remix**: Front-end web application
- **React Native**: iOS mobile application

## Core Technologies

### TypeScript Stack
- **Apollo Gateway**: GraphQL federated gateway and orchestration
- **Remix**: Full-stack React framework for web applications
- **LogLayer**: Structured logging for all TypeScript services (replaces console.log/error/warn)

### Infrastructure
- **PostgreSQL**: Primary relational database
- **AWS**: Cloud infrastructure and deployment
- **Orbstack**: Container runtime (prefer over Docker)

## Development Environment

### Container Strategy
- Use Docker compose for local development
- Container orchestration for all services
- Environment-specific configurations

### Database Setup
- PostgreSQL
- Migration scripts for schema management

### Logging Strategy
- **LogLayer**: Use LogLayer for all structured logging in TypeScript services
- **No console.log**: Replace all console.log, console.error, console.warn with LogLayer
- **Structured data**: Include contextual information (requestId, userId, operation, etc.)
- **Log levels**: Use appropriate levels (debug, info, warn, error, fatal)
- **Performance logging**: Log database queries, API calls, and processing times
- **Error context**: Include stack traces and relevant context for errors

### Testing Strategy
- **Required**: Comprehensive tests for Python data processing and analysis components
- **Optional**: Front-end tests only when explicitly requested
- Unit tests for computer vision accuracy
- Integration tests for spatial operations
- Performance tests for batch processing


### Deployment
- Use AWS ECS for production deployment
- Auto-scaling based on queue depth and resource usage
- CloudFront CDN for static assets and caching