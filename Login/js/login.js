document.getElementById('togglePw').addEventListener('click', function() {
    const pw = document.getElementById('password');
    pw.type = pw.type === 'password' ? 'text' : 'password';
});

function handleLogin() {
    const email    = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const err      = document.getElementById('errorMsg');
    const btn      = document.getElementById('loginBtn');

    if (!email || !password) {
        err.style.display = 'flex';
        err.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Please enter your email and password.`;
        return;
    }

    err.style.display = 'none';
    btn.disabled = true;
    btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> Signing in...`;

    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('backend/auth/login.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            err.style.display = 'flex';
            err.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            ${data.message}`;
            btn.disabled = false;
            btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg> Sign In`;
        }
    })
    .catch(() => {
        err.style.display = 'flex';
        err.textContent = 'Server error. Please try again.';
        btn.disabled = false;
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Enter') handleLogin();
});

