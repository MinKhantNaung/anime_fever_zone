# Project Title

### Anime Fever Zone (SEO)

You can visit here: [https://animefeverzone.com](https://animefeverzone.com)

**Status**: 🚀 **Deployed in Production**

## Project Description

- 'Anime Fever Zone' is a dedicated blog for anime enthusiasts, featuring reviews, news, and analysis of popular and emerging anime series. The blog aims to create a vibrant community for anime fans to share insights and stay updated on the latest trends.

## Blogger Features

- Topic Management:
  - Ability to create, update and delete topics.
  - Can upload one image.
- Tag Management:
  - Ability to create, update and delete tags.
  - Can upload one image.
- Post Management:
  - Ability to create, update and delete posts.
  - Can upload one post image.
  - Can manage which posts are featured.
  - Can send new post emails to subscribers.
    - Section Management: 
      - Ability to create, update and delete sections of a post.
      - Can upload multiple images and videos.
- Video Management:
  - Ability to create, update and delete videos.
  - Can add YouTube video URLs (automatically extracts YouTube ID).
  - Can manage which videos are published.
  - Can mark videos as trending.
  - Videos are displayed on the home page and post pages.
- Profile Management: 
  - Can update or delete profile details and profile image.
  - Can update password.
  - Can delete own account.
  - Can decide to enable or disable the email subscription feature on the post page.

## Frontend Features

1. **Responsive Design**: The frontend is designed to be responsive, ensuring compatibility across different devices and screen sizes. Users can access and utilize the system seamlessly from desktops, laptops, tablets, and mobile devices.
2. **User Authentication**: The frontend includes a user authentication system that allows users to create accounts, log in, and manage their profiles. They can also reset password.
3. **Tag Section**:  The frontend incorporates a tag section that features related posts.
4. **Post Section**: When users click a post, they can see post with images and videos.
5. **Video Section**: 
   - Dedicated video pages with YouTube video integration.
   - Video player with theater mode support.
   - Videos displayed on home page and post pages.
   - Trending videos section.
6. **AI Text-to-Speech**: 
   - Users can listen to posts using AI-powered text-to-speech technology.
   - Powered by ElevenLabs API for natural-sounding voice synthesis.
   - Audio files are cached for faster subsequent access.
   - Available on all post pages with a simple "Listen" button.
7. **Comment System**: In post page, it includes a comment system. Users can comment in posts. *Authorized users have the ability to update and delete their own comments if necessary*.
8. **Like System**: Authenticated and non-authenticated users can like posts.
9. **Email Subscription**: Any user can subscribe to new post notifications by verifying their email.


## Technologies Used

- PHP (always latest)
- Laravel (always latest)
- Livewire (SPA)
- Alpine.js
- HTML/CSS
- Javascript
- Tailwind CSS
- SweetAlert 2
- ElevenLabs API (Text-to-Speech)
- YouTube API (Video Integration)


## Installation

### Option 1: Docker (Recommended for Local Development)

**Prerequisites:**
- Docker and Docker Compose installed on your system

**Quick Start:**

1. **Clone the repository:**
   ```bash
   git clone https://github.com/MinKhantNaung/anime_fever_zone.git
   cd anime_fever_zone
   ```

2. **Create environment file:**
   ```bash
   cp .env.docker.example .env
   ```

3. **Configure environment variables in `.env`:**
   - The `.env.docker.example` file already contains Docker-optimized settings
   - Update the following required variables:
     - `APP_KEY` - Will be generated in step 6
     - `ELEVENLABS_API_KEY=your_api_key_here` - Required for text-to-speech feature
     - `ELEVENLABS_VOICE_ID=your_voice_id` - Optional
     - `ELEVENLABS_MODEL_ID=your_model_id` - Optional
   - All other Docker-specific settings (database host, Redis host, etc.) are already configured

4. **Build and start Docker containers:**
   ```bash
   # Standard build (uses cache, faster)
   docker compose up -d --build
   
   # OR for a completely fresh build (slower, recommended for first-time setup)
   docker compose build --no-cache app
   docker compose up -d
   ```
   
   > **Note:** The `--no-cache` flag is optional but recommended for first-time setup to ensure all dependencies are properly installed. For subsequent runs, `docker compose up -d --build` is sufficient.

5. **Install dependencies inside the container:**
   ```bash
   docker compose exec app composer install
   docker compose exec app npm install
   ```

6. **Generate application key:**
   ```bash
   docker compose exec app php artisan key:generate
   ```

7. **Run database migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

8. **Seed the database:**
   ```bash
   docker compose exec app php artisan db:seed
   ```

9. **Create storage link:**
   ```bash
   docker compose exec app php artisan storage:link
   ```

10. **Access the application:**
    - Open your browser and navigate to: `http://localhost:8081`
    - The application should now be running!

**Useful Docker Commands:**

```bash
# View running containers
docker compose ps

# View logs
docker compose logs -f app
docker compose logs -f nginx

# Stop containers
docker compose down

# Stop and remove volumes (cleans database)
docker compose down -v

# Execute commands in container
docker compose exec app php artisan [command]
docker compose exec app composer [command]
docker compose exec app npm [command]

# Access container shell
docker compose exec app bash

# Rebuild containers after Dockerfile changes
docker compose up -d --build
```

**Default Services:**
- **Application:** Laravel Octane (Swoole) running on port 8000 (internal)
- **Nginx:** Web server on port 8081 (external)
- **MySQL:** Database on port 3307 (external)
- **Redis:** Cache/Queue on port 6379 (external)

**Default Database Credentials:**
- Database: `anime_fever_zone`
- Username: `animeuser`
- Password: `secret`
- Root Password: `secret`

---

### Option 2: Manual Installation (Without Docker)

- If cloning my project is complete or download is complete, open terminal in project directory.
- Install composer dependencies
  - **composer install** (command)
- Install npm dependencies
  - **npm install**
- Create a copy of .env file
  - **cp .env.example .env**
- Generate an app encryption key
  - **php artisan key:generate**
- Create an empty database for my web project
  - created database name must match from .env file
- Configure environment variables:
  - Set up your ElevenLabs API key for text-to-speech feature:
    - `ELEVENLABS_API_KEY=your_api_key_here`
    - `ELEVENLABS_VOICE_ID=your_voice_id` (optional, defaults provided)
    - `ELEVENLABS_MODEL_ID=your_model_id` (optional, defaults provided)
- Start npm 
  - **npm run dev**
- Migrate
  - **php artisan migrate**
- Seed Database
  - **php artisan db:seed**
- Link storage
  - **php artisan storage:link**
- Start 
  - **php artisan serve**

## SEO  

- I write custom command for generating site-map
  - **php artisan sitemap:generate**

## Usage

- Need Internet!
- In UserSeeder.php, I created blogger account.
- Login as blogger,
  - Email - blogger@gmail.com

## Production Deployment

This application is currently deployed and running in production at [https://animefeverzone.com](https://animefeverzone.com). The production environment includes:

- All features mentioned above are live and functional
- Video integration with YouTube
- AI text-to-speech functionality for posts
- Full SEO optimization with sitemap generation
- Email subscription system
- Comment and like systems
