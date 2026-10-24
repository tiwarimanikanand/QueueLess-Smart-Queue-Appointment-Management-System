QueueLess UI + Feedback Admin Fix

Replace these files in C:\xampp\htdocs\queueless:
- style.css
- index.php
- script.js
- api.php

Do not run schema.sql. No database table, column, or data changes are required.

Changes:
- Improved the existing blue/cyan theme, spacing, cards, forms, queue items, responsive layouts, and focus states.
- Added an Admin Dashboard Patient Feedback panel.
- Admin feedback list shows patient name, username, star rating, submitted comment, and timestamp.
- Feedback list refreshes automatically every 5 seconds while an admin is logged in.
- Uses the existing feedback and users tables; the API reads feedback joined to users.

After replacing files, keep Apache and MySQL running and hard-refresh the site with Ctrl+F5.
Test: submit feedback from a patient account, then log in to the Admin Desk and check the Patient Feedback panel.
