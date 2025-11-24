# PHP Web App Boilerplate

A simple PHP web application boilerplate that serves a static HTML page with optional database support.

## Features

- 🚀 Built-in PHP web server
- 🗄️ Optional MySQL and PostgreSQL database support
- 📁 Static file serving
- 🔍 Health check endpoint
- ⚙️ Environment variable configuration
- 🎯 Simple routing system

## Requirements

- PHP >= 7.4
- PDO extension (included by default)
- PDO MySQL driver (for MySQL support)
- PDO PostgreSQL driver (for PostgreSQL support)

## Quick Start

### Running the Application

```bash
# Using PHP directly
php -S localhost:8080 -t . index.php

# Or using Composer
composer start
```

The server will start on port 8080 by default. You can customize the port using the `PORT` environment variable:

```bash
PORT=3000 php index.php
```

### Development Mode

Run the server in development mode:

```bash
make dev
```

This starts the PHP built-in server at `http://localhost:8080`.

## Database Support

The application automatically connects to a database if the `DATABASE_URL` environment variable is set.

### Supported Databases

- **MySQL**: `mysql://user:password@host:port/database`
- **PostgreSQL**: `postgres://user:password@host:port/database` or `postgresql://user:password@host:port/database`

### Example Usage

```bash
# MySQL
DATABASE_URL="mysql://root:password@localhost:3306/mydb" php index.php

# PostgreSQL
DATABASE_URL="postgres://user:pass@localhost:5432/mydb" php index.php
```

The database connection is established on each request and can be checked via the `/health` endpoint.

## API Endpoints

### GET /

Serves the main HTML page from `web/index.html`.

### GET /health

Returns a JSON health check response with database connection status.

**Response Example:**

```json
{
    "status": "ok",
    "database": {
        "type": "MySQL",
        "status": "Connected",
        "connected": true
    }
}
```

### GET /static/*

Serves static files from the `web/` directory.

Example: `/static/styles.css` → `web/styles.css`

## Project Structure

```
.
├── index.php           # Main application file
├── web/
│   └── index.html      # Static HTML page
├── composer.json       # Composer configuration
├── Makefile           # Build and run commands
└── README.md          # This file
```

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `PORT` | Server port | `8080` |
| `DATABASE_URL` | Database connection string | (none) |

## Deploy configuration

This application is designed to work seamlessly with [DollarDeploy](https://dollardeploy.com), which handles build, deployment, and monitoring automatically.

* **Pre-start command:** sudo apt install -y php-fpm php-pgsql php-mysql

## Security Notes

- Static files are served only from the `web/` directory
- Path traversal attacks are prevented with `realpath()` checks
- Database credentials should be provided via `DATABASE_URL` environment variable
- PDO is used with prepared statements for safe database operations

## License

MIT
