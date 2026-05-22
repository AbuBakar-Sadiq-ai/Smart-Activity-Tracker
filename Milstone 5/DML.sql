USE activity_tracker_db;

-- INSERT USERS

INSERT INTO users (name, email, password)
VALUES
('Ali', 'ali@gmail.com', 'hashed_password_1'),
('Ahmad', 'ahmad@gmail.com', 'hashed_password_2'),
('Usman', 'usman@gmail.com', 'hashed_password_3');

-- INSERT PRAYERS

INSERT INTO prayers
(user_id, prayer_date, fajr, zuhar, asar, maghrib, isha, qaza)
VALUES
(1, '2026-04-01', 1,1,1,1,1,0),
(1, '2026-04-02', 1,1,0,1,1,0),
(2, '2026-04-01', 1,0,1,1,1,0);

-- INSERT STUDY

INSERT INTO study
(user_id, study_date, subject_name, completed)
VALUES
(1, '2026-04-01', 'Database Systems', 1),
(1, '2026-04-01', 'Artificial Intelligence', 1),
(2, '2026-04-01', 'Software Engineering', 0);

-- INSERT SKILLS

INSERT INTO skills
(user_id, skill_date, skill_name, completed)
VALUES
(1, '2026-04-01', 'Python', 1),
(1, '2026-04-02', 'SQL', 1),
(2, '2026-04-01', 'Machine Learning', 0);

-- UPDATE OPERATION

UPDATE study
SET completed = 1
WHERE id = 3;

-- DELETE OPERATION

DELETE FROM skills
WHERE id = 3;
