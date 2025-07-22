# ScriptaVox

**Memoriza la Palabra de Dios, Versículo a Versículo.**

ScriptaVox is a modern web application designed to help users learn, practice, and retain Bible verses through an engaging, interactive, and scientifically-backed process. Inspired by the best features of learning apps like Duolingo and Anki, ScriptaVox transforms scripture memorization into a delightful and effective daily habit.

![ScriptaVox Welcome Page](https//i.imgur.com/your-screenshot-url.png) <!-- It's highly recommended to add a screenshot of your app here! -->

## Core Features

Our vision is built on three core pillars:

- **🧠 Aprende (Learn):** A multi-stage learning pipeline guides users from their first encounter with a verse to full comprehension. This includes:

    - **Study Mode:** Simple, focused reading to build familiarity.
    - **Word Bank:** An interactive, Duolingo-style exercise to construct the verse from jumbled words, teaching sentence structure and flow.

- **🗣️ Recita (Recite):** This is the soul of ScriptaVox. Users don't just type—they speak the Word out loud.

    - **Practice Recitation:** A low-pressure practice mode with first-letter prompts to build confidence.
    - **Intelligent Feedback:** Using the Web Speech API, the app provides a percentage-based accuracy score and a detailed visual "diff" of the user's recitation, highlighting correct, missed, and added words.

- **❤️ Retén (Retain):** Powered by a Spaced Repetition System (SRS), our "Anki brain" ensures long-term retention.
    - **Daily Review:** The app intelligently schedules verses for review at scientifically determined intervals (1 day, 3 days, 1 week, etc.).
    - **Self-Assessment:** Users rate their recall ("Again," "Hard," "Good"), which tunes the algorithm for the next review, personalizing their learning journey.

## Tech Stack

ScriptaVox is built on a modern, robust technology stack:

- **Backend:** Laravel 11+
- **Frontend:** Vue.js 3 with TypeScript (using the `<script setup>` syntax)
- **Styling:** Tailwind CSS
- **UI Components:** [Shadcn/Vue](https://www.shadcn-vue.com/) for beautiful, accessible, and theme-able components.
- **Stack:** The TALL stack philosophy, adapted with Vue & Inertia.js for a seamless single-page application experience.
- **Database:** MySQL
- **Deployment:** Configured for deployment on [Railway](https://railway.app/).

## Getting Started

Follow these instructions to get a local development environment up and running.

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- A local MySQL database

### Installation

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/jm-mogo/scriptavox.git
    cd scriptavox
    ```

2.  **Install PHP dependencies:**

    ```bash
    composer install
    ```

3.  **Install NPM dependencies:**

    ```bash
    npm install
    ```

4.  **Set up your environment file:**
    Copy the example environment file and generate your application key.

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5.  **Configure your `.env` file:**
    Update the `DB_*` variables in your `.env` file to match your local database credentials.

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=scriptavox
    DB_USERNAME=root
    DB_PASSWORD=
    ```

6.  **Run database migrations:**

    ```bash
    php artisan migrate
    ```

7.  **Seed the Database with Bible Data:**
    The application includes a system to fetch and seed the Bible data from an external API.

    **Step 7a: Fetch and Cache the Data (Run this only once)**
    This command will download all ~1,189 chapters and save them locally. It will take a few minutes.

    ```bash
    php artisan bible:fetch-api
    ```

    **Step 7b: Seed the Database from the Local Cache**
    This command is very fast and reads from the files you just downloaded.

    ```bash
    php artisan db:seed --class=BibleSeeder
    ```

8.  **Compile frontend assets and start the development server:**

    ```bash
    npm run dev
    ```

9.  **Serve the application:**
    In a separate terminal, run the Laravel development server.
    ```bash
    php artisan serve
    ```

You can now access the application at `http://localhost:8000`.

## About the Author

This project was developed with passion by **José Mogollón**.

- **GitHub:** [@jm-mogo](https://github.com/jm-mogo)
- Feel free to connect and explore my other projects!

---
