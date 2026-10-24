QueueLess JavaScript + Queue Logic Fix

Replace only these two files in C:\xampp\htdocs\queueless:
1. script.js
2. api.php

Do NOT run schema.sql and do NOT change the database.
The existing database tables and data remain unchanged.

Queue behavior after this fix:
- Only one appointment can be In Consultation at a time through the Call Next Patient action.
- First click moves the highest-priority waiting patient into In Consultation.
- Next click completes that consultation and calls the next patient.
- Priority order among waiting patients: Emergency, Senior, General.
- Within the same priority, older appointments are served first (FIFO).
- The patient in consultation is shown at the top of the queue.
- Patient tracker shows zero people ahead while their status is In Consultation.
- JavaScript prevents duplicate clicks/overlapping queue refresh requests and safely renders patient-entered text.

After replacing files, refresh the site with Ctrl+F5. Keep Apache and MySQL running.
