# AIH Healthcare API - Quick Start Guide

## 🎉 API đã sẵn sàng hoạt động!

Server đang chạy tại: **http://127.0.0.1:8000**

## 📋 Available Endpoints

### 1. Root & Health Check
- `GET /` - API information
- `GET /api/v1/health` - Health check

### 2. Doctors
- `GET /api/v1/doctors` - List all doctors
- `GET /api/v1/doctors/{id}` - Get doctor by ID

### 3. Specialties
- `GET /api/v1/specialties` - List all specialties

### 4. Posts
- `GET /api/v1/posts` - List all posts

### 5. Services
- `GET /api/v1/services` - List all services

### 6. Packages
- `GET /api/v1/packages` - List all packages

## 🚀 How to Run

### Method 1: Using VS Code Task (Recommended)
1. Press `Ctrl+Shift+P`
2. Type "Tasks: Run Task"
3. Select "Start AIH API Server"

### Method 2: Manual Command
```bash
php -S 127.0.0.1:8000 -t public
```

### Method 3: Using Batch File
```bash
./simple-setup.bat
```

## 🔧 Current Status

✅ **Working Features:**
- PHP 8.4 server running
- JSON API responses
- CORS headers configured
- Vietnamese language support
- Basic routing system
- Health monitoring
- Error handling

⚠️ **Development Phase:**
- Currently using simple PHP routing
- Sample data (not connected to database yet)
- Laravel framework partially configured

## 🗂️ Project Structure

```
Source/
├── app/                     # Laravel application code
│   ├── Http/Controllers/    # API Controllers
│   ├── Models/             # Database models
│   └── Services/           # Business logic
├── config/                 # Configuration files
├── public/                 # Web accessible files
│   └── index.php          # Main entry point
├── routes/                 # API routes
└── vendor/                # Dependencies (minimal)
```

## 🔗 Test the API

Open these URLs in your browser:

1. **API Info:** http://127.0.0.1:8000
2. **Health Check:** http://127.0.0.1:8000/api/v1/health
3. **Doctors List:** http://127.0.0.1:8000/api/v1/doctors
4. **Single Doctor:** http://127.0.0.1:8000/api/v1/doctors/1
5. **Specialties:** http://127.0.0.1:8000/api/v1/specialties

## 🎯 Next Steps

1. **Database Connection**: Configure MySQL connection in `config/database.php`
2. **Laravel Integration**: Complete Laravel framework setup with Composer
3. **Authentication**: Implement API authentication
4. **Rate Limiting**: Add advanced rate limiting
5. **Validation**: Implement request validation
6. **Documentation**: Generate API documentation

## 📞 Support

If you need help:
1. Check the `INSTALLATION.md` file
2. Run `./fix-composer.bat` if you have Composer issues
3. Use `./docker-setup.bat` for Docker installation

---

**🏥 AIH Healthcare API v1.0.0**  
*Professional healthcare API solution*
