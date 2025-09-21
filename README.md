# Laravel API Task

A Laravel-based REST API application with article management, comment system, and moderation features.

## Features

- **Authentication**: User registration and login with Laravel Sanctum
- **Articles**: CRUD operations for articles with author relationship
- **Comments**: Comment system with moderation queue
- **Queue Processing**: Background job processing for comment moderation
- **API Documentation**: RESTful API endpoints
- **Docker Support**: Complete containerization with Docker Compose

## Tech Stack

- **Backend**: Laravel 12, PHP 8.3
- **Database**: PostgreSQL 15
- **Cache/Sessions**: Redis 7
- **Queue**: Redis-based job queue
- **Containerization**: Docker & Docker Compose
- **Code Quality**: Laravel Pint
- **CI/CD**: GitHub Actions

## Quick Start

### Prerequisites

- Docker and Docker Compose
- Make (optional, for using Makefile commands)

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-api-task
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Build and start containers**
   ```bash
   make build
   make up
   ```
   
   Or manually:
   ```bash
   docker-compose build
   docker-compose up -d
   ```

4. **Install dependencies and setup database**
   ```bash
   make install
   make key
   make migrate
   make seed
   ```

5. **Access the application**
   - API: http://localhost:8000
   - Database: localhost:5432
   - Redis: localhost:6379

## Development Commands

Use the provided Makefile for common development tasks:

```bash
make help          # Show all available commands
make up            # Start the application
make down          # Stop the application
make logs          # View application logs
make shell         # Access application container
make test          # Run tests
make pint          # Format code with Laravel Pint
make migrate       # Run database migrations
make seed          # Seed the database
make cache         # Clear application cache
make optimize      # Optimize application
```

## Docker Services

The application uses the following Docker services:

- **app**: Laravel application with nginx + php-fpm
- **queue-worker**: Background job processor
- **db**: PostgreSQL 15 database
- **redis**: Redis 7 for cache and sessions

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Articles
- `GET /api/articles` - List articles
- `POST /api/articles` - Create article (authenticated)
- `GET /api/articles/{id}` - Show article
- `PUT /api/articles/{id}` - Update article (authenticated, author only)
- `DELETE /api/articles/{id}` - Delete article (authenticated, author only)

### Comments
- `GET /api/articles/{id}/comments` - List article comments
- `POST /api/articles/{id}/comments` - Create comment
- `PUT /api/comments/{id}` - Update comment (authenticated, author only)
- `DELETE /api/comments/{id}` - Delete comment (authenticated, author only)

## Testing

Run the test suite:

```bash
make test
```

Or manually:
```bash
docker-compose exec app php artisan test
```

## Code Quality

This project uses Laravel Pint for code formatting:

```bash
make pint          # Format code
make pint-test     # Check formatting without changes
```

## CI/CD

The project includes GitHub Actions workflow for:

- **Testing**: PHPUnit tests with PostgreSQL and Redis
- **Code Quality**: Laravel Pint formatting checks
- **Security**: Composer audit for vulnerabilities
- **Coverage**: Code coverage reporting

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code quality checks
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
