<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QueueLess | Smart Queue Management</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="navbar">
  <a href="#" class="brand"><span class="brand-icon">Q</span><span><strong>QueueLess</strong><small>Smart Queue Management</small></span></a>
  <div class="nav-status"><span class="live-dot"></span> System Online</div>
</header>
<main class="container">
  <section id="roleSelectionScreen" class="panel auth-panel">
    <span class="panel-label">PORTAL LOGIN</span><h2>Select Your Role</h2>
    <p class="muted intro">Choose portal to access the system.</p>
    <div class="role-grid">
      <button id="goToCustomerAuth" class="primary-btn role-btn"><span>👤</span>Patient Portal</button>
      <button id="goToAdmin" class="primary-btn role-btn admin-role"><span>👨‍⚕️</span>Admin Desk</button>
    </div>
  </section>

  <section id="customerAuthSection" class="panel auth-panel hidden">
    <div id="customerLoginBox">
      <span class="panel-label">PATIENT LOGIN</span><h2>Welcome Back</h2>
      <p class="muted intro">Enter your credentials to book OPD.</p>
      <form id="customerLoginForm">
        <div class="form-group"><label>Email / Mobile *</label><input type="text" id="loginUsername" required autocomplete="username" placeholder="Registered mobile or email"></div>
        <div class="form-group"><label>Password *</label><input type="password" id="loginPassword" required autocomplete="current-password" placeholder="Password"></div>
        <button class="primary-btn" type="submit">Login</button><p id="loginMsg" class="message"></p>
      </form>
      <p class="switch-text">Don't have an account? <a href="#" id="showRegisterBtn">Create Account</a></p>
    </div>
    <div id="customerRegisterBox" class="hidden">
      <span class="panel-label">NEW PATIENT</span><h2>Create Account</h2>
      <p class="muted intro">Register to access OPD booking.</p>
      <form id="customerRegisterForm">
        <div class="form-group"><label>Full Name *</label><input type="text" id="regName" required minlength="3" autocomplete="name"></div>
        <div class="form-group"><label>Mobile / Email *</label><input type="text" id="regUsername" required autocomplete="username"></div>
        <div class="form-group"><label>Create Password *</label><input type="password" id="regPassword" required minlength="8" autocomplete="new-password"></div>
        <button class="primary-btn" type="submit">Register &amp; Login</button><p id="regMsg" class="message"></p>
      </form>
      <p class="switch-text">Already have an account? <a href="#" id="showLoginBtn">Login here</a></p>
    </div>
    <button id="backToRoleBtn" class="secondary-btn full-width">← Back to Role Selection</button>
  </section>

  <section id="adminPinScreen" class="panel auth-panel hidden">
    <span class="panel-label">SECURITY</span><h2>Admin Login</h2>
    <p class="muted intro">Use the admin account configured in the database.</p>
    <form id="adminLoginForm">
      <div class="form-group"><label>Admin username</label><input id="adminUsername" required value="admin"></div>
      <div class="form-group"><label>Password</label><input type="password" id="adminPassword" required></div>
      <button class="primary-btn admin-role" type="submit">Login as Admin</button><p id="pinError" class="message"></p>
    </form>
    <button id="backFromAdmin" class="secondary-btn full-width">← Back</button>
  </section>

  <section id="customerSection" class="hidden">
    <div class="section-top"><h2>👤 Welcome, <span id="loggedUserName" class="primary-text">Patient</span></h2><button id="logoutCustomerBtn" class="secondary-btn">Logout</button></div>
    <div class="dashboard-grid">
      <div class="panel">
        <span class="panel-label">APPOINTMENT BOOKING</span><h2>OPD Registration Form</h2>
        <form id="appointmentForm" class="top-gap">
          <div class="form-group"><label>Patient Full Name *</label><input type="text" id="customerName" required minlength="3"></div>
          <div class="form-group"><label>Select OPD Department &amp; Doctor *</label>
            <select id="service" required>
              <option value="">Choose Department &amp; Doctor</option>
              <optgroup label="General Medicine"><option>General Medicine - Dr. R. K. Sharma (MBBS, MD)</option><option>General Medicine - Dr. Anjali Verma (MBBS)</option></optgroup>
              <optgroup label="Cardiology"><option>Cardiology - Dr. S. P. Mehta (DM, FACC)</option></optgroup>
              <optgroup label="Pediatrics (Child Care)"><option>Pediatrics - Dr. Neha Gupta (MD - Pediatrics)</option></optgroup>
              <optgroup label="Orthopedics"><option>Orthopedics - Dr. Vikram Rathore (MS - Ortho)</option></optgroup>
              <optgroup label="Dermatology (Skin)"><option>Dermatology - Dr. Manoj Kulkarni (MD - Skin)</option></optgroup>
            </select>
          </div>
          <div class="form-group"><label>Priority *</label><div class="radio-group">
            <label><input type="radio" name="priority" value="General" checked> General</label>
            <label><input type="radio" name="priority" value="Senior"> Senior</label>
            <label><input type="radio" name="priority" value="Emergency"> Emergency</label>
          </div></div>
          <div class="form-group" id="appointmentTimeGroup"><label>Preferred Time *</label><input type="time" id="appointmentTime" required></div>
          <div class="form-group"><label>Geolocation (optional)</label><div class="geo-row"><button type="button" id="geoBtn" class="secondary-btn">📍 Get GPS</button><span id="geoStatus" class="muted small">Not fetched</span></div></div>
          <div class="form-group"><label>Symptoms / Notes (optional)</label><textarea id="notes" rows="3" placeholder="Describe symptoms..."></textarea></div>
          <div class="form-group checkbox-line"><input type="checkbox" id="terms" required><label for="terms">I agree to hospital rules *</label></div>
          <button type="submit" class="primary-btn">Submit Form &amp; Get Token</button><p id="message" class="message"></p>
        </form>
      </div>
      <div class="side-stack">
        <div class="panel">
          <span class="panel-label">LIVE STATUS</span><h2>My Token Tracker</h2>
          <div class="token-card"><span>Assigned Token</span><strong id="tokenNumber">--</strong><small id="queueStatus">No active booking</small></div>
          <div class="status-details"><div><span>People Ahead</span><strong id="peopleAhead">--</strong></div><div><span>Status</span><strong id="liveStatus">Waiting</strong></div></div>
          <button id="cancelBtn" class="danger-btn full-width top-gap hidden">Cancel Booking</button>
        </div>
        <div class="panel">
          <span class="panel-label">FEEDBACK</span><h2>Patient Feedback Portal</h2>
          <form id="feedbackForm" class="top-gap">
            <div class="form-group"><label>Service Rating *</label><select id="feedbackRating" required><option value="">Choose rating</option><option value="5">5 - Excellent</option><option value="4">4 - Good</option><option value="3">3 - Average</option><option value="2">2 - Poor</option><option value="1">1 - Very Poor</option></select></div>
            <div class="form-group"><label>Suggestions / Comments *</label><textarea id="feedbackText" rows="3" required></textarea></div>
            <button type="submit" class="primary-btn success-btn">Submit Feedback</button><p id="feedbackMsg" class="message"></p>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section id="adminSection" class="hidden">
    <div class="section-top"><h2>👨‍⚕️ Hospital Admin Desk</h2><button id="logoutAdminBtn" class="secondary-btn">Logout</button></div>
    <div class="dashboard-grid">
      <div class="panel admin-call-panel"><span class="panel-label">ADMIN CONTROLS</span><h2>OPD Chamber #1</h2><p class="muted">Call the next patient in the queue.</p><button id="nextCustomerBtn" class="primary-btn top-gap">📢 Call Next Patient</button><p id="adminMsg" class="message"></p></div>
      <div class="panel"><div class="queue-heading"><div><span class="panel-label">QUEUE</span><h2>Waiting Patients</h2></div><div class="queue-count"><strong id="queueCount">0</strong><span>Active</span></div></div><div id="queueList" class="queue-list top-gap"><div class="empty-state">No patients in queue</div></div></div>
      <div class="panel feedback-admin-panel">
        <div class="queue-heading">
          <div><span class="panel-label">PATIENT EXPERIENCE</span><h2>Patient Feedback</h2><p class="muted intro">Reviews and suggestions submitted by patients.</p></div>
          <div class="queue-count"><strong id="feedbackCount">0</strong><span>Reviews</span></div>
        </div>
        <div id="adminFeedbackList" class="feedback-list top-gap"><div class="empty-state">No feedback submitted yet.</div></div>
      </div>
    </div>
  </section>
</main>
<footer>QueueLess · Smart Queue Management Prototype</footer>
<script src="script.js"></script>
</body>
</html>
