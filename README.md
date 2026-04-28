# Temporia – Smart Diary Calendar

A full-stack calendar-based diary application built with **Laravel 11** + **Vue 3** + **Tailwind CSS**.

---

## Project Structure

```
temporia/
├── temporia-backend/     # Laravel 11 API
└── temporia-frontend/    # Vue 3 + Tailwind CSS
```

---

## Backend Setup (Laravel)

### Requirements
- PHP 8.2+
- Composer
- MySQL 8+

### Steps

```bash
cd temporia-backend

# 1. Install dependencies
composer install

# 2. Copy env and generate key
cp .env.example .env
php artisan key:generate

# 3. Configure your database in .env
#    DB_DATABASE=temporia
#    DB_USERNAME=root
#    DB_PASSWORD=your_password

# 4. Run migrations
php artisan migrate

# 5. Start the server
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

## Frontend Setup (Vue 3)

### Requirements
- Node.js 18+
- npm

### Steps

```bash
cd temporia-frontend

# 1. Install dependencies
npm install

# 2. Configure API URL in .env (default is already set)
#    VITE_API_URL=http://localhost:8000/api

# 3. Start dev server
npm run dev
```

The app will be available at `http://localhost:5173`.

---

## API Endpoints

| Method | Endpoint                  | Auth | Description              |
|--------|---------------------------|------|--------------------------|
| POST   | /api/register             | No   | Register new user        |
| POST   | /api/login                | No   | Login, returns token     |
| POST   | /api/logout               | Yes  | Revoke current token     |
| GET    | /api/me                   | Yes  | Get authenticated user   |
| GET    | /api/diary-entries        | Yes  | List entries (filterable)|
| POST   | /api/diary-entries        | Yes  | Create/update entry      |
| GET    | /api/diary-entries/{id}   | Yes  | Get single entry         |
| PUT    | /api/diary-entries/{id}   | Yes  | Update entry             |
| DELETE | /api/diary-entries/{id}   | Yes  | Delete entry             |
| GET    | /api/notes                | Yes  | List notes (filterable)  |
| POST   | /api/notes                | Yes  | Create note              |
| GET    | /api/notes/{id}           | Yes  | Get single note          |
| PUT    | /api/notes/{id}           | Yes  | Update note              |
| DELETE | /api/notes/{id}           | Yes  | Delete note              |

### Query Parameters
- `GET /api/diary-entries?year=2024&month=6` — filter by month
- `GET /api/notes?date=2024-06-15` — filter by specific date

---

## Features

- **Authentication** — Register, login, logout via Laravel Sanctum (token-based)
- **Calendar View** — Monthly grid with dot indicators for entries and notes
- **Diary Entries** — One entry per day with title, content, and mood tracking
- **Notes** — Multiple color-coded notes per day (indigo, rose, amber, emerald)
- **Isolated Data** — Each user only sees their own entries and notes
- **Responsive UI** — Works on desktop and mobile

## Mood Options
`happy` 😊 · `sad` 😢 · `neutral` 😐 · `excited` 🤩 · `anxious` 😰

## Note Colors
`indigo` · `rose` · `amber` · `emerald`
