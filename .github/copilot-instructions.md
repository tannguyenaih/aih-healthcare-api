# Copilot Instructions

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Project Overview
This is a Laravel 8+ API project for healthcare data management with the following characteristics:

### Architecture Principles
- **Security First**: Use Laravel Sanctum for API authentication, input validation, and rate limiting
- **Lightweight & Maintainable**: Follow SOLID principles, use Repository pattern, and keep controllers thin
- **Performance Optimized**: Use eager loading, database indexing, and response caching where appropriate

### Database Structure
This API manages healthcare data including:
- Doctors (with specialties and employee IDs)
- Medical specialties 
- Blog posts (multilingual)
- Service packages
- Individual services

### API Endpoints Pattern
Each module should implement these standard endpoints:
- `GET /api/{module}` - GetList (with pagination)
- `GET /api/{module}/{id}` - GetOneByID
- `GET /api/{module}/filter` - Filter (with query parameters)
- `GET /api/{module}/parent/{parentId}` - GetByParentId (where applicable)

### Code Standards
- Use Laravel 8+ features including typed properties and union types
- Implement proper error handling with JSON responses
- Use Laravel's built-in validation rules
- Follow PSR-12 coding standards
- Include comprehensive API documentation
- Implement health check monitoring endpoints

### Security Requirements
- API rate limiting (60 requests per minute)
- Input sanitization and validation
- CORS configuration for production
- SQL injection prevention through Eloquent ORM
- XSS protection headers
