(() => {
  const APP_ORIGIN = '/BusinessEventManagementSystem';
  const API_BASE = `${APP_ORIGIN}/api/`;
  const APP_URL = `${APP_ORIGIN}/public/index.html`;

  const openApp = (e) => {
    if (e) e.preventDefault();
    window.location.href = APP_URL;
  };

  const navLoginBtn = document.getElementById('navLoginBtn');
  if (navLoginBtn) navLoginBtn.addEventListener('click', openApp);

  const heroLoginBtn = document.getElementById('heroLoginBtn');
  if (heroLoginBtn) heroLoginBtn.addEventListener('click', openApp);

  const heroSignupBtn = document.getElementById('heroSignupBtn');
  if (heroSignupBtn) heroSignupBtn.addEventListener('click', openApp);

  const ctaSignupBtn = document.getElementById('ctaSignupBtn');
  if (ctaSignupBtn) ctaSignupBtn.addEventListener('click', openApp);

  // NOTE: Landing modal auth is unreliable in some browser automation runs.
  // We intentionally route to the real app page for Login/Register.

  const switchToRegister = document.getElementById('switchToRegister');
  if (switchToRegister) {
    switchToRegister.addEventListener('click', (e) => {
      e.preventDefault();
      showAuthForms('register');
    });
  }

  const switchToLogin = document.getElementById('switchToLogin');
  if (switchToLogin) {
    switchToLogin.addEventListener('click', (e) => {
      e.preventDefault();
      showAuthForms('login');
    });
  }

  const authModalEl = document.getElementById('authModal');
  const authModal = authModalEl ? new bootstrap.Modal(authModalEl) : null;

  // If user clicks hero buttons, open modal first (more user-friendly),
  // otherwise just open the app.
  const shouldOpenModalForHero = true;
  if (navLoginBtn || heroLoginBtn) {
    const wireHeroClick = (btn) => {
      if (!btn) return;
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        if (shouldOpenModalForHero && authModal) {
          showAuthForms('login');
          authModal.show();
        } else {
          openApp();
        }
      });
    };
    wireHeroClick(navLoginBtn);
    wireHeroClick(heroLoginBtn);
    // Signup open: show register form in modal
    if (heroSignupBtn) {
      heroSignupBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (authModal) {
          showAuthForms('register');
          authModal.show();
        } else {
          openApp(e);
        }
      });
    }
    if (ctaSignupBtn) {
      ctaSignupBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (authModal) {
          showAuthForms('register');
          authModal.show();
        } else {
          openApp(e);
        }
      });
    }
  }

  function showAuthForms(mode) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (mode === 'register') {
      if (loginForm) loginForm.style.display = 'none';
      if (registerForm) registerForm.style.display = 'block';
    } else {
      if (loginForm) loginForm.style.display = 'block';
      if (registerForm) registerForm.style.display = 'none';
    }
  }

  function setError(el, msg) {
    if (!el) return;
    el.textContent = msg || '';
    el.style.display = msg ? 'block' : 'none';
  }

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    // Some endpoints might return JSON error payloads; always try to parse
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      return { success: false, message: text || `Request failed (${res.status})` };
    }
  }

  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  const landingErrorBox = document.getElementById('authErrorBox');

  // Login handler (landing modal form)
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('loginEmail')?.value?.trim();
      const password = document.getElementById('loginPassword')?.value;

      setError(landingErrorBox, '');

      if (!email || !password) {
        setError(landingErrorBox, 'Email and password are required.');
        return;
      }

      const response = await postJson(`${API_BASE}auth.php?action=login`, { email, password });

      if (response.success) {
        // Store token and go to app
        localStorage.setItem('auth_token', response.data.token);
        window.location.href = APP_URL;
        return;
      }

      // Descriptive messages
      const msg = response.message || 'Invalid email or password. Please try again.';
      setError(landingErrorBox, msg);
    });
  }

  // Register handler (landing modal form)
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();

      const name = document.getElementById('registerName')?.value?.trim();
      const email = document.getElementById('registerEmail')?.value?.trim();
      const phone = document.getElementById('registerPhone')?.value?.trim();
      const company = document.getElementById('registerCompany')?.value?.trim();
      const password = document.getElementById('registerPassword')?.value;

      setError(landingErrorBox, '');

      if (!name || !email || !phone || !company || !password) {
        setError(landingErrorBox, 'All fields are required to create an account.');
        return;
      }

      const response = await postJson(`${API_BASE}auth.php?action=register`, {
        name,
        email,
        phone,
        company,
        password
        // role is intentionally not sent; backend enforces first-user=admin rule
      });

      if (response.success) {
        // After registration, switch to login tab and show a friendly message.
        setError(landingErrorBox, '');
        alert('Account created successfully. Please login.');
        showAuthForms('login');
        return;
      }

      if (response.errors && typeof response.errors === 'object') {
        const firstField = Object.keys(response.errors)[0];
        const msg = response.errors[firstField] || response.message || 'Registration failed.';
        setError(landingErrorBox, msg);
        return;
      }

      const msg = response.message || 'Registration failed. Please verify your details and try again.';
      setError(landingErrorBox, msg);
    });
  }

})();
