QueueLess replacement package

Copy these project files into C:\xampp\htdocs\queueless and overwrite existing files when prompted.
Do not import schema.sql and do not modify the existing queueless_db database. db.php/schema.sql are included for project completeness; your existing database/data should remain unchanged.

Emergency booking: Preferred Time is hidden in the form. The API fills the existing required appointment_time field with server time, without asking the patient. General and Senior still require a preferred time.

After replacing files, open http://localhost/queueless/ and hard-refresh (Ctrl+F5). Test General, Senior and Emergency bookings.
