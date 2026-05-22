CREATE DATABASE IF NOT EXISTS activity_tracker_db;

USE activity_tracker_db;

-- USERS TABLE

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    password VARCHAR(255) NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PRAYERS TABLE

CREATE TABLE prayers (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    prayer_date DATE NOT NULL,

    fajr TINYINT(1) DEFAULT 0 CHECK (fajr IN (0,1)),

    zuhar TINYINT(1) DEFAULT 0 CHECK (zuhar IN (0,1)),

    asar TINYINT(1) DEFAULT 0 CHECK (asar IN (0,1)),

    maghrib TINYINT(1) DEFAULT 0 CHECK (maghrib IN (0,1)),

    isha TINYINT(1) DEFAULT 0 CHECK (isha IN (0,1)),

    qaza TINYINT(1) DEFAULT 0 CHECK (qaza IN (0,1)),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_prayers_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_prayers_user_id
ON prayers(user_id);

-- STUDY TABLE

CREATE TABLE study (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    study_date DATE NOT NULL,

    subject_name VARCHAR(255) NOT NULL,

    completed TINYINT(1) DEFAULT 0 CHECK (completed IN (0,1)),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_study_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_study_user_id
ON study(user_id);

-- SKILLS TABLE

CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    skill_date DATE NOT NULL,

    skill_name VARCHAR(255) NOT NULL,

    completed TINYINT(1) DEFAULT 0 CHECK (completed IN (0,1)),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_skills_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE INDEX idx_skills_user_id
ON skills(user_id);
