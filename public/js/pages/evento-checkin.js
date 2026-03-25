function handleCheckin(e) {
    e.preventDefault();
    const name = document.getElementById('visitor-name').value;
    const email = document.getElementById('visitor-email').value;
    const errorMsg = document.getElementById('error-msg');
    
    // Security Rule Simulation: Check LocalStorage for unique device checkin
    // and session-based emails (simulated with localStorage for demo)
    const checkinList = JSON.parse(localStorage.getItem('event_checkins') || '[]');
    const checkinData = JSON.parse(localStorage.getItem('event_checkins_data') || '[]');
    const deviceCheckin = localStorage.getItem('already_checked_in');
    const alreadyCheckedInEmail = localStorage.getItem('already_checked_in');
    const existingCheckins = JSON.parse(localStorage.getItem('event_checkins_data') || '[]');

    if (alreadyCheckedInEmail === email.toLowerCase() || existingCheckins.some(item => item.email.toLowerCase() === email.toLowerCase())) {
        errorMsg.style.display = 'block';
        return;
    }

    // Save (Consolidated to single key to remove redundancy)
    const checkinsData = JSON.parse(localStorage.getItem('event_checkins_data') || '[]');
    checkinsData.push({ name, email, time: new Date().toISOString() });
    localStorage.setItem('event_checkins_data', JSON.stringify(checkinsData));

    localStorage.setItem('already_checked_in', email.toLowerCase());

    // Show success
    document.getElementById('checkin-card').style.display = 'none';
    document.getElementById('success-card').style.display = 'block';
}
