# FlowState CRM  
_Manage Leads, Automate Tasks, and Keep Revenue Operations in Flow._

## 📌 Description
FlowState CRM is a full-featured Customer Relationship Management system built with Laravel and Vue 3. It is designed to support dynamic lead tracking, campaign management, user role control, reporting, and task automation for modern business teams.

## 🚀 Installation

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js >= 16
- MySQL or compatible database

### Setup Steps

```bash
# Clone the repository
git clone https://your-repo-url.git
cd CRM\ ECOSYSTEM

# Install PHP dependencies
composer install

# Install JS dependencies
yarn install

# Create environment config
cp .env.example .env

# Set application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Start dev server
yarn dev
```

## 🔌 API Integrations & Platform Compatibility

For a complete integration matrix (payments, communications, marketing, webhook endpoints, and deployment checklist), see:

- [`API_INTEGRATIONS.md`](./API_INTEGRATIONS.md)





...........................................................
...........................................................
...........................................................
...........................................................

# FlowState CRM

## Project Overview
The FlowState CRM project is a web application built using Laravel (a PHP framework) and Vue.js (a JavaScript framework). It leverages various modern web development tools and libraries to provide a robust and scalable CRM solution.

## Technologies Used
1. **Backend**:
   - **Laravel**: A PHP framework used for building the backend of the application.
   - **PHP**: The primary programming language for the backend.
   - **MySQL**: The default database used for storing application data.
   - **Redis**: Used for caching and session management.
   - **Composer**: Dependency management for PHP.

2. **Frontend**:
   - **Vue.js**: A JavaScript framework used for building the frontend of the application.
   - **Vite**: A build tool that provides a faster and leaner development experience for modern web projects.
   - **Vuetify**: A Vue UI Library with beautifully handcrafted Material Components.
   - **Axios**: A promise-based HTTP client for making API requests.

3. **Other Tools**:
   - **ESLint**: A tool for identifying and reporting on patterns found in ECMAScript/JavaScript code.
   - **Stylelint**: A linter for CSS and SCSS.
   - **PHPUnit**: A testing framework for PHP.
   - **Pusher**: A service for adding real-time functionality to web applications.

## Project Structure
- **Configuration Files**:
  - `.editorconfig`, `.env.example`, `.eslintrc-auto-import.json`, `.eslintrc.js`, `.stylelintrc.json`: Configuration files for various tools and environments.
  - `composer.json`, `composer.lock`: PHP dependencies and their versions.
  - `package.json`: JavaScript dependencies and their versions.
  - `tsconfig.json`, `jsconfig.json`: TypeScript and JavaScript configuration files.
  - `vite.config.js`: Configuration for Vite.

- **Directories**:
  - `app/`: Contains the core application code for Laravel.
  - `bootstrap/`: Contains the application bootstrapping scripts.
  - `config/`: Configuration files for Laravel.
  - `database/`: Database migrations and seeders.
  - `public/`: Publicly accessible files.
  - `resources/`: Frontend resources like views, JavaScript, and CSS.
  - `routes/`: Application routes.
  - `storage/`: Storage for logs, compiled templates, file uploads, etc.
  - `tests/`: Unit and feature tests.
  - `Console/`, `Enums/`, `Exceptions/`, `Http/`, `Models/`, `Providers/`: Various Laravel components.

## Key Operations
1. **Project Setup**:
   - Install dependencies: `npm install`
   - Compile and hot-reload for development: `npm run dev`
   - Type-check, compile, and minify for production: `npm run build`

2. **Backend Operations**:
   - Database configuration and connections are managed in `config/database.php`.
   - Session management is configured in `config/session.php`.
   - Logging is configured in `config/logging.php`.
   - Queue management is configured in `config/queue.php`.

3. **Frontend Operations**:
   - Vue components are registered and managed in `resources/js/app.js`.
   - Vue components and layouts are organized in `resources/js/components` and `resources/js/layouts`.
   - Styles are managed using SCSS and configured in `vite.config.js`.

4. **Real-time Functionality**:
   - Pusher is configured in `.env.example` and used for real-time updates.

5. **Testing**:
   - PHPUnit is used for testing the backend, configured in `phpunit.xml`.

## Example Code Excerpts
- **Vue Component**: [resources/js/components/LeadDetails.vue](resources/js/components/LeadDetails.vue)
- **Laravel Configuration**: [config/database.php](config/database.php)
- **Vite Configuration**: [vite.config.js](vite.config.js)

## Project Setup

```sh
npm install



RESOURCES:

https://manuals.setasign.com/fpdi-manual/
https://docupub.com/pdfconvert/
https://www.squarepoint.net/add-text-to-a-pdf-using-php/
https://www.webniraj.com/2016/09/12/creating-editing-a-pdf-using-php/
(add img) https://www.webniraj.com/2016/09/12/creating-editing-a-pdf-using-php/


CRM App – Technical Analysis
1. App Overview

This CRM (Customer Relationship Management) application is designed to manage leads, customers, agents, campaigns, and tasks. It is primarily built for sales, marketing, and support teams in mid-sized to large businesses who need structured pipelines and workflows to manage client engagement and internal task automation.

2. Features & Objectives
Lead Management: Assign, track, and convert leads into customers.
User Roles & Permissions: Agents, Admins, Managers with role-based access.
Automated Tasks: Scheduled commands for resetting data, reassigning leads, etc.
Campaigns: Organize and run marketing campaigns.
Reports & Analytics: Export and view performance data.
Email Integration: Via SendGrid for outbound email communication.
Charts & Dashboards: Visual insights using Chart.js and ApexCharts.
Localization: Language files support multilingual UI.
Modern Frontend: Built with Vue 3, Tailwind CSS, and Vite for responsive UX.
3. Purpose

To centralize client relationship operations across various teams while enabling secure data access, communication, and reporting. Automates repetitive tasks and enhances team collaboration.

4. Use Case Stories
As a Sales Agent, I want to see my assigned leads and update their status, so I can track conversion progress.
As a Marketing Manager, I want to create campaigns and assign them to team members to improve outreach.
As an Admin, I want to manage users, roles, and permissions to maintain security.
As a Developer, I want scheduled tasks like lead cleanup to run automatically using artisan commands.
As a Team Lead, I want real-time dashboards to monitor performance and workload.
5. Tech Stack
Layer	Technology
Frontend	Vue 3, Tailwind CSS, Vite
Backend	Laravel (PHP), Artisan Console
Database	MySQL or compatible DB via Laravel ORM
Email Service	SendGrid API
Charts	ApexCharts, Chart.js
Auth	Laravel Sanctum, Laravel UI
Scheduling	Laravel Console Commands
Dev Tools	ESLint, Prettier, Stylelint
Testing	PHPUnit
6. Directory Structure (Summary)
Directory	Description
app/	Laravel backend logic: Console commands, models, enums, providers
bootstrap/	App bootstrapping and configuration loading
config/	Laravel and service configuration files
database/	Migrations and seeds
lang/	Localization language files
public/	Public web assets (entry point via index.php)
resources/	Vue components, layouts, pages, assets (images, icons, styles)
routes/	Web and API route definitions
storage/	Logs, cache, and user-generated files
tests/	PHPUnit test cases
