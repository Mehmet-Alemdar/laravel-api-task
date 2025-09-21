# Laravel Comment API

A robust Laravel 12-based REST API application featuring article management, comment system with automated moderation, and comprehensive caching strategy.

## 🏗️ Architecture Overview

### System Flow
```
Client → API Gateway → Laravel App → PostgreSQL Database
                    ↓
                Redis Cache ← Queue Worker ← ModerateCommentJob
```

**Key Components:**
- **Client**: Frontend applications, mobile apps, or API consumers
- **API**: Laravel 12 with Sanctum authentication and modern PHP 8.3+ features
- **Database**: PostgreSQL with UUID primary keys
- **Cache**: Redis for comment pagination and session storage
- **Queue**: Redis-based job processing for comment moderation
- **Worker**: Background job processor for automated content moderation

**Laravel 12 Features Used:**
- Enhanced performance with improved query optimization
- Advanced caching mechanisms
- Modern PHP 8.3+ syntax and features
- Improved queue system with better reliability

### Data Flow
1. **Comment Creation**: Client → API → Database (status: pending) → Queue Job
2. **Moderation Process**: Queue Worker → Content Analysis → Status Update → Cache Invalidation
3. **Comment Retrieval**: Client → API → Cache Check → Database (if cache miss) → Response

## 🚀 Quick Start

### Prerequisites
- Docker & Docker Compose
- Make (optional, for convenience commands)

### Docker Installation (Recommended)

1. **Clone and Setup**
   ```bash
   git clone <repository-url>
   cd laravel-api-task
   ```

2. **Start Services**
   ```bash
   make build && make up
   # Or manually: docker-compose build && docker-compose up -d
   ```

3. **Initialize Application**
   ```bash
   make key      # Generate app key
   make migrate  # Run migrations
   make seed     # Seed sample data
   ```

4. **Verify Installation**
   ```bash
   curl http://localhost:8000/api/articles
   ```

### Local Installation (PostgreSQL)

1. **Setup Environment**
   ```bash
   cp env.local.example .env
   # Edit .env with your PostgreSQL credentials
   ```

2. **Install Dependencies**
   ```bash
   composer install
   php artisan key:generate
   ```

3. **Database Setup**
   ```bash
   createdb laravel_api_local
   php artisan migrate --seed
   ```

4. **Start Services**
   ```bash
   php artisan serve              # API server
   php artisan queue:work         # Queue worker (separate terminal)
   ```

## 📋 Environment Configuration

### Docker Environment
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=laravel_api
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_password

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# Comment System
BANNED_KEYWORDS="spam,badword,offensive,inappropriate"
COMMENTS_CACHE_TTL=120
```

### Local Environment
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_DATABASE=laravel_api_local
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Cache & Queue (can use database for development)
CACHE_STORE=database
QUEUE_CONNECTION=database

# Comment System
BANNED_KEYWORDS="spam,badword,offensive"
COMMENTS_CACHE_TTL=60
```

## 🔌 API Endpoints

### Authentication
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Response
{
  "success": true,
  "data": {
    "token": "1|abcdef..."
  },
  "message": "Login successful"
}
```

### Articles
```http
# Get articles with pagination and search
GET /api/articles?page=1&per_page=10&search=laravel&from=2024-01-01&to=2024-12-31

# Response
{
  "success": true,
  "data": {
    "items": [
      {
        "id": "uuid-here",
        "title": "Article Title",
        "body": "Article content...",
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50
    }
  }
}
```

```http
# Get specific article
GET /api/articles/{id}

# Response
{
  "success": true,
  "data": {
    "id": "uuid-here",
    "title": "Article Title",
    "body": "Full article content...",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

### Comments
```http
# Get article comments (cached)
GET /api/articles/{id}/comments?page=1&per_page=10

# Response
{
  "success": true,
  "data": {
    "items": [
      {
        "id": "uuid-here",
        "article_id": "article-uuid",
        "user_id": "user-uuid",
        "content": "Great article!",
        "status": "published",
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25
    }
  }
}
```

```http
# Create comment (authenticated, rate limited)
POST /api/articles/{id}/comments
Authorization: Bearer {token}
Content-Type: application/json

{
  "content": "This is my comment on the article."
}

# Response (202 Accepted - queued for moderation)
{
  "success": true,
  "data": {
    "comment_id": "uuid-here"
  },
  "message": "Comment queued for moderation"
}
```

## ⚡ Rate Limiting & Cache Strategy

### Rate Limiting
- **Comment Creation**: Limited by `throttle:comment-post` middleware
- **Configurable**: Adjust limits in route middleware or env variables
- **Response**: HTTP 429 when limits exceeded

### Caching Strategy
```php
// Comment list caching with tags
$key = "comments:article:{$articleId}:page:{$page}";
$comments = Cache::tags(["article:{$articleId}"])->remember($key, $ttl, function() {
    return Comment::where('article_id', $articleId)->paginate();
});

// Cache invalidation on comment approval
Cache::tags(["article:{$comment->article_id}"])->flush();
```

**Cache Benefits:**
- **Performance**: Reduces database queries for popular articles
- **Scalability**: Handles high traffic with Redis clustering
- **Selective Invalidation**: Article-specific cache clearing
- **TTL Control**: Configurable cache expiration

### Comment Moderation Flow
```
1. Comment Created → Status: "pending"
2. ModerateCommentJob Queued
3. Background Processing:
   - Content analysis against banned keywords
   - Status update: "published" or "rejected"
   - Cache invalidation (if published)
4. Client sees updated comment on next request
```

## 🧪 Testing & CI

### Running Tests
```bash
# Docker environment
make test

# Local environment
php artisan test

# Specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Test Coverage
- **21 passing tests** with comprehensive scenarios
- **Feature Tests**: API endpoints, authentication, rate limiting
- **Unit Tests**: Comment moderation, cache invalidation
- **Integration Tests**: Queue processing, database operations

### CI/CD Pipeline
```bash
# Code formatting
make pint-test    # Check formatting
make pint         # Fix formatting

# Quality checks (if configured)
composer audit    # Security vulnerabilities
phpstan analyse   # Static analysis
```

## 🛠️ Development Commands

### Docker Commands
```bash
make help         # Show all available commands
make up           # Start all services
make down         # Stop all services
make logs         # View logs
make shell        # Access app container
make db-shell     # Access database
make queue-shell  # Access queue worker
```

### Application Commands
```bash
make migrate      # Run migrations
make seed         # Seed database
make fresh        # Fresh migrate + seed
make cache        # Clear all caches
make optimize     # Optimize for production
make queue-work   # Manual queue processing
make queue-failed # Show failed jobs
```

## 📊 Database Schema

### Core Tables
```sql
-- Articles
CREATE TABLE articles (
    id UUID PRIMARY KEY,
    title VARCHAR NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Comments
CREATE TABLE comments (
    id UUID PRIMARY KEY,
    article_id UUID REFERENCES articles(id),
    user_id UUID REFERENCES users(id),
    content TEXT NOT NULL,
    status VARCHAR DEFAULT 'pending', -- pending, published, rejected
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Users
CREATE TABLE users (
    id UUID PRIMARY KEY,
    name VARCHAR NOT NULL,
    email VARCHAR UNIQUE NOT NULL,
    password VARCHAR NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Relationships
- **Article** → **Comments** (One-to-Many)
- **User** → **Comments** (One-to-Many)
- All models use UUID primary keys for better scalability

## 🔧 Configuration

### Comment Moderation
```php
// config/comments.php
return [
    'banned_keywords' => env('BANNED_KEYWORDS', 'spam,badword'),
    'cache_ttl' => env('COMMENTS_CACHE_TTL', 60),
];
```

### Queue Configuration
```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'redis'),
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```
