# AIH Healthcare Data API

A robust Laravel 8+ REST API for managing healthcare data including doctors, specialties, posts, service packages, and medical services.

## Features

- **🔐 Security First**: Rate limiting, input validation, CORS protection, and security headers
- **⚡ High Performance**: Optimized queries, eager loading, and efficient pagination
- **🌐 Multilingual Support**: Vietnamese and English language support
- **📊 Health Monitoring**: Built-in health checks and metrics endpoints
- **🔧 Easy Maintenance**: Clean architecture with service layers and repository patterns

## API Modules

### 1. Doctors Module
- Get doctors list with specialties and employee information
- Filter by specialty, status, search terms
- Multilingual support for doctor profiles

### 2. Specialties Module
- Medical specialties and departments
- Filter and search capabilities
- Integration with doctor and service data

### 3. Posts Module
- Blog posts and news articles
- Multilingual content management
- Search and filtering by status

### 4. Service Packages Module
- Medical service packages by specialty
- Detailed service breakdowns
- Category-based organization

### 5. Services Module
- Individual medical services
- Detailed service components
- Package relationships

### 6. Single Services Module
- Standalone medical services
- Category-based filtering
- Pricing information

## Endpoints

### Health Monitoring
```
GET /api/health/status          # API health status
GET /api/health/metrics         # Detailed metrics
GET /api/health/ping           # Simple ping endpoint
```

### Doctors
```
GET /api/v1/doctors                    # List all doctors
GET /api/v1/doctors/{id}              # Get doctor by ID
GET /api/v1/doctors/filter            # Filter doctors
GET /api/v1/doctors/specialty/{id}    # Get doctors by specialty
```

### Specialties
```
GET /api/v1/specialties               # List all specialties
GET /api/v1/specialties/{id}         # Get specialty by ID
GET /api/v1/specialties/filter       # Filter specialties
```

### Posts
```
GET /api/v1/posts                    # List all posts
GET /api/v1/posts/{id}              # Get post by ID
GET /api/v1/posts/filter            # Filter posts
```

### Service Packages
```
GET /api/v1/service-packages              # List all packages
GET /api/v1/service-packages/{id}         # Get package by ID
GET /api/v1/service-packages/filter      # Filter packages
GET /api/v1/service-packages/category/{id} # Get packages by category
```

### Services
```
GET /api/v1/services                     # List all services
GET /api/v1/services/{id}               # Get service by ID
GET /api/v1/services/filter             # Filter services
GET /api/v1/services/package/{id}       # Get services by package
GET /api/v1/services/{id}/individual    # Get individual service details
```

### Single Services
```
GET /api/v1/single-services              # List all single services
GET /api/v1/single-services/{id}         # Get single service by ID
GET /api/v1/single-services/filter      # Filter single services
GET /api/v1/single-services/category/{id} # Get single services by category
```

## Query Parameters

### Pagination
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

### Language Support
- `languages`: Comma-separated language codes (default: "en_us,vi")

### Filtering
- `search`: Search term for name/description
- `status`: Filter by status (published, draft, etc.)
- `sort_by`: Sort field (name, created_at, etc.)
- `sort_direction`: Sort direction (asc, desc)

### Module-specific Filters
- **Doctors**: `specialty_id`
- **Service Packages**: `category_id`
- **Services**: `package_id`
- **Single Services**: `category_id`

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [...],
    "pagination": {
        "current_page": 1,
        "total_pages": 10,
        "per_page": 15,
        "total_items": 150,
        "has_next_page": true,
        "has_previous_page": false
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {...}
}
```

## Installation

### Requirements
- PHP 8.1+
- MySQL 5.7+
- Composer
- Laravel 10+

### Quick Setup Options

#### Option 1: Simple Setup (Recommended - No external dependencies)
Run as Administrator:
```bash
simple-setup.bat
```
This downloads PHP + Composer directly and sets up everything automatically.

#### Option 2: Chocolatey Setup (Package manager approach)
```bash
setup.bat
```
Uses Chocolatey to install PHP, Composer, and Git.

#### Option 3: Docker Setup (No PHP installation needed)
```bash
docker-setup.bat
```
Runs the API in Docker containers.

#### Option 4: Test and Run (If already installed)
```bash
test-and-run.bat
```
Tests the installation and starts the Laravel server.

### Troubleshooting Common Errors

**Error: `php is not recognized`**
- Solution: Run `simple-setup.bat` as Administrator

**Error: `composer is not recognized`** 
- Solution: Run `simple-setup.bat` as Administrator

**Error: `vendor/autoload.php not found`**
- Solution: Run `composer install` or use `test-and-run.bat`

**Error: `No application encryption key`**
- Solution: Run `php artisan key:generate` or use `test-and-run.bat`
1. **Install PHP and Composer**
   - Download PHP 8.1+ from https://windows.php.net/download/
   - Download Composer from https://getcomposer.org/download/
   - Add both to your PATH

2. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd aih-healthcare-api
   ```

3. **Install dependencies**
   ```bash
   composer install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   ```
   
   Update `.env` with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aih_healthcare
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Start the Server**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```

### Troubleshooting Installation

If you get **"php is not recognized"** error:
1. See `INSTALLATION.md` for detailed PHP installation guide
2. Use the automatic setup script: `setup.bat`
3. Or use Docker: `docker-setup.bat`

## Deployment on CyberPanel

### Quick Deployment
1. **Upload all files** to your domain's directory on CyberPanel
2. **Set document root** to point to the `public` folder
3. **Configure database** settings in `.env`
4. **Run setup commands**:
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan config:cache
   ```

### Detailed Deployment Guide
See `DEPLOYMENT.md` for complete step-by-step deployment instructions including:
- Server requirements and setup
- SSL certificate configuration
- Performance optimization
- Security hardening
- Troubleshooting guide

### 1. Upload Files
- Upload all files to your domain's public_html directory
- Ensure the `public` folder contents are in the web root

### 2. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Configure Web Server
Create `.htaccess` in public directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### 4. Environment Configuration
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure your production database settings

### 5. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Security Features

- **Rate Limiting**: 60 requests per minute per IP
- **CORS Protection**: Configurable cross-origin settings
- **Input Validation**: Comprehensive request validation
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Protection**: Security headers and input sanitization
- **Error Handling**: Secure error responses without sensitive data

## Performance Optimizations

- **Database Indexing**: Optimized queries with proper indexes
- **Eager Loading**: Reduce N+1 query problems
- **Response Caching**: Configurable caching for static data
- **Pagination**: Efficient data pagination
- **Query Optimization**: Optimized database queries based on provided SQL

## Monitoring and Health Checks

The API includes comprehensive health monitoring:

- **Database Connectivity**: Checks MySQL connection
- **Cache System**: Validates cache functionality
- **Disk Space**: Monitors storage usage
- **Memory Usage**: Tracks memory consumption
- **System Metrics**: PHP and Laravel version info

Access health endpoints:
- Health Status: `GET /api/health/status`
- Detailed Metrics: `GET /api/health/metrics`
- Simple Ping: `GET /api/health/ping`

## Database Schema

The API works with the following main tables:
- `doctors` - Doctor information
- `doctors_categories` - Medical specialties
- `doctors_post_categories` - Doctor-specialty relationships
- `posts` - Blog posts and articles
- `doctor_service_packages` - Service packages
- `doctor_services` - Individual services
- `doctor_individual_services` - Detailed service components
- `doctor_single_service` - Standalone services
- `language_meta` - Multilingual support
- `meta_boxes` - Additional metadata

## API Documentation

For detailed API documentation with request/response examples, visit:
`http://your-domain.com/api/documentation`

## Support

For technical support or questions:
- Email: support@aih.com.vn
- Documentation: [API Docs](http://your-domain.com/api/documentation)

## License

This project is proprietary software developed for AIH Healthcare.
