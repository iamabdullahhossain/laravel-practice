# Task Management API

A secure, restful Task Management API built using Laravel 13 (PHP 8.5) and Laravel Sanctum for user authentication.

## Features

- **User Authentication**: Secure registration, login, logout, and profile endpoints via Sanctum Bearer tokens.
- **Change Password**: Dedicated secure endpoint for updating user password with verification.
- **Category Management**: Organize tasks into different categories (Full CRUD capabilities).
- **Task Management**:
  - Full CRUD operations.
  - Custom status workflows (`todo`, `in_progress`, `completed`, `due`).
  - Single-field status updates (`PATCH /api/tasks/{id}/status`).
  - Nullable deadline dates and times (`due_date` and `due_time`).
  - **Auto-Expiry**: Expired tasks automatically transition to `due` status upon retrieval if they are not already marked `completed`.
- **Task Statistics**: Get count of tasks grouped by status (`GET /api/tasks/stats`).
- **Pest PHP Testing**: Fully covered feature tests covering all APIs and status transition rules.

---

## Prerequisites

- **PHP** >= 8.5
- **Composer**
- **SQLite** (or MySQL)

---

## Setup Instructions

### 1. Clone & Install Dependencies
```bash
composer install
```

### 2. Configure Environment
Copy the `.env.example` file to `.env`:
```bash
cp .env.example .env
```
Ensure your database driver is configured. By default, SQLite is used:
```env
DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Run Migrations & Seeders
Build the database tables and populate them with dummy data (1 test user, 5 categories, and 10 tasks):
```bash
php artisan migrate:fresh --seed
```
*Default seeded user login credentials:*
- **Username**: `iamabdullahhossain`
- **Password**: `12345678`

### 5. Run Server
Start the local development server:
```bash
php artisan serve
```
The API will be accessible at: `http://127.0.0.1:8000/api`

---

## API Documentation

### Public Endpoints
| Endpoint | Method | Payload / Request Body | Description |
| :--- | :---: | :--- | :--- |
| `/api/register` | `POST` | `name`, `username`, `password` | Register a new user |
| `/api/login` | `POST` | `username`, `password` | Authenticate user and get Bearer Token |

### Protected Endpoints (Requires `Authorization: Bearer {token}`)
| Endpoint | Method | Payload / Request Body | Description |
| :--- | :---: | :--- | :--- |
| `/api/user` | `GET` | *None* | Get authenticated user profile |
| `/api/change-password` | `POST` | `old_password`, `new_password`, `confirm_password` | Securely change user password |
| `/api/logout` | `POST` | *None* | Revoke current Bearer Token |
| `/api/categories` | `GET` | *None* | List all user categories |
| `/api/categories` | `POST` | `name` | Create a new category |
| `/api/categories/{id}` | `GET` | *None* | View a single category |
| `/api/categories/{id}` | `PUT` | `name` | Update a category |
| `/api/categories/{id}` | `DELETE` | *None* | Delete a category |
| `/api/tasks` | `GET` | *None* | List all user tasks |
| `/api/tasks/stats` | `GET` | *None* | Get task counts grouped by status |
| `/api/tasks` | `POST` | `category_id`, `title`, `description`, `status`, `due_date`, `due_time` | Create a new task |
| `/api/tasks/{id}` | `GET` | *None* | View a single task |
| `/api/tasks/{id}` | `PUT` | `category_id`, `title`, `description`, `status`, `due_date`, `due_time` | Update a task |
| `/api/tasks/{id}/status` | `PATCH` | `status` | Update only the status of a task |
| `/api/tasks/{id}` | `DELETE` | *None* | Delete a task |

---

## Running Tests
Run the Pest PHP test suite to verify the application:
```bash
php artisan test --compact
```

## Postman Collection
An automated Postman collection is included in the project:
👉 [task-management-api.postman_collection.json](./task-management-api.postman_collection.json)

*Note: Importing this file to Postman sets up all endpoints with dynamic token harvesting. Once you log in or register, the token will automatically populate for the remaining endpoints.*
