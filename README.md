
# HR Management API

HR Management API is a RESTful Laravel 12 backend system designed to manage human resources operations such as employee records, departments, payroll, leaves, and candidate applications.

> ✅ **Live Deployment Available:**  
> You can now test the API live at:  
> 🔗 [https://hr-management-api-main-vs9swg.laravel.cloud](https://hr-management-api-main-vs9swg.laravel.cloud)

> 📫 **Updated Postman Collection:**  
> [Download Collection](./HR_Management_API.postman_collection.json)

---

## 🚀 Features

- User authentication via Laravel Sanctum
- Role-based access control using Spatie (admin, hr, employee, candidate)
- Department and Employee management with assignment logic
- Payroll management with PDF generation
- Leave request and approval system with email notifications
- Candidate registration and CV tracking system
- API tested and documented with Postman

---

## 🧱 Tech Stack

- **Framework**: Laravel 12
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel-Permission
- **Database**: MySQL
- **Email Testing**: Mailtrap
- **Testing Tool**: Postman
- **Database Visualization**: DBeaver

---

## 📁 Modules

### 🔐 Authentication & Roles

- `/login`, `/register` for users
- `/candidate/register`, `/candidate/login` for applicants
- Roles: `admin`, `hr`, `employee`, `candidate`
- Sanctum middleware protects sensitive routes

---

### 🧑‍💼 Employee Management

- CRUD operations for employees
- Automatic department assignment on creation
- Filter employees by department or status

---

### 🏢 Department Management

- Supports hierarchical departments via `parent_id`
- Assign/unassign employees
- Cost analysis based on active employee salaries

---

### 💰 Payroll Management

- View and record salary data
- Generate monthly payroll as PDF
- Future plan: Gross-to-net salary calculator

---

### 🏖️ Leave Management

- Employees request leave with reason and dates
- HR can approve or reject with status endpoints
- Email notification sent to HR on request

---

### 📄 Candidate / CV Module

- Candidates register separately from users
- All candidates are assigned the `candidate` role
- HR can:
  - View/filter candidates
  - Approve or reject
  - Add internal notes
  - Mark as favorite
  - Send email notification

---

## 🔐 API Structure

```php
// Public routes
POST /login
POST /register

// Candidate routes
POST /candidate/register
POST /candidate/login

// Protected routes (auth:sanctum)
GET /me
POST /logout
RESOURCE /employees
RESOURCE /departments
RESOURCE /payrolls
RESOURCE /leaves
