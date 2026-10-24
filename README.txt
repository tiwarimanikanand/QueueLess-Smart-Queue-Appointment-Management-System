QUEUELESS — PHP + MySQL (XAMPP) FINAL PROJECT

FILES
- index.php: Frontend page
- style.css: Styling
- script.js: Browser interactions and API calls
- db.php: MySQL connection
- api.php: Registration, login, appointments, queue, cancellation, feedback
- schema.sql: Database tables
- create_admin.php: One-time admin account creator

INSTALLATION
1. Start Apache and MySQL from XAMPP Control Panel.
2. Copy the entire QueueLess_Final folder into C:\xampp\htdocs\queueless
   (rename the folder to queueless).
3. Open http://localhost/phpmyadmin/
4. Open the SQL tab and import schema.sql, or open the file and run all SQL statements.
5. Open http://localhost/queueless/create_admin.php once to create the admin account.
   Admin username: admin
   Temporary password: ChangeMe123!
6. DELETE create_admin.php after the admin account is created.
7. Open http://localhost/queueless/

DATABASE CONNECTION
db.php uses the standard local XAMPP defaults: host localhost, user root, blank password,
database queueless_db. Update it if your local MySQL credentials differ.

NOTES
- This is a local college project prototype, not production hospital software.
- Passwords are stored using PHP password_hash/password_verify.
- API requests use prepared statements for user-supplied SQL values.
- Priority order: Emergency, Senior, General; within a priority, oldest appointment first.
- The queue refreshes every 5 seconds while a user is logged in.
- Before any real deployment, add CSRF protection, stronger admin provisioning, HTTPS,
  rate limiting, audit logging, privacy controls and clinical workflow review.
