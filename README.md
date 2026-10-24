# QueueLess — Smart Queue & Appointment Management System

QueueLess is a web-based hospital OPD queue management system developed using PHP, MySQL, HTML, CSS, and JavaScript. It enables patients to book appointments, track their tokens, and submit feedback, while administrators manage the patient queue through a dedicated dashboard.

## Features

- Patient registration and login
- OPD appointment booking
- Priority-based queue management: Emergency, Senior, and General
- Live token tracking and queue status updates
- Emergency booking without selecting a preferred time
- Admin dashboard for calling the next patient
- Patient feedback and rating system
- Admin panel for viewing patient feedback
- Optional browser geolocation support
- Responsive user interface

## Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Local Development Environment:** XAMPP

## Project Structure

    QueueLess/
    ├── index.php
    ├── style.css
    ├── script.js
    ├── api.php
    ├── db.php
    ├── schema.sql
    ├── create_admin.php
    ├── README.md
    ├── .gitignore
    └── .env.example

Note: The background image can be placed in `images/login-bg.png` if available.

## Installation and Setup

### 1. Install XAMPP

Install XAMPP and start Apache and MySQL from the XAMPP Control Panel.

### 2. Copy the Project

Copy the project folder into:

`C:\xampp\htdocs\queueless`

### 3. Create the Database

1. Open `http://localhost/phpmyadmin/`.
2. Select the Import option.
3. Upload the `schema.sql` file.
4. Execute the import to create the database and tables.

Important: Import the schema only for a fresh setup. Do not re-import it into an existing database containing data you want to preserve.

### 4. Configure the Database Connection

Open `db.php` and verify the database connection settings.

Default local XAMPP settings:

- Host: `localhost`
- Username: `root`
- Password: blank
- Database: `queueless_db`

Update these settings if your local MySQL configuration is different.

### 5. Create an Administrator Account

Configure the following environment variables in your local PHP/Apache environment:

- `QUEUELESS_ADMIN_NAME`
- `QUEUELESS_ADMIN_USERNAME`
- `QUEUELESS_ADMIN_PASSWORD`

Use a unique password containing at least 12 characters.

Then open:

`http://localhost/queueless/create_admin.php`

After successfully creating the administrator account, **delete `create_admin.php` immediately**.

### 6. Run the Application

Open the following URL in your browser:

`http://localhost/queueless/`

Register a patient account or use the Admin Desk to access the administrator dashboard.

## Queue Management Logic

- Emergency appointments receive the highest priority.
- Senior appointments are served after Emergency appointments.
- General appointments are served after Senior appointments.
- Patients within the same priority category are served in first-in, first-out order.
- The administrator can complete the current consultation and call the next waiting patient.
- Queue information refreshes every five seconds while a user is logged in.

## Security Considerations

QueueLess is an educational college project prototype and is not intended for production hospital use.

Before deploying this application, consider implementing:

- CSRF protection
- HTTPS
- Rate limiting
- Secure session-cookie configuration
- Strong administrator account provisioning
- Secure database credential management
- Audit logging and access controls
- Patient data privacy and security reviews

Never upload real patient information, passwords, database credentials, or other secrets to a public repository.

## Future Improvements

- Appointment notifications
- Estimated waiting-time calculation
- Admin analytics dashboard
- Enhanced authentication and security
- Production deployment support

## License

No license has been specified for this project. Unless a license is added, others do not automatically receive permission to reuse, modify, or distribute the code.
