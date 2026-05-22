# Milestone 2 — Database Normalization (1NF to 3NF)

# Smart Daily Activity Tracker

## Database Systems Lab Semester Project

---

# 1. Introduction

This milestone focuses on normalizing the database schema of the Smart Daily Activity Tracker project.

Normalization is used to:

* Reduce data redundancy
* Improve data consistency
* Maintain relational integrity
* Improve scalability
* Organize data efficiently

The database schema was analyzed and normalized from First Normal Form (1NF) to Third Normal Form (3NF).

---

# 2. Database Tables

The project contains the following main tables:

1. users
2. prayers
3. study
4. skills

---

# 3. First Normal Form (1NF)

## Definition

A table is in First Normal Form (1NF) when:

* Each column contains atomic values
* No repeating groups exist
* Each record is unique

---

# USERS Table — 1NF

## Analysis

The users table stores:

* One name per row
* One email per row
* One password per row

No multi-valued attributes exist.

## Conclusion

The users table satisfies 1NF.

---

# PRAYERS Table — 1NF

## Analysis

The prayers table stores:

* One prayer record per row
* Atomic prayer completion values
* One prayer_date value per row

No repeating groups exist.

## Conclusion

The prayers table satisfies 1NF.

---

# STUDY Table — 1NF

## Analysis

The study table stores:

* One subject per row
* One study_date per row
* One completion status per row

No multi-valued attributes exist.

## Conclusion

The study table satisfies 1NF.

---

# SKILLS Table — 1NF

## Analysis

The skills table stores:

* One skill per row
* One skill_date per row
* One completion value per row

All values are atomic.

## Conclusion

The skills table satisfies 1NF.

---

# 4. Second Normal Form (2NF)

## Definition

A table is in Second Normal Form (2NF) when:

* It is already in 1NF
* No partial dependency exists
* All non-key attributes fully depend on the primary key

---

# USERS Table — 2NF

## Analysis

The users table uses:

```text
id
```

as a single-column primary key.

All attributes:

* name
* email
* password

fully depend on the primary key.

## Conclusion

The users table satisfies 2NF.

---

# PRAYERS Table — 2NF

## Analysis

The prayers table uses:

```text
id
```

as a single primary key.

All prayer-related attributes fully depend on the primary key.

No partial dependency exists.

## Conclusion

The prayers table satisfies 2NF.

---

# STUDY Table — 2NF

## Analysis

The study table uses:

```text
id
```

as the primary key.

All non-key attributes depend entirely on the primary key.

## Conclusion

The study table satisfies 2NF.

---

# SKILLS Table — 2NF

## Analysis

The skills table uses:

```text
id
```

as the primary key.

All non-key attributes fully depend on the primary key.

## Conclusion

The skills table satisfies 2NF.

---

# 5. Third Normal Form (3NF)

## Definition

A table is in Third Normal Form (3NF) when:

* It is already in 2NF
* No transitive dependency exists
* Non-key attributes depend only on the primary key

---

# USERS Table — 3NF

## Analysis

The users table stores only user-related information.

Non-key attributes:

* name
* email
* password

only depend on the primary key:

```text
id
```

No transitive dependency exists.

## Conclusion

The users table satisfies 3NF.

---

# PRAYERS Table — 3NF

## Analysis

The prayers table stores prayer tracking data only.

Prayer attributes do not depend on any other non-key attribute.

All attributes depend directly on:

```text
id
```

## Conclusion

The prayers table satisfies 3NF.

---

# STUDY Table — 3NF

## Analysis

The study table stores study tracking records only.

All attributes:

* subject_name
* completed
* study_date

only depend on the primary key.

No transitive dependency exists.

## Conclusion

The study table satisfies 3NF.

---

# SKILLS Table — 3NF

## Analysis

The skills table stores skill development records.

All non-key attributes depend only on the primary key.

No transitive dependency exists.

## Conclusion

The skills table satisfies 3NF.

---

# 6. Updated ERD

## Main Entities

1. Users
2. Prayers
3. Study
4. Skills

---

# Relationships

| Relationship    | Type        |
| --------------- | ----------- |
| Users → Prayers | One-to-Many |
| Users → Study   | One-to-Many |
| Users → Skills  | One-to-Many |

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

# 7. Justification for Normalization

The database schema was normalized to:

* Eliminate duplicate data
* Improve database consistency
* Maintain referential integrity
* Improve scalability and maintainability
* Support efficient SQL queries

The separation of:

* prayers
* study
* skills

into independent tables ensures better relational organization and prevents redundancy.

---

# 8. Future Improvement

Currently, the study timetable is hardcoded in the application layer.

In future versions, a separate timetable table can be introduced to allow dynamic user-customized study schedules.

Proposed Future Table:

```text
timetable
```

Possible Attributes:

* id
* user_id
* day_name
* subject_name
* start_time
* end_time

This improvement would further enhance normalization and scalability.

---

# 9. Conclusion

The Smart Daily Activity Tracker database schema successfully satisfies:

* First Normal Form (1NF)
* Second Normal Form (2NF)
* Third Normal Form (3NF)

The normalized schema improves:

* Data consistency
* Database integrity
* Scalability
* Maintainability

The database design provides a strong relational foundation for the complete application.

---

# 10. GitHub Commit Message

```text
M2: Normalization and updated ERD completed
```

---

# Suggested GitHub Folder Structure

```text
milestone 2/
├── normalization_report.docx
├── updated_ERD.png
├── normalization.md
└── README.md
```
