document.addEventListener('DOMContentLoaded', () => {
    // PENTING: Ganti dengan URL tempat Anda meng-hosting folder backend
    const BACKEND_URL = 'https://nama-domain-hosting-anda.com/backend/api/';

    const loginForm = document.getElementById('loginForm');
    const createPanelForm = document.getElementById('createPanelForm');
    const createUserForm = document.getElementById('createUserForm');
    const messageDiv = document.getElementById('message');
    const themeSelector = document.getElementById('theme-selector');
    const audio = document.getElementById('background-audio');

    const showMessage = (msg, isSuccess) => {
        if (messageDiv) {
            messageDiv.innerHTML = msg;
            messageDiv.style.color = isSuccess ? 'lightgreen' : 'salmon';
        }
    };

    // Logika Tema
    const applyTheme = (theme) => {
        if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    };
    if (themeSelector) {
        themeSelector.addEventListener('change', (e) => {
            localStorage.setItem('theme', e.target.value);
            applyTheme(e.target.value);
        });
        const savedTheme = localStorage.getItem('theme') || 'system';
        themeSelector.value = savedTheme;
        applyTheme(savedTheme);
    }

    // Logika Backsound
    const playMusic = () => {
        if (audio && audio.paused) {
            audio.play().catch(e => console.log("Browser mencegah autoplay."));
        }
    };
    if(audio) {
        document.body.addEventListener('click', playMusic, { once: true });
        document.body.addEventListener('keydown', playMusic, { once: true });
    }
    
    // Logika Halaman Login
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const license_key = document.getElementById('license_key').value;
            const response = await fetch(BACKEND_URL + 'login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, license_key })
            });
            const result = await response.json();
            if (result.success) {
                window.location.href = result.isAdmin ? 'admin.html' : 'panel.html';
            } else {
                showMessage(result.message, false);
            }
        });
    }

    // Logika Halaman User
    if (createPanelForm) {
        createPanelForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            showMessage('Sedang memproses, mohon tunggu...', true);
            const response = await fetch(BACKEND_URL + 'create_panel.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    username: document.getElementById('username').value,
                    password: document.getElementById('password').value,
                    telegram_id: document.getElementById('telegram_id').value,
                    ram: document.getElementById('ram').value,
                    disk: document.getElementById('disk').value,
                    cpu: document.getElementById('cpu').value
                })
            });
            const result = await response.json();
            if(result.message === 'Anda harus login terlebih dahulu.') {
                window.location.href = 'index.html';
            }
            showMessage(result.message, result.success);
            if (result.success) createPanelForm.reset();
        });
    }

    // Logika Halaman Admin
    if (createUserForm) {
        createUserForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            showMessage('Membuat user...', true);
            const response = await fetch(BACKEND_URL + 'create_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: document.getElementById('new_username').value,
                    active_days: document.getElementById('active_days').value,
                    isAdmin: document.getElementById('is_admin').checked
                })
            });
            const result = await response.json();
            if(result.message === 'Akses ditolak. Hanya untuk Admin.') {
                window.location.href = 'index.html';
            }
            if (result.success) {
                const successMsg = `User Berhasil Dibuat! <br><br><strong>Username:</strong> ${result.username} <br><strong>Key License:</strong> ${result.license_key} <br><strong>Aktif Sampai:</strong> ${result.expiry_date}`;
                showMessage(successMsg, true);
                createUserForm.reset();
            } else {
                showMessage(result.message, false);
            }
        });
    }
});