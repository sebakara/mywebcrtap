# MyWebCRTap

A **multi-user video chat application** built with **Laravel 8** (^8.75), **PHP 7.4**, **Blade templates**, and **WebRTC**.  
Users can join rooms, share their video/audio streams, and see other participants in real-time.

---

## 🛠 Features

- Multi-user video chat rooms
- Real-time peer-to-peer streaming via **WebRTC**
- User-defined display names for video labels
- Responsive video grid layout
- Simple Blade + JS frontend (no React/Vue)
- Local development with Laravel WebSockets
- Hover effects and responsive video styling

---

## ⚙️ Requirements

- PHP >= 7.4  
- Composer  
- MySQL or any supported database  
- Browser with WebRTC support (Chrome, Firefox, Edge, Safari)  
- Node.js (optional, only for frontend build tools)  

---

## 📦 Installation

1. Clone the repository:

```bash
git clone https://github.com/yourusername/mywebcrtap.git
cd mywebcrtap

Install PHP dependencies:

composer install


Copy .env and generate an application key:

cp .env.example .env
php artisan key:generate


Configure your .env database settings:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mywebcrtap
DB_USERNAME=root
DB_PASSWORD=


Run database migrations:

php artisan migrate


Install Laravel WebSockets (v1.x for PHP 7.4 + Laravel 8):

composer require beyondcode/laravel-websockets:^1.12
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
php artisan migrate
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"


Start WebSocket server:

php artisan websockets:serve


Start Laravel development server:

php artisan serve --host=0.0.0.0 --port=8000

🌐 Usage

Open your browser and visit:

http://<your-server-ip>:8000/room/123


Replace 123 with any room ID you want to join.

Enter your display name and click Join Room.

Your video will appear along with other participants in the room.

Share the same room URL with other users on the same network to start a multi-user video call.

🔧 Configuration
Broadcasting (.env)
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=localapp
PUSHER_APP_KEY=localkey
PUSHER_APP_SECRET=localsecret
PUSHER_APP_CLUSTER=mt1

Broadcasting Config (config/broadcasting.php)
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'useTLS' => false,
        'host' => '127.0.0.1',
        'port' => 6001,
        'scheme' => 'http',
    ],
],

WebSockets Config (config/websockets.php)

Ensure apps array matches .env settings:

'apps' => [
    [
        'id' => env('PUSHER_APP_ID'),
        'name' => 'MyWebCRTap',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'path' => '',
        'capacity' => null,
        'enable_client_messages' => true,
        'enable_statistics' => true,
    ],
],

💻 Testing on Local Network

Run Laravel and WebSocket servers:

php artisan serve --host=0.0.0.0 --port=8000
php artisan websockets:serve --host=0.0.0.0 --port=6001


On another device connected to the same LAN, open:

http://<server-ip>:8000/room/<room-id>


Enter a name and start video chat.

⚠️ Make sure firewall allows ports 8000 (HTTP) and 6001 (WebSockets).

🎨 Frontend & Styling

Responsive video grid using CSS Grid.

Local video highlighted with blue border.

Remote videos show user-provided names.

Hover effect on videos for subtle zoom.

Users enter a display name before joining the call.

🔗 Project Structure
mywebcrtap/
├── app/
│   └── Events/WebRTCSignaling.php
├── resources/views/room.blade.php
├── routes/web.php
├── database/migrations/
├── public/
├── composer.json
└── .env.example

⚠️ Notes

WebRTC requires HTTPS for production.

Local testing can use HTTP on localhost or LAN.

For production, consider Soketi or Pusher instead of local WebSockets.

PHP 7.4 + Laravel 8 requires WebSockets v1.x stable release.

---

This **single README** combines:  
- Installation & migrations  
- WebSocket setup  
- Multi-user WebRTC usage  
- Styling & layout info  
- Configuration for `.env` and broadcasting  
- LAN/local testing tips  

---


