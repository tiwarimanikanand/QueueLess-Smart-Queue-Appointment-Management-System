# QueueLess — Smart Queue & Appointment Management System

QueueLess is a web-based hospital OPD queue management system developed using PHP, MySQL, HTML, CSS, and JavaScript. It helps patients book appointments, track their queue tokens, and submit feedback, while administrators manage patient flow through a dedicated dashboard.

## Features

- **Patient Registration & Login:** Secure account creation and authentication.
- **Appointment Booking:** Book OPD appointments by selecting a department and doctor.
- **Priority-Based Queue:** Emergency, Senior, and General priority handling.
- **Live Token Tracking:** Monitor appointment status and people ahead in the queue.
- **Emergency Booking:** Emergency appointments do not require patients to select a preferred time.
- **Admin Dashboard:** Call the next patient and manage consultations.
- **Patient Feedback:** Submit ratings and suggestions.
- **Feedback Management:** Administrators can view patient reviews and comments.
- **Geolocation Support:** Optionally share browser-based location during booking.
- **Responsive UI:** A clean interface designed for different screen sizes.

## Technology Stack

| Technology | Purpose |
|---|---|
| PHP | Backend logic and API |
| MySQL | Database management |
| HTML5 | Web page structure |
| CSS3 | Styling and responsive layout |
| JavaScript | Client-side interactions and API requests |
| XAMPP | Local Apache and MySQL environment |

## Project Structure

```text
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
├── .env.example
└── images/
    └── login-bg.png
```

## Installation & Setup

### Prerequisites

- XAMPP
- PHP with MySQLi support
- MySQL or MariaDB
- A modern web browser

### Step 1: Set Up XAMPP

1. Install XAMPP.
2. Open the XAMPP Control Panel.
3. Start **Apache** and **MySQL**.

### Step 2: Copy the Project

Copy the project folder into:

```text
C:\xampp\htdocs\queueless
```

### Step 3: Configure the Database

1. Open `http://localhost/phpmyadmin/`.
2. Import the `schema.sql` file to create the database and tables.
3. Check `db.php` and update the database connection settings if required.

The default local configuration uses:

- Host: `localhost`
- Database: `queueless_db`
- Username: `root`
- Password: blank

These are standard local-development defaults. Configure appropriate credentials for other environments.

**Important:** Import the schema only for a fresh setup. Do not re-import it into an existing database containing data you need to preserve.

### Step 4: Create an Administrator Account

Configure these environment variables in your local PHP/Apache environment:

- `QUEUELESS_ADMIN_NAME`
- `QUEUELESS_ADMIN_USERNAME`
- `QUEUELESS_ADMIN_PASSWORD`

Use a unique password of at least 12 characters. Then open:

```text
http://localhost/queueless/create_admin.php
```

After the administrator account is created, **delete `create_admin.php` immediately**.

### Step 5: Run the Application

Open:

```text
http://localhost/queueless/
```

Register a patient account or log in through the Admin Desk.

## Queue Management Logic

- Priority order: **Emergency → Senior → General**
- Patients with the same priority are served in first-in, first-out order.
- The administrator can complete the current consultation and call the next waiting patient.
- Queue information refreshes every five seconds while a user is logged in.
- Emergency bookings use the server's current time for the appointment-time field.

## Security Considerations

This project is an educational college-project prototype and is not intended for production hospital use.

Before deployment, consider implementing:

- CSRF protection
- HTTPS
- Rate limiting
- Secure session-cookie configuration
- Strong administrator account provisioning
- Environment-based database credentials
- Audit logging and access controls
- Patient data privacy and security reviews

Do not commit real patient information, passwords, database credentials, or other secrets to a public repository.

## Future Improvements

- Appointment notifications
- Estimated waiting-time calculation
- Enhanced admin analytics
- Improved deployment configuration
- Additional authentication and security controls

## License

No license has been specified. All rights are reserved by default unless a license is added to this repository.

