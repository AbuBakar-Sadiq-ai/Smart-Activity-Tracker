#  Smart Daily Activity Tracker

<div align="center">

![Project Status](https://img.shields.io/badge/Status-Active-brightgreen)
![License](https://img.shields.io/badge/License-MIT-blue)
![Version](https://img.shields.io/badge/Version-1.0.0-orange)
![Database](https://img.shields.io/badge/Database-MySQL-0066cc)

**A comprehensive full-stack web application for tracking daily productivity, prayers, studies, and skill development**

[Features](#-features) • [Technologies](#-technologies-used) • [Installation](#-installation-guide) • [Documentation](#-database-schema) • [Contributing](#-contributors)

</div>

---

## 📋 Table of Contents

- [Project Overview](#-project-overview)
- [Features](#-features)
- [Technologies Used](#-technologies-used)
- [Folder Structure](#-folder-structure)
- [Database Schema](#-database-schema)
- [Installation Guide](#-installation-guide)
- [Usage Instructions](#-usage-instructions)
- [Security Features](#-security-features)
- [Future Enhancements](#-future-enhancements)
- [Screenshots](#-screenshots)
- [Contributors](#-contributors)
- [License](#-license)
- [Conclusion](#-conclusion)

---

## 🎯 Project Overview

The **Smart Daily Activity Tracker** is a full-stack web application developed for the **Database Systems Lab** semester project. This system empowers users to efficiently manage and track their daily productivity activities, including religious obligations (Namaz/prayers), academic study progress, and professional skill development.

The application provides an intuitive dashboard with real-time analytics, visual progress reports, and comprehensive tracking capabilities to help users stay organized and productive throughout their day.

### Key Objectives:
- ✅ Enable users to monitor daily prayer (Namaz) completion
- ✅ Track academic subjects and study progress
- ✅ Monitor skill development and learning milestones
- ✅ Provide visual analytics and productivity insights
- ✅ Ensure secure user authentication and data protection
- ✅ Maintain database integrity with proper normalization

---

## 🌟 Features

### 🔐 User Authentication System
- **User Signup**: Create new account with validation
- **User Login**: Secure login with session management
- **Session Management**: Persistent user sessions
- **Password Hashing**: Secure password storage using industry-standard hashing
- **Secure Authentication**: Protection against unauthorized access

###  Namaz (Prayer) Tracker
Track the five daily Islamic prayers plus Qaza (makeup prayers):
- **Fajr** - Dawn prayer
- **Zuhar** - Noon prayer
- **Asar** - Afternoon prayer
- **Maghrib** - Sunset prayer
- **Isha** - Night prayer
- **Qaza** - Makeup prayers

**Features:**
- ✓ Daily prayer completion marking
- ✓ Daily completion percentage calculation
- ✓ Visual progress charts using Chart.js
- ✓ Prayer history and records
- ✓ Weekly and monthly statistics

###  Study Tracker
Comprehensive academic progress tracking system:
- Track university-enrolled subjects
- Record daily study completion
- Monitor completion rates
- View daily/weekly/monthly progress analytics
- Current implementation uses hardcoded timetable
- Suitable for multiple semesters

**Features:**
- ✓ Subject-wise tracking
- ✓ Progress visualization
- ✓ Historical records
- ✓ Performance analytics

###  Skills Tracker
Professional skill development tracking:
- Add and monitor new skills
- Mark completed milestones
- Track skill development progress over time
- Date-wise skill records
- Skill completion history

**Features:**
- ✓ Skill category management
- ✓ Progress monitoring
- ✓ Achievement tracking
- ✓ Historical data maintenance

###  Dashboard & Analytics
Personalized user dashboard featuring:
- **Overall Productivity Score**: Aggregate performance metrics
- **Namaz Analytics**: Prayer completion insights
- **Study Analytics**: Academic progress visualization
- **Skills Analytics**: Skill development metrics
- **Visual Reports**: Interactive Chart.js graphs
- **Progress Percentages**: Real-time completion rates
- **Performance Overview**: Comprehensive productivity summary

---

##  Technologies Used

### **Frontend**
| Technology | Purpose |
|-----------|---------|
| **HTML5** | Page structure and semantic markup |
| **CSS3** | Styling and responsive design |
| **JavaScript** | Client-side interactivity and validation |
| **Chart.js** | Data visualization and charts |

### **Backend**
| Technology | Purpose |
|-----------|---------|
| **PHP 7+** | Server-side processing and business logic |
| **MySQL 5.7+** | Relational database management |

### **Development & Deployment**
| Technology | Purpose |
|-----------|---------|
| **XAMPP** | Local development server (Apache + MySQL) |
| **Apache 2.4+** | Web server |

### **Version Control**
| Technology | Purpose |
|-----------|---------|
| **Git** | Version control system |
| **GitHub** | Remote repository hosting |

---

## 📁 Folder Structure

```
activity_tracker/
│
├── auth/                          # Authentication module
│   ├── signup.php                # User registration
│   ├── login.php                 # User login
│   ├── logout.php                # User logout
│   └── authenticate.php          # Authentication logic
│
├── config/                        # Configuration files
│   ├── database.php              # Database connection
│   └── settings.php              # Application settings
│
├── dashboard/                     # Dashboard module
│   ├── index.php                 # Main dashboard
│   ├── analytics.php             # Analytics display
│   └── dashboard.css             # Dashboard styles
│
├── prayers/                       # Prayer tracker module
│   ├── index.php                 # Prayer tracker page
│   ├── add_prayer.php            # Add prayer record
│   ├── prayer_logic.php          # Prayer processing logic
│   └── prayers.css               # Prayer styles
│
├── study/                         # Study tracker module
│   ├── index.php                 # Study tracker page
│   ├── add_study.php             # Add study record
│   ├── study_logic.php           # Study processing logic
│   └── study.css                 # Study styles
│
├── skills/                        # Skills tracker module
│   ├── index.php                 # Skills tracker page
│   ├── add_skill.php             # Add skill record
│   ├── skill_logic.php           # Skills processing logic
│   └── skills.css                # Skills styles
│
├── assets/                        # Static assets
│   ├── css/                       # Global stylesheets
│   ├── js/                        # Global JavaScript files
│   └── lib/                       # External libraries
│
├── img/                           # Images and icons
│   ├── icons/                     # Icon files
│   └── screenshots/               # Project screenshots
│
├── index.php                      # Application entry point
├── .gitignore                     # Git ignore file
├── README.md                      # Project documentation
└── database.sql                   # Database schema file

```

---

## 🗄 Database Schema

### **Database Name**
```
activity_tracker_db
```

### **Main Tables Overview**

#### 1️⃣ **users** Table
Stores user account information
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### 2️⃣ **prayers** Table
Records daily prayer completion
```sql
CREATE TABLE prayers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    prayer_date DATE NOT NULL,
    fajr BOOLEAN DEFAULT 0,
    zuhar BOOLEAN DEFAULT 0,
    asar BOOLEAN DEFAULT 0,
    maghrib BOOLEAN DEFAULT 0,
    isha BOOLEAN DEFAULT 0,
    qaza BOOLEAN DEFAULT 0,
    total_completed INT,
    completion_percentage DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 3️⃣ **study** Table
Tracks study progress and subject completion
```sql
CREATE TABLE study (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    study_date DATE NOT NULL,
    duration_hours DECIMAL(5, 2),
    is_completed BOOLEAN DEFAULT 0,
    progress_percentage DECIMAL(5, 2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### 4️⃣ **skills** Table
Maintains skill development records
```sql
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    skill_category VARCHAR(50),
    proficiency_level VARCHAR(20),
    completion_percentage DECIMAL(5, 2),
    skill_date DATE NOT NULL,
    is_completed BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### **Database Relationships (ERD Summary)**

```
┌─────────────┐
│   users     │
│  (1 entity) │
└──────┬──────┘
       │
       ├─────────────────────────┬──────────────────────────┬──────────────────────────┐
       │                         │                          │                          │
       │ (1 to Many)             │ (1 to Many)              │ (1 to Many)              │
       ▼                         ▼                          ▼
┌─────────────┐           ┌──────────────┐          ┌──────────────┐
│  prayers    │           │    study     │          │   skills     │
│ (Multiple)  │           │  (Multiple)  │          │  (Multiple)  │
└─────────────┘           └──────────────┘          └──────────────┘
```

### **Key Constraints**

**Primary Keys:**
- `users.id` - Unique user identifier
- `prayers.id` - Unique prayer record identifier
- `study.id` - Unique study record identifier
- `skills.id` - Unique skill record identifier

**Foreign Keys:**
- `prayers.user_id` → `users.id` (ON DELETE CASCADE)
- `study.user_id` → `users.id` (ON DELETE CASCADE)
- `skills.user_id` → `users.id` (ON DELETE CASCADE)

### **Normalization**

The database design adheres to proper relational normalization:

✅ **First Normal Form (1NF)**
- All attributes contain atomic values
- No repeating groups or arrays
- Each field contains a single value

✅ **Second Normal Form (2NF)**
- All 1NF requirements satisfied
- All non-key attributes are fully dependent on the primary key
- No partial dependencies

✅ **Third Normal Form (3NF)**
- All 2NF requirements satisfied
- No transitive dependencies
- Non-key attributes depend only on the primary key

**Benefits:**
- Minimal data redundancy
- Data integrity maintenance
- Efficient query performance
- Easy data updates and modifications

---

## 🚀 Installation Guide

### **Prerequisites**
Before you begin, ensure you have the following installed:
- **XAMPP** (Apache + MySQL + PHP)
- **Git** (for version control)
- **Web Browser** (Chrome, Firefox, Safari, Edge)
- **Text Editor** (VS Code, Sublime Text, etc.)

### **Step-by-Step Installation**

#### **1. Install XAMPP**
- Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
- Follow the installation wizard for your operating system
- Choose the default installation path

#### **2. Clone the Repository**
```bash
# Open terminal/command prompt
git clone https://github.com/AbuBakar-Sadiq-ai/Smart-Activity-Tracker.git

# Navigate to your XAMPP htdocs folder
cd /path/to/xampp/htdocs

# Or move the cloned folder to htdocs directory
```

#### **3. Start XAMPP Services**
```bash
# Launch XAMPP Control Panel
# Start Apache Server
# Start MySQL Database Server
```

#### **4. Create Database**
```bash
# Open phpMyAdmin
# URL: http://localhost/phpmyadmin

# Create new database
CREATE DATABASE activity_tracker_db;
USE activity_tracker_db;
```

#### **5. Import Database Schema**
- In phpMyAdmin, select `activity_tracker_db`
- Click "Import" tab
- Browse and select `database.sql` from the project folder
- Click "Go" to import all tables

#### **6. Configure Database Connection**
Edit `config/database.php`:
```php
<?php
$hostname = 'localhost';
$username = 'root';      // Default XAMPP user
$password = '';          // Default XAMPP password (empty)
$database = 'activity_tracker_db';

$connection = new mysqli($hostname, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>
```

#### **7. Verify Installation**
- Open browser and navigate to:
```
http://localhost/activity_tracker
```
- You should see the login page
- If you see the login page, installation is successful! ✅

---

## 📖 Usage Instructions

### **Getting Started**

#### **1. Create New Account**
- Click "Sign Up" on the login page
- Enter your desired username and email
- Create a strong password
- Click "Register"
- You'll be redirected to login page

#### **2. Login to Application**
- Enter your username/email
- Enter your password
- Click "Login"
- You'll be directed to your personalized dashboard

#### **3. Access Dashboard**
The main dashboard displays:
- Overall productivity score
- Quick statistics for Namaz, Study, and Skills
- Visual charts and progress bars
- Navigation to all tracking modules

### **Using the Namaz Tracker** 📿

```
1. Click "Namaz Tracker" from navigation menu
2. Select today's date (default is current date)
3. Mark completed prayers by clicking checkboxes:
   ✓ Fajr
   ✓ Zuhar
   ✓ Asar
   ✓ Maghrib
   ✓ Isha
   ✓ Qaza
4. System automatically calculates:
   - Number of prayers completed
   - Completion percentage
5. Click "Save" to record
6. View charts showing:
   - Daily completion trends
   - Weekly statistics
   - Monthly progress
```

### **Using the Study Tracker** 📚

```
1. Navigate to "Study Tracker"
2. View your enrolled subjects
3. For each study session:
   - Select subject
   - Enter study date
   - Input study duration (in hours)
   - Mark as completed if finished
   - Add optional notes
4. Click "Add Study Record"
5. Dashboard shows:
   - Subject-wise progress
   - Study hours tracked
   - Completion percentage
   - Weekly/Monthly analysis
```

### **Using the Skills Tracker** 💡

```
1. Go to "Skills Tracker"
2. To add a new skill:
   - Enter skill name
   - Select category (e.g., Programming, Languages, etc.)
   - Set initial proficiency level
   - Click "Add Skill"
3. To mark progress:
   - Select existing skill
   - Update proficiency level
   - Mark milestones as completed
   - Record date of progress
   - Click "Update Skill"
4. View skill development metrics in dashboard
```

### **Viewing Analytics** 📊

```
1. From dashboard, click "View Analytics"
2. See comprehensive reports:
   - Prayer completion trends
   - Study progress graphs
   - Skill development timeline
   - Productivity scores
3. Filter by date range:
   - Daily view
   - Weekly view
   - Monthly view
4. Download/Export reports (future feature)
```

---

## 🔒 Security Features

### **Authentication Security**
- ✅ **Password Hashing**: Passwords encrypted using `password_hash()` function
- ✅ **Session Management**: Secure PHP session handling with timeout
- ✅ **Login Validation**: Server-side and client-side validation
- ✅ **SQL Injection Prevention**: Parameterized queries throughout

### **Database Security**
- ✅ **Prepared Statements**: All database queries use prepared statements
- ✅ **Input Validation**: Strict validation of all user inputs
- ✅ **Type Casting**: Ensure correct data types before processing
- ✅ **Foreign Key Constraints**: Maintain data integrity with CASCADE rules

### **Session Security**
- ✅ **Session Expiration**: Automatic logout after inactivity
- ✅ **Session Regeneration**: Prevent session fixation attacks
- ✅ **Secure Cookies**: HttpOnly and Secure flags on session cookies
- ✅ **CSRF Protection**: Token validation for state-changing operations

### **Code Security**
- ✅ **Error Handling**: Proper exception handling without exposing details
- ✅ **Logging**: Secure logging of authentication attempts
- ✅ **Access Control**: Role-based access to different modules
- ✅ **Data Validation**: Both client and server-side validation

### **Implementation Examples**

**Prepared Statement Example:**
```php
<?php
// ✅ SECURE: Using prepared statements
$stmt = $connection->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// ❌ INSECURE: Direct query (DO NOT USE)
$query = "SELECT * FROM users WHERE username = '$username'";
?>
```

**Password Hashing Example:**
```php
<?php
// ✅ SECURE: Hashing password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Verify password during login
$is_valid = password_verify($input_password, $hashed_password);
?>
```

---

## 🔮 Future Enhancements

### **Phase 2 Improvements**

#### 🎯 Dynamic Timetable System
- [ ] User-defined custom timetable creation
- [ ] Subject scheduling with time slots
- [ ] Automated reminders for study sessions
- [ ] Calendar integration
- [ ] Flexible semester planning

#### 📱 Mobile Responsiveness
- [ ] Responsive design for all screen sizes
- [ ] Mobile app development (React Native/Flutter)
- [ ] Progressive Web App (PWA) capabilities
- [ ] Touch-optimized interface
- [ ] Offline functionality

#### 📈 Advanced Analytics
- [ ] Predictive analytics for productivity
- [ ] Trend analysis and insights
- [ ] Comparative statistics
- [ ] Custom report generation
- [ ] Data export to CSV/PDF

#### 🤖 AI-Powered Features
- [ ] AI productivity suggestions
- [ ] Machine learning-based recommendations
- [ ] Intelligent time management tips
- [ ] Habit formation tracking
- [ ] Personalized learning paths

#### 📊 Export & Reporting
- [ ] PDF report generation
- [ ] Excel/CSV data export
- [ ] Email report delivery
- [ ] Scheduled automated reports
- [ ] Shareable report links

#### 👨‍💼 Admin Panel
- [ ] Admin dashboard
- [ ] User management interface
- [ ] System analytics and monitoring
- [ ] Database backup/restore
- [ ] User statistics and reports

#### 🌍 Additional Features (Backlog)
- [ ] Multi-language support
- [ ] Dark mode/Light mode toggle
- [ ] Social sharing features
- [ ] Leaderboard/Gamification
- [ ] Collaboration tools
- [ ] API development for third-party integration
- [ ] Cloud deployment options
- [ ] Two-factor authentication (2FA)

---

## 📸 Screenshots

### Project Screenshots Section

*Screenshots will be added here to showcase:*
- Login and registration interface
- Dashboard overview
- Namaz tracker interface
- Study tracker with progress visualization
- Skills tracker page
- Analytics and reports
- Database structure

To add screenshots:
1. Place screenshot images in `img/screenshots/` folder
2. Use the following format:

```markdown
#### Login Page
![Login Page](img/screenshots/login.png)

#### Dashboard
![Dashboard](img/screenshots/dashboard.png)

#### Namaz Tracker
![Namaz Tracker](img/screenshots/namaz_tracker.png)

#### Study Tracker
![Study Tracker](img/screenshots/study_tracker.png)

#### Skills Tracker
![Skills Tracker](img/screenshots/skills_tracker.png)

#### Analytics
![Analytics Dashboard](img/screenshots/analytics.png)
```

---

## 👥 Contributors

| Name | Role | University | Contact |
|------|------|-----------|---------|
| Abu Bakar Sadiq | Full Stack Developer | [Your University] | [@AbuBakar-Sadiq-ai](https://github.com/AbuBakar-Sadiq-ai) |

### Contributing

We welcome contributions to improve this project! Here's how you can help:

1. **Fork the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/Smart-Activity-Tracker.git
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/YourFeatureName
   ```

3. **Make your changes**
   - Follow existing code style
   - Add comments for complex logic
   - Test thoroughly before committing

4. **Commit your changes**
   ```bash
   git commit -m "Add: Description of your feature"
   ```

5. **Push to branch**
   ```bash
   git push origin feature/YourFeatureName
   ```

6. **Create a Pull Request**
   - Describe changes clearly
   - Reference any related issues
   - Wait for review and feedback

---

## 📄 License

This project is licensed under the **MIT License** - see the LICENSE file for details.

### MIT License Summary
- ✅ Free to use, modify, and distribute
- ✅ Must include license and copyright notice
- ✅ No warranty provided
- ✅ Can be used for commercial purposes

### You are free to:
- **Use** - Use the software for any purpose
- **Modify** - Modify the source code
- **Distribute** - Copy and distribute the software
- **Sublicense** - Include the software in your own projects

### Conditions:
- Include the original license
- Include copyright notice
- State significant changes made

---

## 🎓 Conclusion

The **Smart Daily Activity Tracker** is a comprehensive solution for managing daily productivity and personal development. This project demonstrates practical application of:

✨ **Full-Stack Web Development**
- Modern frontend technologies (HTML, CSS, JavaScript)
- Server-side scripting (PHP)
- Database design and management (MySQL)

✨ **Database Design Principles**
- Proper normalization (1NF, 2NF, 3NF)
- Entity-relationship modeling
- Data integrity and constraints
- Efficient query optimization

✨ **Security Best Practices**
- User authentication and authorization
- SQL injection prevention
- Password security
- Session management

✨ **Software Engineering Practices**
- Clean code architecture
- Modular folder structure
- Version control (Git/GitHub)
- Documentation standards

### **Learning Outcomes**
This semester project successfully demonstrates:
- Understanding of relational database concepts
- Implementation of secure web applications
- Full-stack development capabilities
- Professional documentation and deployment skills

### **Future Vision**
The Smart Daily Activity Tracker can evolve into a powerful productivity platform with AI-driven insights, mobile applications, and advanced analytics. The modular architecture allows for seamless feature expansion and scalability.

### **Getting Started**
Ready to use the Smart Daily Activity Tracker?
1. Follow the [Installation Guide](#-installation-guide)
2. Create your account
3. Start tracking your daily activities
4. Monitor your productivity growth

---

<div align="center">

### 🌟 If you find this project helpful, please give it a star! ⭐

**Happy Tracking! 🚀**

Made with ❤️ for the Database Systems Lab

</div>
