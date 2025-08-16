# Laravel Real-Time Chat Application

A complete real-time private messaging system built with Laravel 10, Vue.js 3, and Pusher for WebSocket communication.

## Features

### ✅ Completed Features

1. **Authentication System**
   - User registration and login with Laravel Sanctum
   - Token-based API authentication
   - Secure logout functionality

2. **User Management**
   - Display list of all registered users
   - Start conversations with any user
   - View user profiles

3. **Real-Time Chat Module**
   - One-to-one private messaging
   - Message persistence in database
   - Real-time message delivery using Pusher
   - Message timestamps

4. **Frontend (Vue.js 3)**
   - Modern Vue 3 Composition API
   - Responsive chat interface
   - Message bubbles with sender identification
   - Auto-scroll to latest messages
   - Typing indicators
   - Unread message counters
   - Loading states for better UX

5. **Backend (Laravel)**
   - RESTful API endpoints
   - Real-time broadcasting with Laravel Events
   - Database models and relationships
   - Input validation and error handling

6. **Bonus Features**
   - Typing indicator ("User is typing...")
   - Unread message count beside users
   - Conversation history
   - Proper API validation and error handling

## Tech Stack

- **Backend**: Laravel 10 with Sanctum authentication
- **Frontend**: Vue.js 3 with Composition API
- **Real-time**: Pusher for WebSocket communication
- **Database**: MySQL (configurable to SQLite)
- **Build Tool**: Vite
- **Styling**: Custom CSS with responsive design

## Project Structure

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

## API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login
- `GET /api/me` - Get current user
- `POST /api/logout` - User logout

### Users
- `GET /api/users` - Get all users (except current)
- `GET /api/users/{id}` - Get specific user

### Chat
- `GET /api/conversations` - Get user's conversations
- `GET /api/conversations/{id}/messages` - Get messages for conversation
- `POST /api/messages` - Send a new message
- `POST /api/conversations` - Start new conversation
- `POST /api/typing` - Send typing indicator

## Real-Time Events

### Broadcasting Channels
- `conversation.{conversationId}` - Private channel for each conversation

### Events
- `message.sent` - New message in conversation
- `user.typing` - User typing indicator

## Database Schema

### Users Table
- id, name, email, password, timestamps

### Conversations Table
- id, user_one, user_two, last_message_at, timestamps
- Unique constraint on user pairs

### Messages Table
- id, conversation_id, sender_id, message, is_read, timestamps

## Setup Instructions

### Prerequisites
- PHP 8.1+
- Composer
- Node.js 18+
- MySQL or SQLite
- Pusher account (for real-time features)

### Installation

1. **Clone and Install Dependencies**
   ```bash
   cd c:\laragon\www\laravel-chat
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_chat
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Pusher Configuration**
   Sign up at [Pusher.com](https://pusher.com) and add to `.env`:
   ```env
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_APP_CLUSTER=mt1
   ```

5. **Database Migration**
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

6. **Build Frontend**
   ```bash
   npm run dev  # or npm run build for production
   ```

7. **Start Application**
   ```bash
   php artisan serve
   ```

   Visit: http://localhost:8000

## Test Users

The seeder creates these test users (password: `password123`):
- john@example.com (John Doe)
- jane@example.com (Jane Smith) 
- bob@example.com (Bob Johnson)
- alice@example.com (Alice Brown)
- charlie@example.com (Charlie Wilson)

## Usage

1. **Registration/Login**: Create account or login with test credentials
2. **Start Chatting**: Click on any user from the sidebar to start conversation
3. **Real-time Messaging**: Messages appear instantly without page refresh
4. **Typing Indicators**: See when other users are typing
5. **Conversation History**: Access previous conversations from sidebar

## Troubleshooting

### Database Issues
- Ensure MySQL/SQLite is running
- Check database credentials in `.env`
- Run migrations: `php artisan migrate:fresh`

### Real-time Issues
- Verify Pusher credentials
- Check browser console for connection errors
- Ensure BroadcastServiceProvider is enabled

### Frontend Issues
- Clear browser cache
- Restart Vite dev server: `npm run dev`
- Check for JavaScript errors in console

## Security Features

- CSRF protection
- API rate limiting
- SQL injection prevention
- XSS protection
- Secure password hashing
- JWT token authentication

## Performance Optimizations

- Database indexing on foreign keys
- Efficient message loading
- Connection pooling ready
- Asset optimization with Vite

## Future Enhancements

- File/image sharing
- Message search functionality
- Group chat support
- Push notifications
- Message reactions
- User presence indicators
- Message encryption
- Admin panel

## Contributing

This is a complete demo application showcasing real-time chat implementation with modern web technologies.

## License

Open source - feel free to use and modify as needed.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
