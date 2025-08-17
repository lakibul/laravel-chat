
# Laravel Chat System Documentation

## Overview

This project is a full-featured real-time chat application built with Laravel 10 (API backend), Vue.js 3 (frontend SPA), and Pusher (WebSocket broadcasting). It supports one-to-one private messaging, typing indicators, unread message counts, and a modern, responsive UI. The system is designed for scalability, security, and extensibility.

database/migrations/
routes/

## 1. Architecture and Folder Structure

The project is organized for clear separation of backend, frontend, and real-time logic:

```
app/
├── Models/                # Eloquent models: User, Conversation, Message
├── Http/
│   └── Controllers/Api/   # API controllers: Auth, Chat, User
├── Events/                # Broadcast events: MessageSent, UserTyping

database/
├── migrations/            # Table schemas for users, conversations, messages
├── seeders/               # UserSeeder for demo/test users

resources/
├── js/
│   ├── app.js             # Vue app entry
│   ├── bootstrap.js       # Axios, Echo, Pusher setup
│   └── components/
│       └── Chat.vue       # Main chat SPA component
├── views/
│   └── chat.blade.php     # Blade view mounting Vue SPA


├── api.php                # REST API endpoints
├── channels.php           # Broadcast channel authorization
├── web.php                # Web routes (minimal)
```

**Key architectural decisions:**
- All chat logic is API-driven; the frontend is a Vue SPA mounted in a Blade view.
- Real-time events (messages, typing) are broadcast via Pusher and Laravel Echo.
- Models encapsulate relationships and business logic (e.g., finding conversations, unread counts).

## 2. Technologies and Technical Decisions

- **Backend:** Laravel 10
  - RESTful API (controllers in `app/Http/Controllers/Api`)
  - Laravel Sanctum for secure token-based authentication
  - Broadcasting with Laravel Events for real-time features
- **Frontend:** Vue.js 3 (Composition API)
  - SPA logic in `Chat.vue`, mounted via Blade
  - Real-time updates via Laravel Echo and Pusher
- **Real-time:** Pusher
  - WebSocket-based broadcasting for instant messaging and typing indicators
- **Database:** MySQL (default, can use SQLite)
  - Normalized tables for users, conversations, messages
- **Build Tool:** Vite
  - Fast asset bundling, hot reload for development
- **Styling:** Custom CSS (responsive, modern look)

**Design decisions:**
- API-first: All chat and user actions are via REST API endpoints
- Real-time: Pusher and Echo for scalable, reliable WebSocket events
- Security: Sanctum, CSRF, validation, rate limiting, hashed passwords
- Extensibility: Modular code, clear separation of backend/frontend
- Performance: Indexed DB, efficient queries, optimized asset pipeline


## 3. Features

### Core Features
- User registration, login, and logout (Sanctum-protected)
- List all users (except self)
- Start new conversations or continue existing ones
- One-to-one real-time chat (Pusher, Echo)
- Message persistence and conversation history
- Typing indicators ("user is typing...")
- Unread message counters per conversation/user
- Responsive, modern Vue.js SPA interface
- RESTful API endpoints for all actions

### Technical Features
- Secure API authentication (Laravel Sanctum)
- Real-time broadcasting (Pusher, Echo)
- Database relationships and constraints (unique conversations, foreign keys)
- Input validation and error handling (backend and frontend)
- Loading states, error messages, and UX feedback

### Bonus/Future Features (Planned)
- File/image sharing
- Group chat support
- Push notifications
- Message search, reactions, presence, encryption, admin panel


## 4. Setup & Development Workflow

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+
- MySQL or SQLite
- Pusher account (for real-time features)

### Installation Steps
1. **Clone and install dependencies**
  ```bash
  git clone <repo-url>
  cd laravel-chat
  composer install
  npm install
  ```
2. **Environment setup**
  ```bash
  cp .env.example .env
  php artisan key:generate
  ```
3. **Configure database and Pusher** in `.env`:
  ```env
  DB_CONNECTION=mysql
  DB_DATABASE=laravel_chat
  DB_USERNAME=root
  DB_PASSWORD=
  BROADCAST_DRIVER=pusher
  PUSHER_APP_ID=xxx
  PUSHER_APP_KEY=xxx
  PUSHER_APP_SECRET=xxx
  PUSHER_APP_CLUSTER=mt1
  ```
4. **Run migrations and seeders**
  ```bash
  php artisan migrate
  php artisan db:seed --class=UserSeeder
  ```
5. **Build frontend**
  ```bash
  npm run dev
  # or npm run build for production
  ```
6. **Start application**
  ```bash
  php artisan serve
  ```
  Visit: http://localhost:8000

### Usage
- Register/login (or use seeded test users)
- Start conversations and chat in real time
- See typing indicators and unread counts

### Troubleshooting
- Check `.env` for correct DB and Pusher credentials
- Run `php artisan migrate:fresh` if migrations fail
- Restart Vite dev server if frontend changes are not reflected
- Check browser console for real-time or JS errors


## 5. API Endpoints & Real-Time Events

### Main API Endpoints
- `POST   /api/register`         Register new user
- `POST   /api/login`            Login and get token
- `GET    /api/me`               Get current user info
- `POST   /api/logout`           Logout
- `GET    /api/users`            List all users
- `GET    /api/users/{id}`       Get user by ID
- `GET    /api/conversations`    List conversations
- `POST   /api/conversations`    Start new conversation
- `GET    /api/conversations/{id}/messages`  Get messages for conversation
- `POST   /api/messages`         Send a message
- `POST   /api/typing`           Send typing indicator

### Real-Time Broadcasting
- **Channels:**
  - `conversation.{conversationId}` (private, per conversation)
- **Events:**
  - `message.sent` (new message)
  - `user.typing` (typing indicator)

### Database Schema (Summary)
- **users:** id, name, email, password, timestamps
- **conversations:** id, user_one, user_two, last_message_at, timestamps
- **messages:** id, conversation_id, sender_id, message, is_read, timestamps


## 6. Security, Performance, and Best Practices

- **Authentication:** Laravel Sanctum (SPA tokens)
- **CSRF/XSS/SQLi:** All standard Laravel protections enabled
- **API Rate Limiting:** Prevent brute force and abuse
- **Password Hashing:** Secure, never stored in plain text
- **Database Indexing:** On foreign keys and unique constraints
- **Efficient Queries:** Eager loading, optimized relationships
- **Asset Optimization:** Vite for fast, production-ready builds

## 7. Extending and Customizing

- Add new features (e.g., group chat, file uploads) by creating new models, migrations, and events
- Customize frontend by editing `Chat.vue` or adding new Vue components
- Add more API endpoints in `routes/api.php` and controllers
- Use Laravel policies for advanced authorization

---

For more details, see the README.md or contact: lakibul.cse@gmail.com

---

For more details, see the README.md or contact with me: lakibul.cse@gmail.com
