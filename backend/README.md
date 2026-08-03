# SkillBridge BD - Application Tracking System

Welcome to the official repository of the **Application Tracking System (ATS)** developed by **SkillBridge BD**. This system helps companies efficiently manage job applications, track candidates through various hiring stages, and streamline the recruitment process.

---

## 🚀 Features

- Job posting management  
- Candidate application tracking  
- Resume uploads and parsing  
- Interview scheduling and management  
- Status updates & notifications  
- Role-based access (Admin, HR, Interviewer, Applicant)  
- Dashboard with insights and reports

---

## 🏗️ Tech Stack

- **Backend**: Laravel / Node.js  
- **Frontend**: Vue.js / React  
- **Database**: MySQL / PostgreSQL  
- **Authentication**: Laravel Sanctum / JWT  
- **Deployment**: Docker, NGINX, Ubuntu Server

---

## 📦 Installation

```bash
git clone https://github.com/jisan25/sbats.git
cd skillbridge-ats
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev
