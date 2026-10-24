'use strict';

const $ = (id) => document.getElementById(id);
const screens = [
  'roleSelectionScreen', 'customerAuthSection', 'adminPinScreen',
  'customerSection', 'adminSection'
];

let userGPS = '';
let refreshTimer = null;

function updatePreferredTimeVisibility() {
    const selectedPriority =
        document.querySelector('input[name="priority"]:checked')?.value || 'General';

    const timeGroup = document.getElementById('appointmentTimeGroup');
    const timeInput = document.getElementById('appointmentTime');

    if (!timeGroup || !timeInput) return;

    let isEmergency;

    switch (selectedPriority) {
        case 'Emergency':
            isEmergency = true;
            break;

        case 'Senior':
        case 'General':
        default:
            isEmergency = false;
            break;
    }

    timeGroup.classList.toggle('hidden', isEmergency);
    timeInput.required = !isEmergency;

    if (isEmergency) timeInput.value = '';
}

document.querySelectorAll('input[name="priority"]').forEach((radio) => {
  radio.addEventListener('change', updatePreferredTimeVisibility);
});
document.addEventListener('DOMContentLoaded', updatePreferredTimeVisibility);

let currentUser = null;
let queueRequestInProgress = false;
let nextRequestInProgress = false;
let feedbackRequestInProgress = false;

function showScreen(id) {
  screens.forEach((screen) => {
    const element = $(screen);
    if (element) element.classList.toggle('hidden', screen !== id);
  });
}

function setMessage(id, message, type = 'error') {
  const element = $(id);
  if (!element) return;
  element.textContent = message;
  element.className = message ? `message ${type}` : 'message';
}

async function api(action, data = {}, method = 'POST') {
  const options = {
    method,
    headers: { Accept: 'application/json' },
    credentials: 'same-origin'
  };

  let url = 'api.php';
  if (method === 'GET') {
    url += `?action=${encodeURIComponent(action)}`;
  } else {
    options.headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify({ ...data, action });
  }

  const response = await fetch(url, options);
  const raw = await response.text();
  let result;
  try {
    result = JSON.parse(raw);
  } catch (_) {
    throw new Error('Server returned an invalid response. Check PHP errors in XAMPP.');
  }

  if (!response.ok || !result.success) {
    throw new Error(result.message || `Request failed (${response.status}).`);
  }
  return result;
}

function stopRefresh() {
  if (refreshTimer !== null) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
}

function startRefresh() {
  stopRefresh();
  refreshQueue();
  if (currentUser?.role === 'admin') refreshFeedback();
  refreshTimer = setInterval(() => {
    if (document.visibilityState === 'hidden') return;
    refreshQueue();
    if (currentUser?.role === 'admin') refreshFeedback();
  }, 5000);
}

function showPatient(user) {
  currentUser = user;
  $('loggedUserName').textContent = user.name || 'Patient';
  $('customerName').value = user.name || '';
  showScreen('customerSection');
  setMessage('message', '');
  setMessage('feedbackMsg', '');
  startRefresh();
}

$('goToCustomerAuth').addEventListener('click', () => showScreen('customerAuthSection'));
$('goToAdmin').addEventListener('click', () => showScreen('adminPinScreen'));
$('backToRoleBtn').addEventListener('click', () => showScreen('roleSelectionScreen'));
$('backFromAdmin').addEventListener('click', () => showScreen('roleSelectionScreen'));

$('showRegisterBtn').addEventListener('click', (event) => {
  event.preventDefault();
  $('customerLoginBox').classList.add('hidden');
  $('customerRegisterBox').classList.remove('hidden');
});
$('showLoginBtn').addEventListener('click', (event) => {
  event.preventDefault();
  $('customerRegisterBox').classList.add('hidden');
  $('customerLoginBox').classList.remove('hidden');
});

$('customerRegisterForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const button = event.currentTarget.querySelector('button[type="submit"]');
  button.disabled = true;
  try {
    const result = await api('register', {
      name: $('regName').value.trim(),
      username: $('regUsername').value.trim(),
      password: $('regPassword').value
    });
    $('customerRegisterForm').reset();
    showPatient(result.user);
  } catch (error) {
    setMessage('regMsg', error.message);
  } finally {
    button.disabled = false;
  }
});

$('customerLoginForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const button = event.currentTarget.querySelector('button[type="submit"]');
  button.disabled = true;
  try {
    const result = await api('login', {
      username: $('loginUsername').value.trim(),
      password: $('loginPassword').value
    });
    $('customerLoginForm').reset();
    showPatient(result.user);
  } catch (error) {
    setMessage('loginMsg', error.message);
  } finally {
    button.disabled = false;
  }
});

$('adminLoginForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const button = event.currentTarget.querySelector('button[type="submit"]');
  button.disabled = true;
  try {
    const result = await api('admin_login', {
      username: $('adminUsername').value.trim(),
      password: $('adminPassword').value
    });
    currentUser = result.user;
    $('adminPassword').value = '';
    showScreen('adminSection');
    setMessage('adminMsg', '');
    startRefresh();
  } catch (error) {
    setMessage('pinError', error.message);
  } finally {
    button.disabled = false;
  }
});

async function logout() {
  stopRefresh();
  try {
    await api('logout');
  } catch (error) {
    console.warn('Logout request failed:', error.message);
  }
  currentUser = null;
  userGPS = '';
  showScreen('roleSelectionScreen');
}
$('logoutCustomerBtn').addEventListener('click', logout);
$('logoutAdminBtn').addEventListener('click', logout);

$('geoBtn').addEventListener('click', () => {
  if (!navigator.geolocation) {
    $('geoStatus').textContent = 'Geolocation is not supported by this browser.';
    return;
  }
  $('geoStatus').textContent = 'Fetching location...';
  navigator.geolocation.getCurrentPosition(
    (position) => {
      userGPS = `Lat: ${position.coords.latitude.toFixed(5)}, Lon: ${position.coords.longitude.toFixed(5)}`;
      $('geoStatus').textContent = userGPS;
    },
    (error) => {
      userGPS = '';
      $('geoStatus').textContent = error.code === error.PERMISSION_DENIED
        ? 'Location permission denied.'
        : 'Could not fetch location.';
    },
    { enableHighAccuracy: false, timeout: 10000, maximumAge: 60000 }
  );
});

$('appointmentForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button[type="submit"]');
  if (!form.reportValidity()) return;
  button.disabled = true;
  try {
    const result = await api('book', {
      patient_name: $('customerName').value.trim(),
      department_doctor: $('service').value,
      priority: document.querySelector('input[name="priority"]:checked')?.value || 'General',
      appointment_time: document.querySelector('input[name="priority"]:checked')?.value === 'Emergency'
        ? new Date().toTimeString().slice(0, 5)
        : $('appointmentTime').value,
      notes: $('notes').value.trim(),
      gps: userGPS
    });
    setMessage('message', `Appointment booked successfully. Your token is #${result.appointment.token}.`, 'success');
    form.reset();
    $('customerName').value = currentUser?.name || '';
    $('geoStatus').textContent = 'Not fetched';
    userGPS = '';
    await refreshQueue();
  } catch (error) {
    setMessage('message', error.message);
  } finally {
    button.disabled = false;
  }
});

$('cancelBtn').addEventListener('click', async () => {
  if (!confirm('Cancel your active appointment?')) return;
  const button = $('cancelBtn');
  button.disabled = true;
  try {
    await api('cancel');
    setMessage('message', 'Your appointment has been cancelled.', 'success');
    await refreshQueue();
  } catch (error) {
    setMessage('message', error.message);
  } finally {
    button.disabled = false;
  }
});

$('feedbackForm').addEventListener('submit', async (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const button = form.querySelector('button[type="submit"]');
  button.disabled = true;
  try {
    await api('feedback', {
      rating: Number($('feedbackRating').value),
      suggestion: $('feedbackText').value.trim()
    });
    setMessage('feedbackMsg', 'Thank you! Your feedback has been submitted.', 'success');
    form.reset();
  } catch (error) {
    setMessage('feedbackMsg', error.message);
  } finally {
    button.disabled = false;
  }
});

$('nextCustomerBtn').addEventListener('click', async () => {
  if (nextRequestInProgress) return;
  nextRequestInProgress = true;
  const button = $('nextCustomerBtn');
  button.disabled = true;
  try {
    const result = await api('next');
    setMessage('adminMsg', result.message, 'success');
    await refreshQueue();
  } catch (error) {
    setMessage('adminMsg', error.message);
  } finally {
    nextRequestInProgress = false;
    button.disabled = false;
  }
});

function escapeHTML(value) {
  return String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  })[character]);
}

async function refreshFeedback() {
  if (!currentUser || currentUser.role !== 'admin' || feedbackRequestInProgress) return;
  feedbackRequestInProgress = true;
  try {
    const result = await api('feedback_list', {}, 'GET');
    const list = $('adminFeedbackList');
    const count = $('feedbackCount');
    if (!list || !count) return;

    count.textContent = String(result.feedback.length);
    list.replaceChildren();

    if (result.feedback.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'empty-state';
      empty.textContent = 'No feedback submitted yet.';
      list.appendChild(empty);
      return;
    }

    result.feedback.forEach((entry) => {
      const card = document.createElement('article');
      card.className = 'feedback-item';

      const header = document.createElement('div');
      header.className = 'feedback-item-header';
      const patient = document.createElement('div');
      patient.className = 'feedback-patient';
      const name = document.createElement('strong');
      name.textContent = entry.patient_name || 'Patient';
      const username = document.createElement('span');
      username.textContent = entry.patient_username || '';
      patient.append(name, username);

      const rating = document.createElement('span');
      rating.className = 'feedback-rating';
      rating.textContent = `${'★'.repeat(Math.max(0, Math.min(5, Number(entry.rating) || 0)))}${'☆'.repeat(5 - Math.max(0, Math.min(5, Number(entry.rating) || 0)))}`;
      rating.setAttribute('aria-label', `${Number(entry.rating) || 0} out of 5 stars`);
      header.append(patient, rating);

      const comment = document.createElement('p');
      comment.className = 'feedback-comment';
      comment.textContent = entry.suggestion || 'No comment provided.';

      const date = document.createElement('time');
      date.className = 'feedback-date';
      date.textContent = entry.created_at ? new Date(String(entry.created_at).replace(' ', 'T')).toLocaleString() : 'Date unavailable';
      card.append(header, comment, date);
      list.appendChild(card);
    });
  } catch (error) {
    console.error('Feedback refresh failed:', error);
  } finally {
    feedbackRequestInProgress = false;
  }
}

async function refreshQueue() {
  if (!currentUser || queueRequestInProgress) return;
  queueRequestInProgress = true;
  try {
    const result = await api('queue', {}, 'GET');
    const list = $('queueList');
    if (!list) return;
    list.replaceChildren();
    $('queueCount').textContent = String(result.queue.length);

    if (result.queue.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'empty-state';
      empty.textContent = 'No patients in queue';
      list.appendChild(empty);
    } else {
      result.queue.forEach((patient) => {
        const item = document.createElement('div');
        item.className = 'queue-item';
        const top = document.createElement('div');
        top.className = 'queue-item-top';
        const token = document.createElement('span');
        token.className = 'queue-token';
        token.textContent = `Token #${Number(patient.token)}`;
        const priority = document.createElement('span');
        priority.className = `priority ${String(patient.priority).toLowerCase()}`;
        priority.textContent = patient.priority;
        top.append(token, priority);
        const name = document.createElement('strong');
        name.textContent = patient.patient_name || 'Patient';
        const details = document.createElement('div');
        details.className = 'queue-detail';
        details.append(document.createTextNode(`Department: ${patient.department_doctor || 'N/A'}`));
        details.append(document.createElement('br'));
        details.append(document.createTextNode(`Time: ${patient.appointment_time || 'N/A'}`));
        details.append(document.createElement('br'));
        details.append(document.createTextNode(`Status: ${patient.status || 'Waiting'}`));
        if (patient.notes) {
          details.append(document.createElement('br'));
          details.append(document.createTextNode(`Notes: ${patient.notes}`));
        }
        item.append(top, name, details);
        list.appendChild(item);
      });
    }

    const mine = result.my_appointment;
    $('tokenNumber').textContent = mine ? String(mine.token) : '--';
    $('queueStatus').textContent = mine ? mine.status : 'No active booking';
    $('liveStatus').textContent = mine ? mine.status : 'No booking';

    if (!mine) {
      $('peopleAhead').textContent = '--';
    } else if (mine.status === 'In Consultation') {
      $('peopleAhead').textContent = '0';
    } else {
      const myIndex = result.queue.findIndex((patient) => Number(patient.id) === Number(mine.id));
      // The patient currently in consultation is not counted as a waiting patient ahead.
      const ahead = myIndex < 0 ? 0 : result.queue.slice(0, myIndex).filter((patient) => patient.status === 'Waiting').length;
      $('peopleAhead').textContent = String(ahead);
    }

    $('cancelBtn').classList.toggle('hidden', !mine || mine.status !== 'Waiting');
  } catch (error) {
    console.error('Queue refresh failed:', error);
    if (error.message.toLowerCase().includes('log in')) {
      stopRefresh();
      currentUser = null;
      showScreen('roleSelectionScreen');
    }
  } finally {
    queueRequestInProgress = false;
  }
}
