USE activity_tracker_db;

-- ROW COUNTS

SELECT COUNT(*) AS total_users
FROM users;

SELECT COUNT(*) AS total_prayers
FROM prayers;

SELECT COUNT(*) AS total_study_records
FROM study;

SELECT COUNT(*) AS total_skills
FROM skills;

-- NULL CHECKS

SELECT *
FROM users
WHERE name IS NULL
OR email IS NULL
OR password IS NULL;

SELECT *
FROM prayers
WHERE user_id IS NULL
OR prayer_date IS NULL;

SELECT *
FROM study
WHERE user_id IS NULL
OR subject_name IS NULL;

SELECT *
FROM skills
WHERE user_id IS NULL
OR skill_name IS NULL;

-- FOREIGN KEY INTEGRITY CHECK

SELECT
    prayers.id,
    users.name,
    prayers.prayer_date
FROM prayers
JOIN users
ON prayers.user_id = users.id;

SELECT
    study.id,
    users.name,
    study.subject_name
FROM study
JOIN users
ON study.user_id = users.id;

SELECT
    skills.id,
    users.name,
    skills.skill_name
FROM skills
JOIN users
ON skills.user_id = users.id;
