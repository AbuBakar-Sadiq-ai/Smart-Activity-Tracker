# schema_description.md

# Relational Database Schema Description

## Database Name

```sql
activity_tracker_db
```

---

# USERS Table

## Description

The users table stores registered user accounts and authentication information.

| Attribute  | Data Type    | Constraints                 |
| ---------- | ------------ | --------------------------- |
| id         | INT          | Primary Key, Auto Increment |
| name       | VARCHAR(100) | NOT NULL                    |
| email      | VARCHAR(150) | UNIQUE, NOT NULL            |
| password   | VARCHAR(255) | NOT NULL                    |
| created_at | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP   |

---

# PRAYERS Table

## Description

The prayers table stores daily prayer tracking records for each user.

| Attribute   | Data Type  | Constraints                 |
| ----------- | ---------- | --------------------------- |
| id          | INT        | Primary Key, Auto Increment |
| user_id     | INT        | Foreign Key                 |
| prayer_date | DATE       | NOT NULL                    |
| fajr        | TINYINT(1) | DEFAULT 0                   |
| zuhar       | TINYINT(1) | DEFAULT 0                   |
| asar        | TINYINT(1) | DEFAULT 0                   |
| maghrib     | TINYINT(1) | DEFAULT 0                   |
| isha        | TINYINT(1) | DEFAULT 0                   |
| qaza        | TINYINT(1) | DEFAULT 0                   |
| created_at  | TIMESTAMP  | DEFAULT CURRENT_TIMESTAMP   |

## Foreign Key Relationship

```text
prayers.user_id → users.id
```

---

# STUDY Table

## Description

The study table stores study progress records and completed subjects.

| Attribute    | Data Type    | Constraints                 |
| ------------ | ------------ | --------------------------- |
| id           | INT          | Primary Key, Auto Increment |
| user_id      | INT          | Foreign Key                 |
| study_date   | DATE         | NOT NULL                    |
| subject_name | VARCHAR(255) | NOT NULL                    |
| completed    | TINYINT(1)   | DEFAULT 0                   |
| created_at   | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP   |

## Foreign Key Relationship

```text
study.user_id → users.id
```

---

# SKILLS Table

## Description

The skills table stores user skill development progress records.

| Attribute  | Data Type    | Constraints                 |
| ---------- | ------------ | --------------------------- |
| id         | INT          | Primary Key, Auto Increment |
| user_id    | INT          | Foreign Key                 |
| skill_date | DATE         | NOT NULL                    |
| skill_name | VARCHAR(255) | NOT NULL                    |
| completed  | TINYINT(1)   | DEFAULT 0                   |
| created_at | TIMESTAMP    | DEFAULT CURRENT_TIMESTAMP   |

## Foreign Key Relationship

```text
skills.user_id → users.id
```

---

# Primary Keys and Foreign Keys

## Primary Keys

| Table   | Primary Key |
| ------- | ----------- |
| users   | id          |
| prayers | id          |
| study   | id          |
| skills  | id          |

---

## Foreign Keys

| Table   | Foreign Key | References |
| ------- | ----------- | ---------- |
| prayers | user_id     | users.id   |
| study   | user_id     | users.id   |
| skills  | user_id     | users.id   |

---

# Relationships Summary

* One user can have multiple prayer records
* One user can have multiple study records
* One user can have multiple skill records

---

# milestone1_report.docx CONTENT

# Milestone 1 — ERD & Relational Database Schema

# Smart Daily Activity Tracker

## Database Systems Lab Semester Project

---

# Project Overview

The Smart Daily Activity Tracker is a full-stack web application developed for Database Systems Lab. The system helps users manage and track their daily productivity activities including Namaz tracking, study progress, and skill development.

The application provides a secure authentication system where users can create accounts, log in securely, and manage their daily records through a personalized dashboard.

The system stores user activity records in a relational MySQL database using multiple related tables.

---

# Main Entities

1. Users
2. Prayers
3. Study
4. Skills

---

# Relationships

* One User can have many Prayer records
* One User can have many Study records
* One User can have many Skill records

---

# ERD Structure

```text
Users
  |
  |------< Prayers
  |
  |------< Study
  |
  |------< Skills
```

---

# Database Design Goals

* Reduce data redundancy
* Maintain data integrity
* Support future scalability
* Organize user activity records efficiently
* Maintain relational consistency

---

# Technology Stack

| Technology | Purpose                 |
| ---------- | ----------------------- |
| HTML       | Frontend Structure      |
| CSS        | Frontend Styling        |
| JavaScript | Frontend Interactivity  |
| PHP        | Backend Logic           |
| MySQL      | Database Management     |
| Chart.js   | Dashboard Visualization |
| GitHub     | Version Control         |

---

# Security Features

* Password hashing
* Prepared statements
* SQL injection prevention
* Session-based authentication

---

# Conclusion

The Smart Daily Activity Tracker database is designed using relational database concepts to efficiently manage user activity records.

The database schema ensures:

* Relational integrity
* Scalability
* Data consistency
* Efficient record management

The ERD and relational schema provide a strong foundation for the complete application.

---

# GitHub Commit Message

```text
M1: ERD and relational schema documentation added
```
