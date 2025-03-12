# Bakery Hours System

A web application for managing and displaying bakery operating hours, built with Laravel and Vue.js.

## Features

- 🕒 Real-time store status display
- 📅 Date-based availability checker
- ⚙️ Configurable opening hours
- 🔄 Alternate week scheduling
- 👥 Admin interface for managing hours
- 📱 Responsive design

## Requirements

### Option 1: Docker
- Docker
- Docker Compose
- Git

### Option 2: XAMPP
- XAMPP (PHP 8.2 or higher)
- Composer
- Node.js (v16 or higher)
- npm
- Git

## Installation

### Option 1: Using Docker

1. Clone the repository:
```bash
git clone https://github.com/mjdurandar/durandar-mj.git
cd durandar-mj
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Start the Docker containers:
```bash
docker-compose up -d
```

4. Install dependencies:
```bash
docker-compose exec app composer install
docker-compose exec app npm install
```

5. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

6. Run migrations and seed the database:
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

7. Build frontend assets:
```bash
docker-compose exec app npm run build
```

The application will be available at `http://localhost:8000`

### Option 2: Using XAMPP

1. Clone the repository into your XAMPP htdocs folder:
```bash
cd C:/xampp/htdocs
git clone https://github.com/mjdurandar/durandar-mj.git
cd durandar-mj
```

2. Copy the environment file:
```bash
cp .env.example .env
```

3. Update .env file with your database settings:
```
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=bakery_hours_system
DB_USERNAME=bakery
DB_PASSWORD=bakery
```

4. Install dependencies:
```bash
composer install
npm install
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Create database and run migrations:
```bash
# Create database using phpMyAdmin or MySQL command
php artisan migrate:fresh --seed
```

7. Build frontend assets:
```bash
npm run build
```

8. Start the development server:
```bash
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`

## Default Admin Credentials

After seeding the database, you can log in with these credentials:
- Email: `admin@example.com`
- Password: `password123`

## Database Structure

The application uses the following main tables:
- `store_hours_config`: Stores the operating hours configuration
- `users`: Stores admin user information

## Testing

To run the test suite:

### Docker:
```bash
docker-compose exec app php artisan test
```

### XAMPP:
```bash
php artisan test
```

## Development

To start the development server with hot-reloading:

### Docker:
```bash
docker-compose exec app npm run dev
```

### XAMPP:
```bash
npm run dev
```

## Store Hours Configuration

The default store hours are:
- Monday: 08:00 - 16:00 (Lunch: 12:00 - 12:45)
- Wednesday: 08:00 - 16:00 (Lunch: 12:00 - 12:45)
- Friday: 08:00 - 16:00 (Lunch: 12:00 - 12:45)
- Saturday: 08:00 - 16:00 (Alternate weeks only)

These can be modified through the admin interface at `/admin/store-hours`

## API Endpoints

### Public Endpoints
- `GET /api/store-hours/status` - Get current store status
- `GET /api/store-hours/check-date/{date}` - Check status for specific date
- `GET /api/store-hours/schedule` - Get weekly schedule

### Admin Endpoints
- `GET /admin/api/store-hours` - List store hours configuration
- `POST /admin/api/store-hours/bulk-update` - Update store hours
