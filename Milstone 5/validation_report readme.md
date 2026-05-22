# Validation Report — Smart Daily Activity Tracker

## Introduction

This milestone focuses on populating the database and validating relational integrity using SQL DML operations and verification queries.

---

# Tables Populated

1. users
2. prayers
3. study
4. skills

---

# Operations Performed

## INSERT Operations

Sample records were inserted into all database tables.

---

## UPDATE Operation

The following update operation was performed:

```sql id="m5u1"
UPDATE study
SET completed = 1
WHERE id = 3;
```

This operation updated the completion status of a study record.

---

## DELETE Operation

The following delete operation was performed:

```sql id="m5d1"
DELETE FROM skills
WHERE id = 3;
```

This operation removed a skill record from the database.

---

# Validation Queries

The following validations were performed:

## Row Count Validation

COUNT(*) queries were executed to verify successful data insertion.

---

## NULL Value Checks

Queries checked for NULL values in important columns.

No invalid NULL values were found.

---

## Foreign Key Integrity Checks

JOIN queries confirmed valid relationships between:

* users
* prayers
* study
* skills

Relational integrity was successfully maintained.

---

# Conclusion

The database was successfully populated using SQL DML statements.

All validations confirmed:

* Correct row insertion
* Proper foreign key relationships
* No invalid NULL values
* Successful UPDATE and DELETE operations

The database is now fully operational and ready for application use.
