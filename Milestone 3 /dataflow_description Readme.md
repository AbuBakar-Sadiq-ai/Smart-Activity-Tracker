# Dataflow Description

## Data Input

Data enters the Smart Daily Activity Tracker system through frontend forms developed using HTML, CSS, and JavaScript.

Users perform the following actions:

* Register/Login
* Mark prayers as completed
* Track study subjects
* Track skill progress

The frontend sends user input data to the backend PHP application.

---

# Data Processing

PHP scripts receive user input and perform:

* Validation
* Authentication
* Session management
* SQL query execution

The backend processes the data and executes:

* INSERT queries
* UPDATE queries
* SELECT queries
* DELETE queries

The system stores records into the MySQL database tables:

* users
* prayers
* study
* skills

All activity tables reference:

```text
user_id
```

to maintain relational integrity.

---

# Data Storage

The processed data is stored inside:

```text
activity_tracker_db
```

Database records are organized using relational database principles.

Foreign key relationships ensure consistency between users and activity records.

---

# Data Output

The dashboard retrieves stored records using SQL queries and displays:

* Prayer completion statistics
* Study progress
* Skill progress
* Productivity analytics

Chart.js is used for visual representation of user productivity data.

---

# System Flow Summary

```text
User Input
    ↓
Frontend Forms
    ↓
PHP Backend Processing
    ↓
MySQL Database Storage
    ↓
Dashboard Analytics & Charts
```
