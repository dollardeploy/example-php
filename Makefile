.PHONY: help install run dev clean

# Default target
help:
	@echo "Available targets:"
	@echo "  make install  - Install PHP dependencies via Composer"
	@echo "  make run      - Run the PHP web server"
	@echo "  make dev      - Run the PHP web server in development mode"
	@echo "  make clean    - Clean up generated files"

# Install dependencies
install:
	@command -v composer >/dev/null 2>&1 || { echo "Composer not found. Please install Composer first."; exit 1; }
	composer install

# Run the application
run:
	php index.php

# Run in development mode (with auto-reload)
dev:
	@echo "Starting PHP development server..."
	@echo "Server will run on http://localhost:8080"
	@echo "Press Ctrl+C to stop"
	php -S localhost:8080 -t . index.php

# Clean up
clean:
	rm -rf vendor/
	rm -f composer.lock
