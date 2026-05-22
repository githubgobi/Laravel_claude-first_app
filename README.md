# MyApp — Laravel 13

A Laravel 13 application with two authentication layers: **web session auth** (Blade + forms) and **OAuth API auth** (Laravel Passport).

---

## Requirements

- PHP 8.3+
- Composer 2+
- MySQL 8+
- Node.js 18+ (for Vite assets)

---

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# 3. Create the database (MySQL)
mysql -u root -e "CREATE DATABASE myapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Run all migrations (users + Passport OAuth tables)
php artisan migrate

# 5. Install Passport encryption keys and OAuth clients
php artisan passport:install --no-interaction

# 6. Install and build frontend assets
npm install
npm run build

# 7. Start the dev server
php artisan serve
```

The app will be available at `http://localhost:8000`.

---

## Authentication

### Web Auth (Blade + Sessions)

Cookie/session based authentication with Blade views.

| Route | Method | Middleware | Description |
|---|---|---|---|
| `/login` | GET | guest | Show login form |
| `/login` | POST | guest | Submit credentials |
| `/register` | GET | guest | Show registration form |
| `/register` | POST | guest | Create account & auto-login |
| `/dashboard` | GET | auth | Protected user dashboard |
| `/logout` | POST | auth | Logout & invalidate session |

**Flow:**
1. Visit `/register` to create an account — you are logged in automatically.
2. Visit `/login` to authenticate with email and password.
3. After login, you are redirected to `/dashboard`.
4. The nav bar shows **Login / Register** for guests and **username + Logout** for authenticated users.

**Controller:** `app/Http/Controllers/Web/AuthController.php`

**Views:**
```
resources/views/
├── layouts/app.blade.php      # Base layout with nav
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── dashboard.blade.php
```

---

### API Auth (Laravel Passport — OAuth 2.0)

Token-based authentication for API consumers. All responses are JSON. Attach the returned `access_token` as a `Bearer` token on protected requests.

#### Endpoints

| Method | URL | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | Public | Register a new user |
| POST | `/api/auth/login` | Public | Login and receive token |
| GET | `/api/user` | Bearer token | Get authenticated user |
| POST | `/api/auth/logout` | Bearer token | Revoke current token |

#### Register

```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful.",
  "access_token": "<token>",
  "token_type": "Bearer",
  "user": { "id": 1, "name": "John Doe", "email": "john@example.com" }
}
```

#### Protected Request

```http
GET /api/user
Authorization: Bearer <access_token>
```

**Controller:** `app/Http/Controllers/AuthController.php`

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php          # API (Passport) auth
│   └── Web/
│       └── AuthController.php      # Web (session) auth
├── Models/
│   └── User.php                    # Uses HasApiTokens + HasFactory + Notifiable
config/
├── auth.php                        # api guard → passport driver
├── passport.php                    # Passport config
database/migrations/
├── 0001_01_01_000000_create_users_table.php
├── 2026_05_21_*_create_oauth_*.php  # Passport tables
routes/
├── web.php                         # Web auth routes
└── api.php                         # API auth routes
resources/views/
├── layouts/app.blade.php
├── auth/login.blade.php
├── auth/register.blade.php
└── dashboard.blade.php
```

---

## Environment Variables

Key variables in `.env`:

```env
APP_NAME=Laravel
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapp
DB_USERNAME=root
DB_PASSWORD=
```

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
