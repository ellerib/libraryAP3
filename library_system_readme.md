# Library Management System

A web-based system for managing book borrowing, reservations, returns, penalties, and user roles (Student, Teacher, Staff, Librarian).

---

## Table of Contents
1. Overview
2. Features
3. User Roles
4. How to Use the System
5. Borrowing Rules
6. Reservation Rules
7. Penalty Handling
8. Staff/Admin Features
9. Error Messages
10. Logout
11. Developer Notes

---

## 1. Overview

The Library Management System allows students, teachers, and staff to manage core library operations.
This README provides instructions on how the system works and how to use each feature.

---

## 2. Features
- User Login (Student, Teacher, Staff, Librarian)
- Borrowing books
- Reserving books
- Returning books
- Penalty tracking for overdue returns
- Book inventory management
- Staff approval for borrow/reserve requests
- User management
- Real-time book quantity updates

---

## 3. User Roles

**Student:**
- Borrow books (Max 3 per semester)
- Reserve books
- View penalties
- View borrowed/reserved books

**Teacher:**
- Borrow books (Unlimited)
- Reserve books
- View history

**Staff / Admin:**
- Approve borrow/reserve requests
- Add/edit/delete books
- Manage inventory
- Handle penalties
- Manage users

---

## 4. How to Use the System

### Login
1. Enter email and password
2. Click **Login**
3. Redirected to the appropriate dashboard based on role and user_id

---

## 5. Borrowing Books

### Steps:
1. Open **Available Books**
2. Click **Borrow**
3. Enter:
   - Borrow date
   - Return date
   - Semester (1st/2nd)
4. Submit

**Rules:**
- Students: Max 3 active borrowings per semester
- Teachers: Unlimited
- Book quantity decreases only after staff approval

---

## 6. Reserving Books

### Steps:
1. Open **Available Books**
2. Click **Reserve**
3. Enter:
   - Reservation date
   - Pickup date
   - Semester
4. Submit

**Notes:**
- Cannot reserve the same book twice in the same semester
- Quantity does not reduce until staff approval

---

## 7. Returning Books

1. Go to **Borrowed Books**
2. Click **Return**
3. System updates:
   - Status → Returned
   - Book quantity increases
   - Penalty generated if overdue

---

## 8. Penalties

Penalty page shows:
- Book title
- Days overdue
- Penalty cost
- Status (Pending/Paid)

Staff can mark penalties as **Paid**.

---

## 9. Staff/Admin Features

**Book Management:**
- Add, edit, delete books
- Update quantities
- Archive books

**Approvals:**
- Approve/Reject borrow requests
- Approve/Reject reservations

**User Management:**
- Create accounts
- Edit user info
- Reset passwords

---

## 10. Error Messages
| Message | Meaning |
|---------|---------|
| Borrow limit reached | Student exceeded 3-book limit |
| Already reserved this book | Duplicate reservation |
| All fields are required | Missing input field |
| Access denied | Unauthorized user |

---

## 11. Logout
Click **Logout** to safely end the session.

---

## 12. Developer Notes
- Book quantity decreases only after staff approval for borrow/reserve
- Borrowing and reservation are separated into dedicated classes
- Semester-based rules enforced in backend
- Uses PHP OOP, MySQLi prepared statements, and session-based roles
- System is responsive and uses simple modern design dashboard UI styles
- Only one user can login and cannot login another user even when switching tabs

---
