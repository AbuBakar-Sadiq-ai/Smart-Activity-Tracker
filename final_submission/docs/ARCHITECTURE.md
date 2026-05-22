# Architecture Documentation

## Smart Daily Activity Tracker - System Architecture

---

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [3-Tier Architecture](#3-tier-architecture)
3. [Components Breakdown](#components-breakdown)
4. [Data Flow Architecture](#data-flow-architecture)
5. [Security Architecture](#security-architecture)
6. [Technology Stack](#technology-stack)
7. [Design Patterns](#design-patterns)

---

## Architecture Overview

The Smart Daily Activity Tracker follows a **3-Tier Web Application Architecture** with clear separation of concerns:

```
┌─────────────────────────────────────────────────────┐
│          PRESENTATION LAYER                         │
│  (HTML, CSS, JavaScript, Chart.js)                  │
├─────────────────────────────────────────────────────┤
│          APPLICATION LAYER                          │
│  (PHP Business Logic, Session Management)           │
├─────────────────────────────────────────────────────┤
│          DATA LAYER                                 │
│  (MySQL Database, SQL Queries)                      │
└─────────────────────────────────────────────────────┘
```

### Key Architectural Principles

- **Separation of Concerns:** Each layer has distinct responsibilities
- **Modularity:** Independent components that can be updated separately
- **Scalability:** Can handle increased load by optimizing each layer
- **Security:** Security measures implemented at each layer
- **Maintainability:** Clean code structure for easy updates

---

## 3-Tier Architecture

### Tier 1: Presentation Layer (Frontend)

**Responsibility:** User Interface and user interaction

**Components:**
- `index.php` - Landing page and navigation
- `auth/login.php` - User login interface
- `auth/signup.php` - User registration interface
- `dashboard/dashboard.php` - Main dashboard view
- `prayers/prayer_tracker.php` - Prayer tracking UI
- `study/study_tracker.php` - Study tracking UI
- `skills/skills_tracker.php` - Skills tracking UI

**Technologies:**
- **HTML5** - Semantic markup structure
- **CSS3** - Responsive styling
- **JavaScript** - Client-side interactivity
- **Chart.js** - Data visualization

**Responsibilities:**
- Render user interface
- Collect user input via forms
- Display data from server
- Provide real-time feedback with AJAX
- Handle client-side validation
- Generate charts and visualizations

```
User Interface Layer:
├── Forms (Input Validation)
├── Navigation (Routing)
├── Charts (Visualization)
├── Notifications (User Feedback)
└── Session Display (User State)
```

### Tier 2: Application Layer (Backend)

**Responsibility:** Business logic and request processing

**Components:**
- `config/database.php` - Database connection configuration
- `auth/authenticate.php` - Authentication logic
- `prayers/add_prayer.php` - Prayer handling logic
- `study/add_study.php` - Study handling logic
- `skills/add_skill.php` - Skills handling logic
- `dashboard/get_analytics.php` - Analytics calculation
- `dashboard/productivity_score.php` - Score calculation

**Technologies:**
- **PHP 7.4+** - Server-side scripting
- **Session Management** - User state tracking

**Responsibilities:**
- Process user requests
- Validate input data
- Execute business logic
- Manage user sessions
- Calculate analytics
- Interact with database
- Handle errors and exceptions
- Return JSON/HTML responses

```
Application Logic Layer:
├── Request Handler (Receive & validate input)
├── Session Manager (Track user state)
├── Business Logic (Process data)
├── Calculation Engine (Analytics, scores)
├── Error Handler (Manage exceptions)
└── Response Formatter (JSON, HTML, redirects)
```

### Tier 3: Data Layer (Backend)

**Responsibility:** Data persistence and retrieval

**Components:**
- **MySQL Database** - Data storage
- **SQL Scripts** - Queries and stored procedures
- **Indexes** - Query optimization

**Technologies:**
- **MySQL 8.0+** - Relational database
- **Prepared Statements** - SQL injection prevention
- **Transactions** - Data consistency

**Responsibilities:**
- Store data persistently
- Retrieve data on demand
- Maintain data integrity
- Ensure referential relationships
- Enforce constraints
- Optimize query performance

```
Data Persistence Layer:
├── users (Account data)
├── prayers (Prayer tracking data)
├── study (Study session data)
├── skills (Skills data)
├── Indexes (Performance optimization)
└── Constraints (Data validation)
```

---

## Components Breakdown

### 1. Authentication Component

```
Authentication System:
├── Signup Module
│   ├── Input Validation (PHP)
│   ├── Username/Email Uniqueness Check
│   ├── Password Hashing (SHA-256)
│   └── User Record Creation
│
├── Login Module
│   ├── Credential Validation
│   ├── Password Verification
│   ├── Session Creation
│   └── Redirect to Dashboard
│
└── Session Management
    ├── Session Start ($_SESSION)
    ├── Session Validation
    ├── Session Timeout (30 min)
    └── Logout/Session Cleanup
```

**Security Features:**
- Prepared statements for injection prevention
- Password hashing (SHA-256 with salt)
- Session validation on each request
- HTTPS recommended for production

### 2. Tracker Components

#### Prayer Tracker
```
Prayer Tracker Architecture:
├── Input Interface
│   ├── Prayer selection checkboxes (Fajr, Zuhar, Asar, Maghrib, Isha)
│   ├── Qaza count input
│   └── Date selector
│
├── Processing
│   ├── Calculate completion percentage
│   ├── Validate data
│   └── Store in prayers table
│
└── Display
    ├── Daily completion status
    ├── Weekly summary
    ├── Monthly statistics
    └── Visual indicators
```

#### Study Tracker
```
Study Tracker Architecture:
├── Input Interface
│   ├── Subject selection/input
│   ├── Duration input (minutes)
│   ├── Progress percentage
│   ├── Status selection
│   └── Notes field
│
├── Processing
│   ├── Validate input ranges
│   ├── Calculate aggregate statistics
│   └── Store study session
│
└── Display
    ├── Session list
    ├── Subject-based summaries
    ├── Weekly progress charts
    └── Performance analytics
```

#### Skills Tracker
```
Skills Tracker Architecture:
├── Input Interface
│   ├── Skill name input
│   ├── Proficiency level selection
│   ├── Progress percentage
│   └── Notes field
│
├── Processing
│   ├── Validate proficiency levels
│   ├── Update skill records
│   └── Calculate growth metrics
│
└── Display
    ├── Skills inventory
    ├── Proficiency distribution
    ├── Learning timeline
    └── Progress visualization
```

### 3. Analytics Component

```
Analytics System Architecture:
├── Data Collection
│   ├── Prayers: Daily completion rates
│   ├── Study: Duration and progress
│   └── Skills: Proficiency levels
│
├── Calculation Engine
│   ├── Prayer completion percentage
│   ├── Study hours per subject
│   ├── Skills proficiency average
│   └── Overall productivity score
│
└── Visualization
    ├── Pie charts (Prayer completion)
    ├── Bar charts (Study hours)
    ├── Line graphs (Skills progression)
    └── Summary cards (Key metrics)
```

---

## Data Flow Architecture

### User Registration Flow

```
User Input (Signup Form)
        ↓
Frontend Validation (JavaScript)
        ↓
POST /auth/signup.php
        ↓
Backend Validation (PHP)
        ↓
Database Uniqueness Check (Query)
        ↓
Password Hashing (SHA-256)
        ↓
INSERT INTO users (Prepared Statement)
        ↓
Session Creation
        ↓
Redirect to Dashboard
```

### Prayer Recording Flow

```
User Selects Prayers
        ↓
User Submits Form
        ↓
Frontend Validation
        ↓
AJAX POST /prayers/add_prayer.php
        ↓
Backend Processing
    ├─ Validate input
    ├─ Calculate completion %
    └─ Prepare statement
        ↓
Database Insert (Transaction)
        ↓
AJAX Response (JSON)
        ↓
Frontend Update (JavaScript)
        ↓
Chart.js Refresh
```

### Analytics Generation Flow

```
User Views Dashboard
        ↓
AJAX GET /dashboard/get_analytics.php
        ↓
Backend Aggregation Queries
    ├─ Prayer completion stats
    ├─ Study hours summary
    ├─ Skills proficiency
    └─ Productivity calculation
        ↓
Data Formatting (JSON)
        ↓
AJAX Response
        ↓
Chart.js Rendering
        ↓
Display Visualizations
```

---

## Security Architecture

### Multi-Layer Security Implementation

#### Layer 1: Input Validation
```php
// Frontend (JavaScript)
- Check field not empty
- Validate email format
- Check password strength
- Verify data type

// Backend (PHP)
- Trim whitespace
- Check data types
- Validate value ranges
- Sanitize input
```

#### Layer 2: SQL Injection Prevention
```php
// Use Prepared Statements (NEVER raw queries)
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();

// Benefits:
- Separates SQL code from data
- Escapes special characters
- Prevents SQL injection attacks
```

#### Layer 3: Authentication
```php
// Password Security
- Hash with SHA-256
- Add salt (random string)
- Never store plain text passwords

// Session Security
- Session ID generation
- Session timeout (30 minutes)
- Session validation on each request
- Logout clears session
```

#### Layer 4: Output Security
```php
// Prevent XSS (Cross-Site Scripting)
- Escape HTML special characters
- Use htmlspecialchars()
- Set Content-Type headers
- Use CSP (Content Security Policy)
```

### Security Checklist

- ✅ Prepared statements for all queries
- ✅ Password hashing (SHA-256)
- ✅ Session-based authentication
- ✅ Input validation (frontend & backend)
- ✅ HTTPS recommended for production
- ✅ No sensitive data in logs
- ✅ Secure session cookies
- ✅ CSRF token implementation (recommended)
- ✅ Rate limiting (recommended)

---

## Technology Stack

### Frontend Stack
```
├─ HTML5
│  └─ Semantic markup
├─ CSS3
│  ├─ Responsive design
│  └─ Flexbox/Grid layouts
├─ JavaScript (Vanilla)
│  ├─ DOM manipulation
│  ├─ Form validation
│  ├─ AJAX requests
│  └─ Event handling
└─ Chart.js
   ├─ Pie charts
   ├─ Bar charts
   └─ Line graphs
```

### Backend Stack
```
├─ PHP 7.4+
│  ├─ Request handling
│  ├─ Session management
│  ├─ Business logic
│  └─ Database interaction
└─ Web Server
   └─ Apache 2.4+ (via XAMPP)
```

### Database Stack
```
├─ MySQL 8.0+
│  ├─ 4 normalized tables
│  ├─ Prepared statements
│  ├─ Transaction support
│  └─ 15+ performance indexes
```

### Development Stack
```
├─ XAMPP (Local Development)
│  ├─ Apache server
│  ├─ PHP interpreter
│  └─ MySQL database
├─ MySQL Workbench (Database management)
├─ Text Editor/IDE (Code editing)
└─ Git (Version control)
```

---

## Design Patterns

### 1. MVC-inspired Architecture

```
Controllers (PHP files handling requests)
├─ auth/authenticate.php
├─ prayers/add_prayer.php
├─ study/add_study.php
├─ skills/add_skill.php
└─ dashboard/get_analytics.php

Views (HTML/PHP files for display)
├─ auth/login.php
├─ auth/signup.php
├─ dashboard/dashboard.php
├─ prayers/prayer_tracker.php
├─ study/study_tracker.php
└─ skills/skills_tracker.php

Models (Database operations)
└─ config/database.php
```

### 2. Separation of Concerns

```
Database Layer
├─ Connection management
├─ Prepared statements
└─ Data operations

Business Logic Layer
├─ Input validation
├─ Data processing
├─ Calculation engines
└─ Error handling

Presentation Layer
├─ User interface
├─ Form handling
├─ Data display
└─ User feedback
```

### 3. Session Management Pattern

```
Session Lifecycle:
1. User logs in → Session created
2. Session stored in $_SESSION
3. User navigates → Session validated
4. Session timeout after inactivity
5. User logs out → Session destroyed
```

### 4. AJAX Communication Pattern

```
Client (JavaScript)
    ↓ AJAX POST/GET
Server (PHP)
    ↓ Process request
    ↓ Query database
    ↓ Format JSON response
Client (JavaScript)
    ↓ Receive response
    ↓ Update DOM
    ↓ Refresh charts
```

---

## Performance Optimization

### Database Optimization
- Proper indexing on frequently queried columns
- Composite indexes for multi-column searches
- Query optimization with EXPLAIN analysis
- Connection pooling (XAMPP default)

### Frontend Optimization
- Minimize JavaScript file size
- Cache Chart.js library
- Lazy load images
- Reduce DOM manipulation
- Use AJAX for partial page updates

### Caching Strategy
- Browser caching for static assets
- Session caching for user data
- Query result caching for analytics (recommended for production)

---

## Scalability Considerations

### Horizontal Scalability
- Stateless session handling (can use Redis)
- Load balancing support
- Separate database server

### Vertical Scalability
- Database query optimization
- Efficient indexing strategy
- Code optimization

### Future Enhancements
- Implement caching layer (Redis)
- Add database replication
- Implement API gateway
- Add microservices architecture

---

**Last Updated:** May 2026  
**Version:** 1.0.0  
**Architecture Level:** Production Ready  
**Normalization:** 3NF
