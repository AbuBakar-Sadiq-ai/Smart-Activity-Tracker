# Normalization Walkthrough

## Smart Daily Activity Tracker - Complete 1NF, 2NF, 3NF Analysis

---

## Table of Contents
1. [Overview](#overview)
2. [First Normal Form (1NF)](#first-normal-form-1nf)
3. [Second Normal Form (2NF)](#second-normal-form-2nf)
4. [Third Normal Form (3NF)](#third-normal-form-3nf)
5. [Normalization Decisions](#normalization-decisions)

---

## Overview

### What is Normalization?

Normalization is a systematic approach to organizing data in a database to eliminate redundancy and improve data integrity. The Smart Daily Activity Tracker database has been designed to satisfy 1NF, 2NF, and 3NF requirements.

### Design Goal

```
Eliminate:
✓ Data Redundancy
✓ Update Anomalies
✓ Insertion Anomalies
✓ Deletion Anomalies

Achieve:
✓ Data Consistency
✓ Referential Integrity
✓ Query Efficiency
✓ Scalability
```

---

## First Normal Form (1NF)

### Definition

A relation is in 1NF if:
1. All attributes contain **atomic (indivisible) values**
2. No **repeating groups** of columns
3. Each row contains a **unique identifier** (primary key)
4. Each cell contains **single values only**

### Initial Problem (Before 1NF)

```
❌ UNNORMALIZED STRUCTURE

PrayerLog Table:
┌────┬────────┬────────────┬─────────────────────────────────┐
│ id │user_id │prayer_date │ prayers (REPEATING GROUP)      │
├────┼────────┼────────────┼─────────────────────────────────┤
│ 1  │   1    │ 2024-05-20 │ Fajr, Zuhar, Asar, Maghrib    │
│ 2  │   1    │ 2024-05-21 │ Fajr, Isha                    │
└────┴────────┴────────────┴─────────────────────────────────┘

Problems:
- "prayers" column is multivalued (repeating group)
- Difficult to query individual prayers
- Cannot efficiently search for specific prayers
- Space inefficient with repeated data
```

### Solution (After 1NF)

```
✅ 1NF STRUCTURE

prayers Table:
┌────┬─────────┬───────────────┬────┬────┬────┬────────┬────┬────┐
│ id │user_id  │ prayer_date   │fajr│zuhr│asar│maghrib │isha│qaza│
├────┼─────────┼───────────────┼────┼────┼────┼────────┼────┼────┤
│ 1  │    1    │  2024-05-20   │ 1  │ 1  │ 0  │   1    │ 0  │ 0  │
│ 2  │    1    │  2024-05-21   │ 1  │ 0  │ 0  │   0    │ 1  │ 1  │
└────┴─────────┴───────────────┴────┴────┴────┴────────┴────┴────┘

Features:
✓ All columns have atomic values (boolean: 0 or 1)
✓ No repeating groups
✓ Primary key: (id)
✓ Each row uniquely identified
✓ Each column contains single values only
```

### 1NF Compliance Check for All Tables

#### **users Table**

```
✅ IN 1NF

Structure:
┌────────────┬─────────────┬────────────────────┐
│ Column     │ Data Type   │ Atomic?            │
├────────────┼─────────────┼────────────────────┤
│ id         │ INT         │ ✓ Yes (PK)         │
│ username   │ VARCHAR(50) │ ✓ Yes (Single)     │
│ email      │ VARCHAR(100)│ ✓ Yes (Single)     │
│ password   │ VARCHAR(255)│ ✓ Yes (Single)     │
│ first_name │ VARCHAR(50) │ ✓ Yes (Single)     │
│ last_name  │ VARCHAR(50) │ ✓ Yes (Single)     │
│ created_at │ TIMESTAMP   │ ✓ Yes (Single)     │
│ is_active  │ BOOLEAN     │ ✓ Yes (Single)     │
└────────────┴─────────────┴────────────────────┘

✓ No repeating groups
✓ All values atomic
✓ Primary key exists: id
✓ IN 1NF COMPLIANCE
```

#### **prayers Table**

```
✅ IN 1NF

Structure:
┌──────────────────────┬─────────────┬──────────────────────────┐
│ Column               │ Data Type   │ Atomic?                  │
├──────────────────────┼─────────────┼──────────────────────────┤
│ id                   │ INT         │ ✓ Yes (PK)               │
│ user_id              │ INT         │ ✓ Yes (FK)               │
│ prayer_date          │ DATE        │ ✓ Yes (Single date)      │
│ fajr                 │ BOOLEAN     │ ✓ Yes (Single value)     │
│ zuhar                │ BOOLEAN     │ ✓ Yes (Single value)     │
│ asar                 │ BOOLEAN     │ ✓ Yes (Single value)     │
│ maghrib              │ BOOLEAN     │ ✓ Yes (Single value)     │
│ isha                 │ BOOLEAN     │ ✓ Yes (Single value)     │
│ qaza                 │ INT         │ ✓ Yes (Single integer)   │
│ completion_%         │ DECIMAL     │ ✓ Yes (Computed value)   │
│ created_at           │ TIMESTAMP   │ ✓ Yes (Single timestamp) │
└──────────────────────┴─────────────┴──────────────────────────┘

✓ No repeating groups
✓ All values atomic (not multivalue)
✓ Primary key exists: id
✓ Foreign key: user_id
✓ IN 1NF COMPLIANCE
```

#### **study Table**

```
✅ IN 1NF

Structure:
┌──────────────────┬─────────────┬──────────────────────┐
│ Column           │ Data Type   │ Atomic?              │
├──────────────────┼─────────────┼──────────────────────┤
│ id               │ INT         │ ✓ Yes (PK)           │
│ user_id          │ INT         │ ✓ Yes (FK)           │
│ study_date       │ DATE        │ ✓ Yes (Single date)  │
│ subject          │ VARCHAR(100)│ ✓ Yes (Single value) │
│ duration_minutes │ INT         │ ✓ Yes (Single value) │
│ progress_%       │ DECIMAL     │ ✓ Yes (Single value) │
│ status           │ ENUM        │ ✓ Yes (Enum type)    │
│ notes            │ TEXT        │ ✓ Yes (Single text)  │
│ created_at       │ TIMESTAMP   │ ✓ Yes (Single value) │
└──────────────────┴─────────────┴──────────────────────┘

✓ No repeating groups
✓ All values atomic
✓ IN 1NF COMPLIANCE
```

#### **skills Table**

```
✅ IN 1NF

Structure:
┌──────────────────────┬─────────────┬────────────────────────┐
│ Column               │ Data Type   │ Atomic?                │
├──────────────────────┼─────────────┼────────────────────────┤
│ id                   │ INT         │ ✓ Yes (PK)             │
│ user_id              │ INT         │ ✓ Yes (FK)             │
│ skill_name           │ VARCHAR(100)│ ✓ Yes (Single value)   │
│ skill_date           │ DATE        │ ✓ Yes (Single date)    │
│ proficiency_level    │ ENUM        │ ✓ Yes (Enum value)     │
│ progress_%           │ DECIMAL     │ ✓ Yes (Single value)   │
│ notes                │ TEXT        │ ✓ Yes (Single text)    │
│ created_at           │ TIMESTAMP   │ ✓ Yes (Single value)   │
└──────────────────────┴─────────────┴────────────────────────┘

✓ No repeating groups
✓ All values atomic
✓ IN 1NF COMPLIANCE
```

---

## Second Normal Form (2NF)

### Definition

A relation is in 2NF if:
1. It is in **1NF** ✓
2. Every **non-key attribute** is **fully dependent** on the **entire primary key** (no partial dependencies)
3. There are **no partial dependencies** from composite keys

### Key Concepts

```
Partial Dependency: A non-key attribute depends on only part of 
                    a composite primary key

Example of PARTIAL DEPENDENCY (BAD):
Table: OrderDetails
PK: (OrderID, ProductID)
  - OrderDate depends only on OrderID (partial)
  - ProductPrice depends only on ProductID (partial)

Solution: Split into separate tables
```

### Initial Problem (Before 2NF)

```
❌ PROBLEMATIC STRUCTURE (with composite key)

UserPrayers Table:
PK: (user_id, prayer_date)
┌─────────┬────────────┬──────────┬─────────────┐
│user_id  │prayer_date │username  │email        │
├─────────┼────────────┼──────────┼─────────────┤
│   1     │ 2024-05-20 │abubakar  │abubakar@... │
│   1     │ 2024-05-21 │abubakar  │abubakar@... │
└─────────┴────────────┴──────────┴─────────────┘

PARTIAL DEPENDENCY DETECTED:
- username depends on (user_id) only, NOT on (user_id, prayer_date)
- email depends on (user_id) only, NOT on (user_id, prayer_date)
- This violates 2NF

Problem: 
- Redundant user data repeated for each prayer
- Wastes storage
- Update anomalies possible
```

### Solution (After 2NF)

```
✅ 2NF STRUCTURE (Separate tables)

users Table:
PK: user_id
┌─────────┬──────────┬─────────────┐
│user_id  │username  │email        │
├─────────┼──────────┼─────────────┤
│   1     │abubakar  │abubakar@... │
└─────────┴──────────┴─────────────┘

prayers Table:
PK: id
FK: user_id
┌────┬─────────┬────────────┬──────┐
│id  │user_id  │prayer_date │fajr │
├────┼─────────┼────────────┼──────┤
│1   │   1     │ 2024-05-20 │ 1   │
│2   │   1     │ 2024-05-21 │ 1   │
└────┴─────────┴────────────┴──────┘

DEPENDENCIES:
- fajr depends on (id) - fully dependent on PK ✓
- user_id depends on (id) - FK reference ✓
- No partial dependencies

Result:
✓ Eliminates redundancy
✓ Reduces storage
✓ Prevents update anomalies
✓ IN 2NF COMPLIANCE
```

### 2NF Analysis for All Tables

#### **users Table**

```
✅ IN 2NF

Primary Key: id (single column, NO partial dependency possible)

Attributes:
- username: Depends on (id) ✓
- email: Depends on (id) ✓
- password: Depends on (id) ✓
- first_name: Depends on (id) ✓
- last_name: Depends on (id) ✓
- created_at: Depends on (id) ✓
- is_active: Depends on (id) ✓

Result:
✓ Single primary key (no composite key)
✓ All attributes fully depend on PK
✓ No partial dependencies
✓ IN 2NF COMPLIANCE
```

#### **prayers Table**

```
✅ IN 2NF

Primary Key: id (single column)

Attributes:
- user_id (FK): References users table ✓
- prayer_date: Depends on (id) ✓
- fajr, zuhar, asar, etc.: Depend on (id) ✓
- qaza: Depends on (id) ✓
- completion_%: Depends on (id) ✓
- created_at: Depends on (id) ✓

Result:
✓ Single primary key
✓ All attributes fully depend on PK
✓ No partial dependencies
✓ User info NOT duplicated (separate table)
✓ IN 2NF COMPLIANCE
```

#### **study Table**

```
✅ IN 2NF

Primary Key: id (single column)

Attributes:
- user_id (FK): References users table ✓
- study_date: Depends on (id) ✓
- subject: Depends on (id) ✓
- duration_minutes: Depends on (id) ✓
- progress_%: Depends on (id) ✓
- status: Depends on (id) ✓
- notes: Depends on (id) ✓

Result:
✓ Single primary key
✓ All attributes fully dependent
✓ No partial dependencies
✓ IN 2NF COMPLIANCE
```

#### **skills Table**

```
✅ IN 2NF

Primary Key: id (single column)

Attributes:
- user_id (FK): References users table ✓
- skill_name: Depends on (id) ✓
- skill_date: Depends on (id) ✓
- proficiency_level: Depends on (id) ✓
- progress_%: Depends on (id) ✓
- notes: Depends on (id) ✓

Result:
✓ Single primary key
✓ All attributes fully dependent
✓ No partial dependencies
✓ IN 2NF COMPLIANCE
```

---

## Third Normal Form (3NF)

### Definition

A relation is in 3NF if:
1. It is in **2NF** ✓
2. Every **non-key attribute** is **non-transitively dependent** on the **primary key**
3. There are **NO transitive dependencies** (no functional dependency between non-key attributes)

### Key Concepts

```
Transitive Dependency: A non-key attribute B depends on 
                       another non-key attribute A, which 
                       depends on the primary key

Example of TRANSITIVE DEPENDENCY (BAD):
Table: StudentCourses
PK: student_id
- CourseID (non-key) → CourseName (non-key)
- Violates 3NF

Solution: Create separate Course table
```

### Initial Problem (Before 3NF)

```
❌ PROBLEMATIC STRUCTURE (transitive dependency)

StudyLog Table:
PK: id
FK: user_id
┌────┬─────────┬──────────┬─────────────┬────────────────┐
│id  │user_id  │date      │subject      │subject_teacher│
├────┼─────────┼──────────┼─────────────┼────────────────┤
│1   │   1     │2024-05-20│Mathematics  │Mr. Ahmed       │
│2   │   1     │2024-05-21│Mathematics  │Mr. Ahmed       │
└────┴─────────┴──────────┴─────────────┴────────────────┘

TRANSITIVE DEPENDENCY DETECTED:
- id → subject (depends on PK) ✓
- subject → subject_teacher (non-key → non-key)
- id → subject_teacher (TRANSITIVE)

Problems:
- Redundant teacher info repeated
- If teacher changes, need multiple updates
- Update anomalies possible
- Storage waste
```

### Solution (After 3NF)

```
✅ 3NF STRUCTURE (Eliminate transitive dependency)

study Table:
PK: id
FK: user_id
┌────┬─────────┬──────────┬──────────────┐
│id  │user_id  │date      │subject       │
├────┼─────────┼──────────┼──────────────┤
│1   │   1     │2024-05-20│Mathematics   │
│2   │   1     │2024-05-21│Mathematics   │
└────┴─────────┴──────────┴──────────────┘

(Note: Subject teacher would be in separate table if needed)

Result:
✓ No transitive dependencies
✓ Each non-key attribute depends ONLY on PK
✓ Eliminates redundancy
✓ Prevents anomalies
✓ IN 3NF COMPLIANCE
```

### 3NF Analysis for All Tables

#### **users Table**

```
✅ IN 3NF

Primary Key: id

Dependency Check:
- id → username ✓ (depends on PK)
- id → email ✓ (depends on PK)
- id → password ✓ (depends on PK)
- id → first_name ✓ (depends on PK)
- id → last_name ✓ (depends on PK)
- id → created_at ✓ (depends on PK)

NO TRANSITIVE DEPENDENCIES:
- username ↛ email (no dependency)
- first_name ↛ last_name (no dependency)
- No non-key attribute depends on another non-key attribute

Result:
✓ All attributes depend only on PK
✓ No transitive dependencies
✓ IN 3NF COMPLIANCE
```

#### **prayers Table**

```
✅ IN 3NF

Primary Key: id

Dependency Check:
- id → user_id ✓ (FK reference)
- id → prayer_date ✓ (depends on PK)
- id → fajr ✓ (depends on PK)
- id → completion_% ✓ (computed from PK)

NO TRANSITIVE DEPENDENCIES:
- prayer_date ↛ fajr (no dependency)
- fajr ↛ zuhar (no dependency)
- No non-key to non-key dependency

Result:
✓ No computed values causing circular dependencies
✓ Completion_% calculated FROM prayer columns, not stored redundantly
✓ IN 3NF COMPLIANCE
```

#### **study Table**

```
✅ IN 3NF

Primary Key: id

Dependency Check:
- id → user_id ✓ (FK reference)
- id → study_date ✓ (depends on PK)
- id → subject ✓ (depends on PK)
- id → duration_minutes ✓ (depends on PK)
- id → progress_% ✓ (depends on PK)
- id → status ✓ (depends on PK)
- id → notes ✓ (depends on PK)

NO TRANSITIVE DEPENDENCIES:
- subject ↛ duration (no dependency)
- progress_% ↛ status (no dependency)
- No non-key attribute transitively depends on another

Result:
✓ All fields depend only on PK
✓ No transitive dependencies
✓ IN 3NF COMPLIANCE
```

#### **skills Table**

```
✅ IN 3NF

Primary Key: id

Dependency Check:
- id → user_id ✓ (FK reference)
- id → skill_name ✓ (depends on PK)
- id → skill_date ✓ (depends on PK)
- id → proficiency_level ✓ (depends on PK)
- id → progress_% ✓ (depends on PK)
- id → notes ✓ (depends on PK)

NO TRANSITIVE DEPENDENCIES:
- skill_name ↛ proficiency_level (no dependency)
- progress_% ↛ skill_date (no dependency)
- No field transitively depends on another non-key field

Result:
✓ All attributes depend only on PK
✓ No transitive dependencies
✓ IN 3NF COMPLIANCE
```

---

## Normalization Decisions

### Design Decisions Explained

#### **Decision 1: Separate users Table**

```
Rationale:
✓ User information is independent of activities
✓ One user can have many prayers/studies/skills
✓ Prevents redundancy (don't repeat user info)
✓ Supports referential integrity via FK
✓ Enables authentication and session management

Result:
- users table stores user-specific data only
- Normalized to 3NF
- Efficient queries
```

#### **Decision 2: Individual Boolean Columns for Prayers**

```
ALTERNATIVE (BAD):
prayers Table with comma-separated values:
┌────┬─────────┬────────────┬──────────────────────┐
│id  │user_id  │prayer_date │prayer_list           │
├────┼─────────┼────────────┼──────────────────────┤
│1   │   1     │ 2024-05-20 │Fajr,Zuhar,Maghrib   │
└────┴─────────┴────────────┴──────────────────────┘

Problems:
✗ Text parsing required
✗ Difficult to query specific prayers
✗ Not in 1NF (violates atomic values)

CHOSEN (GOOD):
prayers Table with boolean columns:
┌────┬─────────┬────────────┬────┬────┬────┬────────┐
│id  │user_id  │prayer_date │fajr│zuhr│asar│maghrib │
├────┼─────────┼────────────┼────┼────┼────┼────────┤
│1   │   1     │ 2024-05-20 │ 1  │ 1  │ 0  │   1    │
└────┴─────────┴────────────┴────┴────┴────┴────────┘

Advantages:
✓ In 1NF (atomic values)
✓ Easy to query individual prayers
✓ Efficient indexing
✓ Direct boolean logic
```

#### **Decision 3: Computed Column for Completion Percentage**

```
OPTION 1: Store completion_% directly
- Redundant data (derived from prayer values)
- Update anomalies (must update if prayer changes)
- Storage waste
- NOT IDEAL

OPTION 2: Compute on query time (CHOSEN)
SQL: (fajr + zuhar + asar + maghrib + isha) / 5 * 100

MySQL Implementation:
ALTER TABLE prayers
ADD COLUMN completion_% DECIMAL(5,2)
GENERATED ALWAYS AS (
  (fajr + zuhar + asar + maghrib + isha) / 5.0 * 100
) STORED;

Benefits:
✓ Eliminates redundancy
✓ Always in sync with source data
✓ No update anomalies
✓ Efficient calculation
✓ Normalized design
```

#### **Decision 4: Foreign Key References**

```
Database Schema:

users (1) ──────┬──────── (Many) prayers
                ├──────── (Many) study
                └──────── (Many) skills

Constraints:
- prayers.user_id → users.id
- study.user_id → users.id
- skills.user_id → users.id

Benefits:
✓ Enforces referential integrity
✓ Prevents orphaned records
✓ Database maintains consistency
✓ Automatic cascade operations if needed
```

#### **Decision 5: Date Handling**

```
CHOSEN APPROACH:

users Table:
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

prayers Table:
- prayer_date (DATE) - the date of the prayer
- created_at (TIMESTAMP) - when record was inserted

study Table:
- study_date (DATE) - the date studied
- created_at (TIMESTAMP) - when logged

skills Table:
- skill_date (DATE) - when skill milestone achieved
- created_at (TIMESTAMP) - when record inserted

Rationale:
✓ DATE sufficient for logging dates (no time needed)
✓ TIMESTAMP for audit trail (when created)
✓ Separate concerns (activity date vs. record date)
✓ Enables historical tracking
```

---

## Summary of Normalization Compliance

```
┌────────────────────────────────────────────────────┐
│        NORMALIZATION COMPLIANCE SUMMARY            │
├────────────────────────────────────────────────────┤
│                                                    │
│  TABLE          │  1NF  │  2NF  │  3NF  │ STATUS  │
│  ─────────────────────────────────────────────    │
│  users          │  ✓    │  ✓    │  ✓    │  3NF   │
│  prayers        │  ✓    │  ✓    │  ✓    │  3NF   │
│  study          │  ✓    │  ✓    │  ✓    │  3NF   │
│  skills         │  ✓    │  ✓    │  ✓    │  3NF   │
│                                                    │
├────────────────────────────────────────────────────┤
│  DATABASE STATUS: ✅ FULLY NORMALIZED (3NF)      │
└────────────────────────────────────────────────────┘
```

---

## Design Benefits

### Achieved Normalization Benefits

```
1. ✅ REDUCED REDUNDANCY
   - No repeated user data across tables
   - Each fact stored once
   - Minimal storage overhead

2. ✅ DATA CONSISTENCY
   - Single source of truth
   - No conflicting values
   - Referential integrity maintained

3. ✅ EFFICIENT UPDATES
   - Change prayer data once, reflected everywhere
   - No cascading updates needed
   - Transactional safety

4. ✅ FLEXIBLE QUERYING
   - Easy to join tables
   - Can select any combination of data
   - Supports complex analytics

5. ✅ SCALABILITY
   - Can add new users/records without redesign
   - Grows gracefully
   - Performance remains optimal

6. ✅ INTEGRITY PRESERVATION
   - Foreign keys prevent orphaned records
   - Database enforces business rules
   - Automatic cascade handling
```

---

**Last Updated:** May 2026  
**Version:** 1.0.0  
**Certification:** Database Fully Normalized to 3NF
