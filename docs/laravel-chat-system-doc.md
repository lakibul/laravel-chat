# Laravel Chat System Documentation

## 1. Architecture and Folder Structure

This Laravel chat system is structured for clarity, scalability, and real-time communication. Below is an overview of the main folders and their responsibilities:

```
app/
├── Models/
│   ├── User.php          # User model with relationships
│   ├── Conversation.php  # Conversation model
│   └── Message.php       # Message model
├── Http/Controllers/Api/
│   ├── AuthController.php    # Authentication endpoints
│   ├── ChatController.php    # Chat functionality
│   └── UserController.php    # User management
└── Events/
    ├── MessageSent.php       # Real-time message broadcasting
    └── UserTyping.php        # Typing indicator broadcasting

database/migrations/
├── create_conversations_table.php  # Conversation schema
└── create_messages_table.php       # Message schema

resources/
├── js/
│   ├── app.js                      # Vue app entry point
│   ├── bootstrap.js                # Laravel Echo & Pusher setup
│   └── components/
│       └── Chat.vue                # Main chat component
└── views/
    └── chat.blade.php              # Main application view

routes/
├── api.php         # API routes
├── web.php         # Web routes
└── channels.php    # Broadcasting channels
```

## 2. Technologies and Decisions Made

- **Backend:** Laravel 10
  - RESTful API for authentication, user management, and chat
  - Laravel Sanctum for API authentication
  - Laravel Events and Broadcasting for real-time features
- **Frontend:** Vue.js 3 (Composition API)
  - Modern, reactive UI for chat
  - Real-time updates via Laravel Echo and Pusher
- **Real-time:** Pusher
  - WebSocket-based real-time messaging and typing indicators
- **Database:** MySQL (configurable to SQLite)
  - Relational structure for users, conversations, and messages
- **Build Tool:** Vite
  - Fast frontend asset bundling and hot reload
- **Styling:** Custom CSS
  - Responsive, modern chat interface

### Key Decisions
- **Separation of Concerns:** API endpoints are separated from web routes. Vue handles all frontend logic, while Laravel manages backend and real-time events.
- **Real-Time Communication:** Pusher is used for reliable, scalable WebSocket communication. Laravel Echo integrates frontend events.
- **Security:** Sanctum for authentication, CSRF protection, API rate limiting, and secure password hashing.
- **Scalability:** Database indexing, efficient queries, and modular code structure.

## 3. Project Features Overview
- User registration/login (with Sanctum)
- List users, start conversations, view profiles
- One-to-one real-time chat (Pusher)
- Message persistence and history
- Typing indicators, unread message counters
- RESTful API endpoints
- Responsive Vue.js frontend

## 4. Setup & Usage (Summary)
1. Clone repo & install dependencies (`composer install`, `npm install`)
2. Configure `.env` for database and Pusher
3. Run migrations and seeders (`php artisan migrate`, `php artisan db:seed --class=UserSeeder`)
4. Build frontend (`npm run dev`)
5. Start server (`php artisan serve`)
6. Access at http://localhost:8000

## 5. API & Real-Time Events (Summary)
- **API:** `/api/register`, `/api/login`, `/api/users`, `/api/conversations`, `/api/messages`, `/api/typing`
- **Broadcasting:** `conversation.{id}` channels, `message.sent`, `user.typing` events

## 6. Security & Performance
- CSRF, XSS, SQL injection protection
- API rate limiting
- Database indexing
- Asset optimization with Vite

---

For more details, see the README.md or contact with me: lakibul.cse@gmail.com
