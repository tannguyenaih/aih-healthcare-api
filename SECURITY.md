# AIH Healthcare API - Security Features

## 🔒 Security Architecture

### 1. **Secure Entry Point**
- File `public/index.php` được refactor hoàn toàn
- Sử dụng Router pattern thay vì if-else trực tiếp
- Tách biệt logic thành Controllers và Services

### 2. **Security Headers**
```php
X-Content-Type-Options: nosniff
X-Frame-Options: DENY  
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'
```

### 3. **Middleware Security Stack**
- **Rate Limiting**: 60 requests/minute per IP
- **Input Validation**: SQL injection protection
- **CORS Headers**: Proper cross-origin configuration
- **Security Headers**: XSS, Clickjacking protection

### 4. **Input Sanitization**
```php
// Tự động sanitize tất cả inputs
$_GET = array_map('htmlspecialchars', $_GET);
$_POST = array_map('htmlspecialchars', $_POST);

// Phát hiện SQL injection patterns
$dangerous_patterns = [
    '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
    '/[\'";]/',
    '/--/',
    '/\/\*/',
    '/\*\//'
];
```

### 5. **Database Security**
- **Prepared Statements**: Tự động escape parameters
- **Connection Fallback**: MySQLi fallback nếu PDO không có
- **Error Handling**: Không expose database errors
- **Sample Data Fallback**: Graceful degradation

### 6. **Route Protection**
```php
// Mỗi endpoint được bảo vệ bởi middleware
$router->get('api/v1/doctors', $handler, [
    'security',        // Security headers
    'rate_limit',      // Rate limiting  
    'input_validation' // Input sanitization
]);
```

## 🛡️ Security Features

### **Rate Limiting**
- **Limit**: 60 requests per minute per IP
- **Storage**: File-based caching
- **Response**: 429 Too Many Requests khi vượt limit

### **SQL Injection Protection**
- **Prepared Statements**: Sử dụng parameter binding
- **Pattern Detection**: Phát hiện SQL injection attempts
- **Input Sanitization**: htmlspecialchars() cho tất cả inputs

### **XSS Protection**
- **Content-Type**: Explicit JSON content type
- **X-XSS-Protection**: Browser XSS filter enabled
- **Output Encoding**: JSON_UNESCAPED_UNICODE

### **CSRF Protection**
- **SameSite Cookies**: Nếu sử dụng session
- **CORS Headers**: Controlled cross-origin access

### **Error Handling**
- **Production Mode**: Không expose stack traces
- **Debug Mode**: Detailed errors khi APP_DEBUG=true
- **Logging**: Error logging trong storage/logs

## 📁 Architecture Security

### **Separation of Concerns**
```
public/index.php          # Entry point only
app/Router.php            # Routing logic
app/Middleware/           # Security middleware
app/Http/Controllers/     # Business logic
app/Services/             # Data access
```

### **Controller Pattern**
```php
// Thay vì logic trực tiếp trong index.php
if ($path === 'doctors') {
    // Direct database query - KHÔNG AN TOÀN
}

// Sử dụng Controller
$router->get('api/v1/doctors', function() {
    $controller = new DoctorController(new DoctorService());
    return $controller->index($_GET);
}, ['security', 'rate_limit', 'input_validation']);
```

### **Database Abstraction**
```php
// Database class handle connection security
class Database {
    // PDO với prepared statements
    // MySQLi fallback
    // Error handling
    // Sample data fallback
}
```

## ⚡ Performance Security

### **Response Time Monitoring**
- Mỗi response có `response_time` field
- Monitoring performance để phát hiện attacks

### **Memory Management**
- Giới hạn pagination (max 100 records)
- Efficient query patterns
- Proper error handling

### **Caching Strategy**
- Rate limiting cache
- Database connection pooling (future)

## 🔧 Configuration Security

### **Environment Variables**
```bash
APP_DEBUG=false          # Production: disable debug
DB_PASSWORD=***          # Strong password
API_RATE_LIMIT=60       # Configurable rate limit
```

### **File Permissions**
```bash
.env                    # 600 (read-write owner only)
storage/                # 755 (writable)
public/                 # 755 (web accessible)
```

## 📊 Security Monitoring

### **Health Check Enhanced**
```json
{
    "success": true,
    "message": "API is healthy",
    "database": {
        "connected": true,
        "connection_type": "MySQLi"
    },
    "response_time": "15.2ms"
}
```

### **Database Test Endpoint**
- `/api/v1/test-db` - Kiểm tra database connection
- Hiển thị connection info an toàn
- Test query để verify connectivity

## 🚀 Production Recommendations

### **Web Server Configuration**
```apache
# Apache .htaccess
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

# Nginx
location ~ /\.env {
    deny all;
}
```

### **SSL/HTTPS**
- Sử dụng HTTPS trong production
- HSTS headers
- Secure cookie flags

### **Additional Security**
- WAF (Web Application Firewall)
- DDoS protection
- Regular security audits
- Database backup encryption

---

**✅ API hiện tại đã được bảo mật với:**
- Router-based architecture
- Multiple security middleware layers  
- Input validation và sanitization
- Rate limiting
- Proper error handling
- Database security với prepared statements
