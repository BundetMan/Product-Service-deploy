# 📦 Product Service App

A full-stack product management application powered by **Slim PHP** for the backend and **React (Vite)** for the frontend. Easily deployable on platforms like **Railway**.

---

## 🚀 Features

- 🧱 Slim PHP REST API backend
- ⚡ Vite-powered React frontend (optional)
- 🔐 Secure environment variable handling with `.env`
- ☁️ Ready for deployment on Railway
- 🗃 PostgreSQL support

---

## 📁 Project Structure

product-service-app/
│
├── public/ # PHP public entry point (index.php)
├── src/ # Config and logic files
├── vendor/ # Composer packages (generated)
├── react-crud-app/ # React frontend (optional)
├── .env.example # Template for environment variables
├── composer.json # PHP dependencies
├── README.md # You're here
└── ...

## 🧑‍💻 Getting Started (Backend - Slim PHP)

### 1. Clone the Repository

```bash
git clone https://github.com/BundetMan/Product-Service-deploy
cd product-service-app
2. Install PHP Dependencies
composer install
3. Set Up Environment Variables
Create your own .env file by copying the example:
cp .env.example .env
Then update .env with your database info and other settings:
DB_HOST=localhost
DB_PORT=5432
DB_NAME=your_db
DB_USER=your_user
DB_PASS=your_password
APP_ENV=development
WEBHOOK_URL=http://localhost:8080/api/webhook
4. Run the Backend Locally
Start the PHP server:
php -S localhost:8080 -t public
Visit http://localhost:8080

⚛️ React Frontend (Optional)
If you're using the react-crud-app frontend:
1. Install Node Modules
cd react-crud-app
npm install

2. Set API Base URL
Create a .env file inside react-crud-app:
VITE_API_URL=http://localhost:8080/api

3. Run the Frontend Dev Server
npm run dev
Then visit http://localhost:5173


🚀 Deploying on Railway

1. Push the project to GitHub
2. Create a new project on Railway
3. Connect your GitHub repository
4. Add all the variables from .env to the Railway "Variables" tab
5. Deploy!
Environment variables in Railway will automatically be injected into your container.

🛠 Required Environment Variables
| Key           | Required | Description                     |
| ------------- | -------- | ------------------------------- |
| `DB_HOST`     | ✅        | Your database host              |
| `DB_PORT`     | ✅        | DB port (usually `5432`)        |
| `DB_NAME`     | ✅        | Name of your database           |
| `DB_USER`     | ✅        | Database username               |
| `DB_PASS`     | ✅        | Database password               |
| `APP_ENV`     | ✅        | `development` or `production`   |
| `WEBHOOK_URL` | ✅        | API webhook endpoint (optional) |

🧹 .gitignore Example
# Environment
.env

# Vendor folder
/vendor/

# Node modules
react-crud-app/node_modules/

# IDE/Editor files
.idea/
.vscode/
.DS_Store
Thumbs.db

📄 License
This project is open-source and available under the MIT License.

🙌 Credits
Made with ❤️ by BundetMan