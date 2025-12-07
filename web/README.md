# Workforce Contract Digitization API

A Laravel-based RESTful API for workforce contract digitization system.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Architecture](#architecture)
- [Code Standards](#code-standards)
- [Contributing](#contributing)

## ✨ Features

- **Authentication System**: Session-based authentication with login/logout
- **User Management**: Full CRUD operations for user management
- **Audit Logging**: Complete audit trail for all operations
- **Soft Deletes**: Soft delete functionality for data recovery
- **RESTful API**: Clean REST API design
- **Service Layer Architecture**: Separation of concerns with Service and Repository patterns
- **Custom Exceptions**: Proper exception handling
- **Validation**: Comprehensive input validation

## 🔧 Requirements

- PHP >= 8.2
- Composer
- Node.js >= 18.x
- Database (MySQL/PostgreSQL/SQLite)
- Laravel 12.x

## 🚀 Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd workforce-contract-digitization/web
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Start development server

```bash
php artisan serve
```

Or use the dev script:

```bash
composer run dev
```

## ⚙️ Configuration

### API Base URL

Default API base URL: `http://localhost:8000/api/v1`

### Session Configuration

The API uses session-based authentication. Make sure to include session cookies in your requests.

## 📚 API Documentation

### Authentication Endpoints

#### Login
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "Password123!",
    "remember": false
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com"
        }
    }
}
```

#### Logout
```http
POST /api/v1/logout
```

#### Get Current User
```http
GET /api/v1/me
```

### User Endpoints

#### List Users
```http
GET /api/v1/users?per_page=15&search=john
```

#### Create User
```http
POST /api/v1/users
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123!"
}
```

#### Get User
```http
GET /api/v1/users/{id}
```

#### Update User
```http
PUT /api/v1/users/{id}
Content-Type: application/json

{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

#### Delete User
```http
DELETE /api/v1/users/{id}
```

## 🧪 Testing

### Run all tests

```bash
php artisan test
```

### Run specific test suite

```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature
```

### Code coverage

```bash
php artisan test --coverage
```

## 🏗️ Architecture

### Project Structure

```
app/
├── Exceptions/          # Custom exception classes
├── Helpers/             # Helper functions
├── Http/
│   ├── Controllers/     # API controllers
│   ├── Middleware/      # HTTP middleware
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
├── Providers/           # Service providers
├── Repositories/        # Repository pattern
│   ├── Contracts/       # Repository interfaces
│   └── UserRepository.php
└── Services/            # Business logic layer
    ├── AuthService.php
    └── UserService.php
```

### Design Patterns

- **Service Layer Pattern**: Business logic separated from controllers
- **Repository Pattern**: Data access abstraction
- **Dependency Injection**: Services injected via constructor
- **Exception Handling**: Custom exceptions for better error handling

### Data Flow

```
Request → Controller → Service → Repository → Model → Database
                ↓
            Response
```

## 📝 Code Standards

### PSR Standards

- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloading Standard
- **PSR-12**: Extended Coding Style

### Laravel Best Practices

- Form Requests for validation
- Service layer for business logic
- Repository pattern for data access
- Resource controllers for RESTful APIs
- Eloquent ORM for database operations

### Code Quality Tools

- **Laravel Pint**: Code formatting
- **PHPUnit**: Testing framework
- **PHPStan**: Static analysis (recommended)

## 🔒 Security

- Password hashing using `Hash::make()`
- Session-based authentication
- Input validation via Form Requests
- CSRF protection
- SQL injection protection via Eloquent ORM

## 📊 Audit Logging

All operations are logged with:
- Action type
- User ID
- Timestamp
- Old/New values (for updates)
- IP address

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Code Review Checklist

- [ ] Code follows PSR standards
- [ ] Tests are written and passing
- [ ] Documentation is updated
- [ ] No linter errors
- [ ] Security best practices followed

## 📄 License

This project is licensed under the MIT License.

## 👥 Support

For support, email support@example.com or create an issue in the repository.

---

**Version:** 1.0.0  
**Last Updated:** 2025-12-07
