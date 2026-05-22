# DDL Report — Smart Daily Activity Tracker

## Introduction

This milestone focuses on implementing the relational database schema using SQL DDL statements.

The schema was implemented in MySQL using:

* CREATE TABLE
* PRIMARY KEY
* FOREIGN KEY
* NOT NULL
* CHECK constraints
* INDEXES

---

# Database Name

```sql
activity_tracker_db
```

---

# Tables Created

1. users
2. prayers
3. study
4. skills

---

# Constraints Implemented

## Primary Keys

Each table contains:

```text
id
```

as AUTO_INCREMENT PRIMARY KEY.

---

## Foreign Keys

Foreign key relationships:

```text
prayers.user_id → users.id
study.user_id → users.id
skills.user_id → users.id
```

---

## NOT NULL Constraints

Important attributes such as:

* name
* email
* password
* prayer_date
* study_date
* skill_date

cannot contain NULL values.

---

## CHECK Constraints

Boolean tracking fields:

* fajr
* zuhar
* asar
* maghrib
* isha
* qaza
* completed

only allow:

```text
0 or 1
```

---

## INDEXES

Indexes were created on:

```text
user_id
```

to improve query performance.

---

# Database Verification

The schema was verified using MySQL Workbench and the EER diagram matches the implemented DDL scripts.

---

# Conclusion

The database schema successfully implements relational database principles while ensuring:

* Data integrity
* Referential consistency
* Efficient querying
* Scalability

The DDL implementation provides a strong foundation for future DML operations and application development.
