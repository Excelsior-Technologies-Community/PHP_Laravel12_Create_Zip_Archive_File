# PHP_Laravel12_Create_Zip_Archive_File

A comprehensive Laravel 12 application for creating ZIP archive files using multiple approaches, including web UI, REST APIs, and Artisan CLI commands. This project is designed for beginners, interviews, and real-world use cases where file compression and downloads are required.

---

## Overview

This project demonstrates how to:

* Create ZIP archives from existing files
* Upload multiple files and compress them
* Compress entire directories
* Automatically download generated ZIP files
* Manage temporary files safely
* Use RESTful routes and Artisan commands

The implementation uses PHP's built-in ZipArchive class and follows Laravel 12 best practices.

---

## Features

* Create ZIP archives from existing storage files
* Upload and compress multiple files instantly
* Compress full directories
* Automatic ZIP download after creation
* Temporary file cleanup
* RESTful endpoints
* Artisan CLI command support
* Simple Blade-based UI
* Secure file validation

---

## Requirements

* PHP 8.2 or higher
* Laravel 12
* Composer
* ZIP extension enabled in PHP
* MySQL / PostgreSQL / SQLite (optional for demo)

---

## Installation

### Step 1: Clone Repository and Install Dependencies

```bash
git clone https://github.com/yourusername/laravel-zip-archive.git
cd laravel-zip-archive
composer install
npm install
```

### Step 2: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Update database configuration in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_zip
DB_USERNAME=root
DB_PASSWORD=
```

### Step 3: Storage Setup

```bash
mkdir -p storage/app/public/files
mkdir -p storage/app/temp
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

### Step 4: Database Setup

```bash
php artisan migrate
php artisan db:seed --class=SampleFilesSeeder
```

---

## Running the Application

```bash
php artisan serve
```

Open in browser:

```
http://localhost:8000/zip
```
---
## Screenshot
### http://localhost:8000/zip
<img width="1585" height="967" alt="image" src="https://github.com/user-attachments/assets/d694d015-1f7d-4b6a-839b-5056a3f147ed" />

<img width="1680" height="967" alt="image" src="https://github.com/user-attachments/assets/2430b52c-3ad2-44b9-bbf4-26249a08f98d" />


---

## ZIP Creation Methods

### 1. Create ZIP from Existing Files

* Select files from storage
* Supports multiple selection
* ZIP is generated and downloaded automatically

### 2. Upload Files and Create ZIP

* Upload multiple files
* Maximum file size: 10MB per file
* Files are compressed and downloaded

### 3. Create ZIP from Directory

* Compress entire directory
* Downloads ZIP automatically

---

## API Endpoints

| Method | Endpoint                   | Description             |
| ------ | -------------------------- | ----------------------- |
| GET    | /zip                       | ZIP creation UI         |
| POST   | /zip/create-from-files     | ZIP from selected files |
| POST   | /zip/create-from-upload    | ZIP from uploaded files |
| GET    | /zip/create-from-directory | ZIP from directory      |
| GET    | /zip/list-files            | List files (JSON)       |

---

## Artisan Commands

```bash
php artisan zip:create
php artisan zip:create public/uploads
php artisan zip:create --name=my-archive.zip
php artisan zip:create public/documents --name=documents.zip
```

---

## Project Structure

```
laravel-zip-archive/
├── app/
│   ├── Console/Commands/CreateZipCommand.php
│   ├── Http/Controllers/ZipController.php
│   └── Models/
├── database/
│   ├── migrations/
│   └── seeders/SampleFilesSeeder.php
├── resources/views/zip/index.blade.php
├── routes/web.php
├── storage/app/public/files
├── storage/app/temp
└── tests/
```

---

## PHP Configuration

Ensure ZIP extension and limits in `php.ini`:

```ini
extension=zip
upload_max_filesize=10M
post_max_size=12M
memory_limit=256M
max_execution_time=300
```

---

## Security Considerations

* Validate file types and sizes
* Sanitize file names
* Prevent directory traversal
* Restrict system directory access
* Apply rate limiting if required

---

## Testing

Run all tests:

```bash
php artisan test
```

---

## Troubleshooting

### ZIP Extension Not Enabled

```
Class 'ZipArchive' not found
```

Enable ZIP extension in `php.ini`.

### Permission Errors

```bash
chmod -R 775 storage
chown -R www-data:www-data storage
```

### File Size Issues

Increase limits in `php.ini`:

```ini
upload_max_filesize=50M
post_max_size=55M
```

---

## Performance Optimization

* Use queues for large ZIP files
* Stream files instead of loading into memory
* Enable OPcache
* Implement chunk uploads

---

## Deployment

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Disable debug mode:

```env
APP_ENV=production
APP_DEBUG=false
```

---

## Roadmap

* Password-protected ZIP files
* ZIP extraction feature
* Progress bar for large files
* Cloud storage integration
* Email notifications
* Dark mode UI

---

## Conclusion

This project provides a complete reference for ZIP file creation in Laravel 12 using UI, APIs, and CLI. It is suitable for learning, interviews, and production-ready use cases.
