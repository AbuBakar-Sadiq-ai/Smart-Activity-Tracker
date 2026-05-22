# Data Flow Description

## Smart Daily Activity Tracker - Complete Data Flow Documentation

---

## Table of Contents
1. [System Overview](#system-overview)
2. [User Registration Data Flow](#user-registration-data-flow)
3. [Prayer Tracking Data Flow](#prayer-tracking-data-flow)
4. [Study Tracking Data Flow](#study-tracking-data-flow)
5. [Skills Tracking Data Flow](#skills-tracking-data-flow)
6. [Analytics Generation Data Flow](#analytics-generation-data-flow)
7. [Data Persistence Strategy](#data-persistence-strategy)
8. [CSV Import Flow](#csv-import-flow)

---

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                      SMART ACTIVITY TRACKER                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  User Input → Validation → Processing → Database → Display │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│                   DATA FLOW ARCHITECTURE                   │
│                                                             │
│  Frontend (HTML/JS) ↔ Backend (PHP) ↔ Database (MySQL)    │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

## User Registration Data Flow

### Flow Diagram

```
START: User Signup
        ↓
┌──────────────────────────────────────┐
│    1. INPUT COLLECTION               │
│  (User fills signup form)            │
├──────────────────────────────────────┤
│ Inputs:                              │
│ - username: "abubakar"              │
│ - email: "abubakar@example.com"    │
│ - password: "SecurePass123"         │
│ - first_name: "Abu"                 │
│ - last_name: "Bakar"                │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    2. CLIENT-SIDE VALIDATION         │
│    (JavaScript - auth/signup.php)    │
├──────────────────────────────────────┤
│ Checks:                              │
│ ✓ All fields not empty              │
│ ✓ Email format valid               │
│ ✓ Password length >= 8             │
│ ✓ Passwords match                  │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    3. FORM SUBMISSION                │
│    (POST request to authenticate.php)│
├──────────────────────────────────────┤
│ HTTP Method: POST                    │
│ Endpoint: auth/authenticate.php     │
│ Content-Type: application/x-www...  │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    4. SERVER-SIDE VALIDATION         │
│    (PHP - Input Sanitization)        │
├──────────────────────────────────────┤
│ ✓ Check all fields received         │
│ ✓ Trim whitespace                   │
│ ✓ Validate data types               │
│ ✓ Check field lengths               │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    5. DATABASE UNIQUENESS CHECK      │
│    (Prepared Statement Query)        │
├──────────────────────────────────────┤
│ Query: SELECT id FROM users         │
│        WHERE username = ?            │
│        OR email = ?                  │
│ Bindings: ["abubakar",              │
│          "abubakar@example.com"]    │
│ Result: No matches (VALID)          │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    6. PASSWORD HASHING               │
│    (Security Processing)             │
├──────────────────────────────────────┤
│ Algorithm: SHA-256                   │
│ Input: "SecurePass123"              │
│ Hash: "a3f5d8e2c1b9..."             │
│ Storage: Hashed value only          │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    7. DATABASE INSERTION             │
│    (INSERT INTO users)               │
├──────────────────────────────────────┤
│ Prepared Statement:                  │
│ INSERT INTO users (username,        │
│   email, password, first_name,      │
│   last_name, is_active)             │
│ VALUES (?, ?, ?, ?, ?, 1)           │
│ Status: SUCCESS                      │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    8. SESSION CREATION               │
│    (PHP Session Management)          │
├──────────────────────────────────────┤
│ $_SESSION['user_id'] = 1            │
│ $_SESSION['username'] = 'abubakar'  │
│ Session ID: <random_hash>           │
│ Session Timeout: 30 minutes         │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    9. REDIRECT TO DASHBOARD          │
│    (User authenticated)              │
├──────────────────────────────────────┤
│ Redirect: /dashboard/dashboard.php  │
│ HTTP Status: 302 Found              │
│ User authenticated: YES             │
└──────────────────────────────────────┘
        ↓
END: Registration Complete
```

### Data Transformation Steps

```
User Input:
{
  "username": "abubakar",
  "email": "abubakar@example.com",
  "password": "SecurePass123",
  "first_name": "Abu",
  "last_name": "Bakar"
}
        ↓ (Validation)
        ↓ (Hash Password)
{
  "username": "abubakar",
  "email": "abubakar@example.com",
  "password": "a3f5d8e2c1b9...",  [HASHED]
  "first_name": "Abu",
  "last_name": "Bakar",
  "is_active": 1,
  "created_at": "2024-05-20 10:30:45"
}
        ↓ (Insert into DB)
Database users Table:
{
  "id": 1,
  "username": "abubakar",
  "email": "abubakar@example.com",
  "password": "a3f5d8e2c1b9...",
  "first_name": "Abu",
  "last_name": "Bakar",
  "is_active": 1,
  "created_at": "2024-05-20 10:30:45"
}
```

---

## Prayer Tracking Data Flow

### Flow Diagram

```
START: User Logs Prayer
        ↓
┌──────────────────────────────────────┐
│    1. PRAYER SELECTION               │
│    (User Interface)                  │
├──────────────────────────────────────┤
│ User selects:                        │
│ ✓ Fajr (checked)                    │
│ ✓ Zuhar (checked)                   │
│ ✓ Asar (unchecked)                  │
│ ✓ Maghrib (checked)                 │
│ ✓ Isha (checked)                    │
│ ✓ Qaza count: 1                     │
│ ✓ Date: 2024-05-20                  │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    2. FORM VALIDATION                │
│    (Client-side JavaScript)          │
├──────────────────────────────────────┤
│ Checks:                              │
│ ✓ Date not in future                │
│ ✓ At least one prayer selected      │
│ ✓ Qaza count is numeric             │
│ ✓ Qaza >= 0                         │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    3. AJAX REQUEST                   │
│    (Asynchronous data submission)    │
├──────────────────────────────────────┤
│ Method: POST                         │
│ URL: /prayers/add_prayer.php        │
│ Data:                                │
│ {                                    │
│   prayer_date: "2024-05-20",       │
│   fajr: 1,                          │
│   zuhar: 1,                         │
│   asar: 0,                          │
│   maghrib: 1,                       │
│   isha: 1,                          │
│   qaza: 1                           │
│ }                                    │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    4. SERVER VALIDATION              │
│    (PHP - Input Validation)          │
├──────────────────────────────────────┤
│ Verify:                              │
│ ✓ User is authenticated             │
│ ✓ Session valid                     │
│ ✓ All required fields present       │
│ ✓ Boolean values valid (0 or 1)    │
│ ✓ Qaza is valid integer             │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    5. CALCULATION ENGINE             │
│    (Completion Percentage)           │
├──────────────────────────────────────┤
│ Formula:                             │
│ completed_count = 1+1+0+1+1 = 4    │
│ completion_% = (4 / 5) * 100       │
│ completion_% = 80.00               │
│                                      │
│ MySQL Generated Column:             │
│ GENERATED ALWAYS AS (               │
│   (fajr+zuhar+asar+maghrib+isha)   │
│   /5*100                           │
│ ) STORED                            │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    6. DATABASE INSERTION             │
│    (Prepared Statement)              │
├──────────────────────────────────────┤
│ Query:                               │
│ INSERT INTO prayers                 │
│   (user_id, prayer_date,           │
│    fajr, zuhar, asar,              │
│    maghrib, isha, qaza)            │
│ VALUES (?, ?, ?, ?, ?, ?, ?, ?)   │
│                                      │
│ Bindings:                            │
│ [1, "2024-05-20",                  │
│  1, 1, 0, 1, 1, 1]                 │
│                                      │
│ Result: INSERT SUCCESS              │
│ Last ID: 1                          │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    7. RESPONSE GENERATION            │
│    (JSON Response)                   │
├──────────────────────────────────────┤
│ {                                    │
│   "success": true,                  │
│   "message": "Prayer logged!",     │
│   "data": {                         │
│     "id": 1,                        │
│     "completion_percentage": 80.00, │
│     "timestamp": "10:30:45"        │
│   }                                 │
│ }                                    │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    8. CLIENT-SIDE UPDATE             │
│    (JavaScript DOM manipulation)     │
├──────────────────────────────────────┤
│ ✓ Parse JSON response               │
│ ✓ Update UI elements               │
│ ✓ Display success message          │
│ ✓ Refresh prayer list              │
│ ✓ Update statistics                │
│ ✓ Trigger chart refresh            │
└──────────────────────────────────────┘
        ↓
┌──────────────────────────────────────┐
│    9. CHART.JS REFRESH               │
│    (Data Visualization)              │
├──────────────────────────────────────┤
│ ✓ Query database for updated data  │
│ ✓ Re-render pie chart              │
│ ✓ Update completion percentage     │
│ ✓ Display visual feedback          │
└──────────────────────────────────────┘
        ↓
END: Prayer Logged Successfully
```

### Data Structure in Database

```
Before Insertion:
prayers table is empty for user_id=1 on 2024-05-20

After Insertion:
prayers Table Row:
┌─────┬─────────┬───────────────┬────┬────┬────┬────────┬────┬────┬──────────────┐
│ id  │ user_id │ prayer_date   │fajr│zuhr│asar│maghrib │isha│qaza│completion_% │
├─────┼─────────┼───────────────┼────┼────┼────┼────────┼────┼────┼──────────────┤
│  1  │    1    │  2024-05-20   │ 1  │ 1  │ 0  │   1    │ 1  │ 1  │   80.00      │
└─────┴─────────┴───────────────┴────┴────┴────┴────────┴────┴────┴──────────────┘
```

---

## Study Tracking Data Flow

### Flow Diagram

```
START: User Logs Study Session
        ↓
┌──────────────────────────────────────────────┐
│    1. STUDY INPUT                            │
│    (User fills study session form)           │
├──────────────────────────────────────────────┤
│ Inputs:                                      │
│ - subject: "Mathematics"                     │
│ - duration_minutes: 60                       │
│ - progress_percentage: 85                    │
│ - status: "Completed"                        │
│ - notes: "Completed chapters 5-7"           │
│ - study_date: 2024-05-20                     │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    2. VALIDATION                             │
│    (Frontend & Backend)                      │
├──────────────────────────────────────────────┤
│ Frontend:                                    │
│ ✓ Subject not empty                         │
│ ✓ Duration > 0                              │
│ ✓ Progress 0-100                            │
│ ✓ Valid status enum                         │
│                                              │
│ Backend:                                     │
│ ✓ User authenticated                        │
│ ✓ Valid date range                          │
│ ✓ Data type checking                        │
│ ✓ Constraint validation                     │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    3. DATABASE INSERTION                     │
│    (INSERT INTO study)                       │
├──────────────────────────────────────────────┤
│ Prepared Statement:                          │
│ INSERT INTO study                           │
│   (user_id, study_date, subject,            │
│    duration_minutes, progress_%,            │
│    status, notes)                           │
│ VALUES (?, ?, ?, ?, ?, ?, ?)               │
│                                              │
│ Result: SUCCESS                              │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    4. ANALYTICS UPDATE                       │
│    (Cache invalidation for dashboard)        │
├──────────────────────────────────────────────┤
│ ✓ Total study hours updated                 │
│ ✓ Subject totals recalculated               │
│ ✓ Weekly statistics updated                 │
│ ✓ Dashboard cache invalidated               │
└──────────────────────────────────────────────┘
        ↓
END: Study Session Recorded
```

### Data Structure in Database

```
study Table Row:
┌─────┬─────────┬────────────┬───────────┬──────────┬────────┬──────────┬──────────────────┐
│ id  │user_id  │study_date  │ subject   │duration  │progress│ status   │ notes            │
├─────┼─────────┼────────────┼───────────┼──────────┼────────┼──────────┼──────────────────┤
│  1  │    1    │2024-05-20  │Mathematics│   60     │  85.00 │Completed │Chapters 5-7      │
└─────┴─────────┴────────────┴───────────┴──────────┴────────┴──────────┴──────────────────┘
```

---

## Skills Tracking Data Flow

### Flow Diagram

```
START: User Logs Skill
        ↓
┌──────────────────────────────────────────────┐
│    1. SKILL INPUT                            │
│    (User fills skill tracking form)          │
├──────────────────────────────────────────────┤
│ Inputs:                                      │
│ - skill_name: "Python Programming"          │
│ - proficiency_level: "Intermediate"         │
│ - progress_percentage: 65                    │
│ - notes: "Working on OOP concepts"         │
│ - skill_date: 2024-05-20                     │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    2. VALIDATION                             │
│    (Input validation)                        │
├──────────────────────────────────────────────┤
│ ✓ Skill name not empty                      │
│ ✓ Valid proficiency enum                    │
│ ✓ Progress 0-100 range                      │
│ ✓ Valid date (not future)                   │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    3. DATABASE INSERTION                     │
│    (INSERT INTO skills)                      │
├──────────────────────────────────────────────┤
│ INSERT INTO skills                          │
│   (user_id, skill_name, skill_date,        │
│    proficiency_level, progress_%)          │
│ VALUES (?, ?, ?, ?, ?)                     │
│                                              │
│ Result: SUCCESS                              │
└──────────────────────────────────────────────┘
        ↓
END: Skill Recorded
```

---

## Analytics Generation Data Flow

### Flow Diagram

```
START: User Views Dashboard
        ↓
┌──────────────────────────────────────────────┐
│    1. DASHBOARD REQUEST                      │
│    (User navigates to dashboard)             │
├──────────────────────────────────────────────┤
│ ✓ Check user authentication                 │
│ ✓ Validate session                          │
│ ✓ Load dashboard.php                        │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    2. AJAX ANALYTICS REQUEST                 │
│    (JavaScript requests data)                │
├──────────────────────────────────────────────┤
│ URL: /dashboard/get_analytics.php           │
│ Method: GET                                  │
│ User ID: Passed via session                 │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    3. AGGREGATION QUERIES                    │
│    (Backend calculates analytics)            │
├──────────────────────────────────────────────┤
│ Query 1: Prayer completion rate             │
│ Query 2: Study hours summary                │
│ Query 3: Skills proficiency distribution    │
│ Query 4: Productivity score                 │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    4. CALCULATION ENGINE                     │
│    (Process aggregated data)                 │
├──────────────────────────────────────────────┤
│ Prayer Completion: (4/5)*100 = 80%         │
│ Study Hours: 180 minutes = 3 hours         │
│ Skills Average: (80+65+70)/3 = 71.67%     │
│ Productivity Score: (80*0.3 + 75*0.4 + ... │
│ = 76.5                                       │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    5. JSON FORMATTING                        │
│    (Prepare response)                        │
├──────────────────────────────────────────────┤
│ {                                            │
│   "prayer_completion": 80.00,               │
│   "study_hours": 3,                         │
│   "skills_count": 3,                        │
│   "productivity_score": 76.5,               │
│   "prayer_data": [...],                     │
│   "study_data": [...],                      │
│   "skills_data": [...]                      │
│ }                                            │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    6. FRONTEND RECEPTION                     │
│    (JavaScript receives JSON)                │
├──────────────────────────────────────────────┤
│ ✓ Parse JSON data                           │
│ ✓ Validate data structure                   │
│ ✓ Store in JavaScript variables             │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    7. CHART.JS RENDERING                     │
│    (Create visualizations)                   │
├──────────────────────────────────────────────┤
│ Pie Chart: Prayer completion breakdown      │
│ Bar Chart: Study hours by subject           │
│ Line Chart: Skills progression              │
│ Summary Cards: Key metrics                  │
└──────────────────────────────────────────────┘
        ↓
┌──────────────────────────────────────────────┐
│    8. DISPLAY DASHBOARD                      │
│    (User views analytics)                    │
├──────────────────────────────────────────────┤
│ ✓ Charts rendered                           │
│ ✓ Metrics displayed                         │
│ ✓ Real-time data shown                      │
│ ✓ Interactive elements functional           │
└──────────────────────────────────────────────┘
        ↓
END: Dashboard Ready
```

---

## Data Persistence Strategy

### Transaction Management

```
User Action → Begin Transaction
                    ↓
            Execute SQL Queries
                    ↓
        Validation Checks (Success?)
            ↓ YES        ↓ NO
         COMMIT        ROLLBACK
            ↓             ↓
        Data Saved    Data Unchanged
            ↓             ↓
        Return Success  Return Error
```

### Error Handling Flow

```
Try Execution
    ↓
Exception Occurs?
    ├─ YES: Catch Exception
    │        ├─ Log Error
    │        ├─ ROLLBACK Transaction
    │        ├─ Return Error Message
    │        └─ Display to User
    │
    └─ NO: Continue
             ├─ COMMIT Transaction
             ├─ Return Success
             └─ Display Confirmation
```

---

## CSV Import Flow

### Data Population Process

```
CSV Files
├─ users.csv (5 records)
├─ prayers.csv (35 records)
├─ study.csv (30 records)
└─ skills.csv (20 records)
        ↓
Phase 1: Load Users
    sql/dml/01_insert_sample_users.sql
        ↓
Phase 2: Load Prayers
    sql/dml/02_insert_prayer_data.sql
        ↓
Phase 3: Load Study
    sql/dml/03_insert_study_data.sql
        ↓
Phase 4: Load Skills
    sql/dml/04_insert_skills_data.sql
        ↓
Phase 5: Validation
    sql/dml/06_validation_queries.sql
        ↓
Database Populated: Ready for Testing
```

---

**Last Updated:** May 2026  
**Version:** 1.0.0  
**Status:** Complete Data Flow Documentation
