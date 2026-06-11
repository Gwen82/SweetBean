# Product Requirements Document (PRD)

## Project Title

Online Ordering System for Sweet Bean Cafe (Pickup & Delivery)

---

# 1. Project Overview

Sweet Bean Cafe currently faces several operational problems during busy hours, such as long customer queues, incorrect order input, and difficulty tracking order status. These problems reduce service efficiency and customer satisfaction.

To solve these issues, this project develops an Online Ordering System using PHP and MySQL. The system allows customers to order food and drinks online, choose pickup or delivery services, and track order status in real time. Staff and administrators can also manage orders, menu items, employees, and reports through the system.

---

# 2. Project Objectives

The objectives of this project are:

* Reduce ordering mistakes during busy hours
* Shorten customer waiting time
* Improve order tracking process
* Improve cafe operational efficiency
* Provide better customer experience
* Allow staff and admin to manage orders more efficiently

---

# 3. Scope of the System

The system includes three main user roles:

## 3.1 Customer

Customers can:

* Register and login
* Browse menu items
* Add products to cart
* Select pickup or delivery
* Enter delivery address
* Make simulated payment
* Track order status
* View order history
* Submit reviews and ratings
* Subscribe to newsletter emails

---

## 3.2 Staff

Staff can:

* Log in to staff dashboard
* View incoming orders
* Manage order processing
* Update order status
* Update delivery status
* Mark orders as completed

---

## 3.3 Admin

Admin can:

* Manage menu items (add, edit, delete)
* Manage employees
* View sales reports
* Manage customer reviews
* Send newsletter emails to subscribers
* Monitor overall system activities

---

# 4. Functional Requirements

## 4.1 Authentication Module

### Features

* User registration
* User login/logout
* Session management
* Role-based access control

### Inputs

* Email
* Password

### Outputs

* Successful login
* Error message if invalid

---

## 4.2 Menu Management Module

### Features

* Display menu items
* Search or browse menu
* Show item details
* Admin CRUD operations

### Menu Information

* Product name
* Price
* Description
* Category
* Product image

---

## 4.3 Shopping Cart Module

### Features

* Add item to cart
* Update quantity
* Remove item
* Calculate total price

---

## 4.4 Checkout Module

### Features

* Choose pickup or delivery
* Enter delivery address
* Payment simulation
* Create order record

### Outputs

* Order confirmation
* Order ID

---

## 4.5 Order Tracking Module

### Features

Customers can view order status:

* Pending
* Preparing
* Ready
* Delivering
* Completed

Staff can update statuses in real time.

---

## 4.6 Review and Rating Module

### Features

* Customers can submit reviews
* Customers can rate products/services
* Admin can manage reviews

---

## 4.7 Newsletter Email Module

### Features

* Customer email subscription
* Admin sends promotional emails
* Order confirmation emails
* Order completion notification emails

### Technology

* PHPMailer
* SMTP Email Service

---

## 4.8 Sales Report Module

### Features

* Daily sales report
* Monthly sales report
* Total orders summary
* Revenue summary

---

# 5. Non-Functional Requirements

## Performance

* System should load pages quickly
* Order processing should update in real time

## Security

* Password encryption using hashing
* Session protection
* SQL injection prevention

## Usability

* Simple and user-friendly interface
* Easy navigation for customers and staff

## Compatibility

* Compatible with desktop browsers
* Compatible with mobile browsers

---

# 6. System Architecture

## Frontend

* HTML
* CSS

## Backend

* PHP

## Database

* MySQL

## Email Service

* PHPMailer

## Server Environment

* XAMPP / Laragon

---

# 7. Database Design

Main tables:

* users
* menu
* cart
* orders
* order_items
* reviews
* newsletter_subscribers

---

# 8. User Flow

## Customer Flow

Register/Login
→ Browse Menu
→ Add to Cart
→ Checkout
→ Choose Pickup/Delivery
→ Payment
→ Track Order
→ Receive Order
→ Submit Review

---

## Staff Flow

Login
→ View Orders
→ Update Order Status
→ Manage Delivery
→ Complete Order

---

## Admin Flow

Login
→ Manage Menu
→ Manage Employees
→ View Reports
→ Manage Reviews
→ Send Newsletter

---

# 9. Development Plan

## Phase 1

* Database setup
* System structure
* Authentication module

## Phase 2

* Customer ordering system
* Cart and checkout

## Phase 3

* Order tracking system
* Staff management features

## Phase 4

* Admin management system
* Sales reports
* Review system

## Phase 5

* Newsletter email integration
* Testing and debugging

---

# 10. Expected Outcomes

After completing this project:

* Customers can place orders more efficiently
* Staff can manage orders faster
* Admin can monitor cafe operations easily
* Cafe service quality and efficiency will improve

---

# 11. Conclusion

This Online Ordering System is designed to improve the operational process of Sweet Bean Cafe by reducing ordering errors, decreasing waiting time, and improving order management efficiency. The system provides convenience for customers while helping staff and administrators manage cafe operations more effectively.