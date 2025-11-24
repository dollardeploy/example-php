<?php

/**
 * PHP Web Application Boilerplate
 *
 * A simple PHP web server that serves static HTML pages and supports
 * database connections via the DATABASE_URL environment variable.
 */

// Get port from environment or default to 8080
$port = getenv('PORT') ?: '8080';

// Database connection status
$dbConnection = null;
$dbStatus = 'Not configured';
$dbType = 'none';

/**
 * Parse DATABASE_URL and establish database connection
 */
function connectToDatabase() {
    $databaseUrl = getenv('DATABASE_URL');

    if (!$databaseUrl) {
        return ['status' => 'Not configured', 'connection' => null, 'type' => 'none'];
    }

    // Parse DATABASE_URL (format: scheme://user:pass@host:port/dbname)
    $parsedUrl = parse_url($databaseUrl);

    if (!$parsedUrl) {
        return ['status' => 'Invalid DATABASE_URL format', 'connection' => null, 'type' => 'none'];
    }

    $scheme = $parsedUrl['scheme'] ?? '';
    $host = $parsedUrl['host'] ?? 'localhost';
    $port = $parsedUrl['port'] ?? null;
    $user = $parsedUrl['user'] ?? '';
    $pass = $parsedUrl['pass'] ?? '';
    $dbname = ltrim($parsedUrl['path'] ?? '', '/');

    try {
        if ($scheme === 'postgres' || $scheme === 'postgresql') {
            // PostgreSQL connection
            $port = $port ?? 5432;
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return ['status' => 'Connected', 'connection' => $connection, 'type' => 'PostgreSQL'];

        } elseif ($scheme === 'mysql') {
            // MySQL connection
            $port = $port ?? 3306;
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return ['status' => 'Connected', 'connection' => $connection, 'type' => 'MySQL'];

        } else {
            return ['status' => "Unsupported database scheme: $scheme", 'connection' => null, 'type' => 'none'];
        }
    } catch (PDOException $e) {
        return ['status' => 'Connection failed: ' . $e->getMessage(), 'connection' => null, 'type' => 'none'];
    }
}

/**
 * Serve static files from the web directory
 */
function serveStaticFile($filePath) {
    $webRoot = __DIR__ . '/web';
    $fullPath = realpath($webRoot . $filePath);

    // Security check: ensure file is within web directory
    if ($fullPath === false || strpos($fullPath, $webRoot) !== 0) {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    if (!file_exists($fullPath) || !is_file($fullPath)) {
        http_response_code(404);
        echo "404 Not Found";
        return;
    }

    // Set appropriate content type
    $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
    $contentTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
    ];

    $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
    header("Content-Type: $contentType");

    readfile($fullPath);
}

/**
 * Handle incoming requests
 */
function handleRequest() {
    global $dbStatus, $dbType, $dbConnection;

    // Try to connect to database if DATABASE_URL is set
    $dbResult = connectToDatabase();
    $dbStatus = $dbResult['status'];
    $dbConnection = $dbResult['connection'];
    $dbType = $dbResult['type'];

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH);

    // Route: / - serve index.html
    if ($path === '/') {
        serveStaticFile('/index.html');
        return;
    }

    // Route: /health - health check endpoint
    if ($path === '/health') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'database' => [
                'type' => $dbType,
                'status' => $dbStatus,
                'connected' => $dbConnection !== null
            ]
        ], JSON_PRETTY_PRINT);
        return;
    }

    // Route: /static/* - serve static files
    if (strpos($path, '/static/') === 0) {
        $filePath = substr($path, 7); // Remove '/static' prefix
        serveStaticFile($filePath);
        return;
    }

    // 404 for all other routes
    http_response_code(404);
    echo "404 Not Found";
}

// If running via PHP built-in server
if (php_sapi_name() === 'cli-server') {
    handleRequest();
}
// If running via CLI (start server)
elseif (php_sapi_name() === 'cli') {
    echo "Starting PHP web server on port $port...\n";
    echo "Server running at http://localhost:$port\n";
    echo "Press Ctrl+C to stop\n\n";

    // Start PHP built-in web server
    $command = sprintf(
        'php -S 0.0.0.0:%s -t %s %s',
        escapeshellarg($port),
        escapeshellarg(__DIR__),
        escapeshellarg(__FILE__)
    );

    passthru($command);
}
// If running via web server (Apache/Nginx)
else {
    handleRequest();
}
