// assets/js/login.js

class LoginManager {
    constructor() {
        this.initializeFormToggle();
        this.initializePasswordToggles();
        this.initializeFormSubmission();
        this.initializeAlerts();
        this.initializeFloatingLabels();
        this.initializeRememberMe();
        this.initializeBackgroundEffects();
    }

    /* ===== FORM TOGGLE ===== */
    initializeFormToggle() {
        const triggerBtn  = document.getElementById('showLoginBtn');
        const formWrapper = document.getElementById('loginFormWrapper');
        const divider     = document.getElementById('formDivider');
        if (!triggerBtn || !formWrapper) return;

        // Auto-open if there are validation errors
        if (document.querySelector('.alert-danger')) {
            this._openForm(triggerBtn, formWrapper, divider);
        }

        triggerBtn.addEventListener('click', () => {
            formWrapper.classList.contains('is-open')
                ? this._closeForm(triggerBtn, formWrapper, divider)
                : this._openForm(triggerBtn, formWrapper, divider);
        });
    }

    _openForm(btn, wrapper, divider) {
        wrapper.classList.add('is-open');
        btn.classList.add('is-open');
        if (divider) divider.classList.add('is-open');
        const label = btn.querySelector('.btn-label');
        if (label) label.textContent = 'Hide Form';
        setTimeout(() => {
            const first = wrapper.querySelector('input');
            if (first) first.focus();
        }, 500);
    }

    _closeForm(btn, wrapper, divider) {
        wrapper.classList.remove('is-open');
        btn.classList.remove('is-open');
        if (divider) divider.classList.remove('is-open');
        const label = btn.querySelector('.btn-label');
        if (label) {
            label.textContent = document.body.classList.contains('admin-bg')
                ? 'Admin Sign In'
                : 'Staff Sign In';
        }
    }

    /* ===== PASSWORD TOGGLE ===== */
    initializePasswordToggles() {
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const wrapper = button.closest('.password-wrapper');
                if (!wrapper) return;
                const input = wrapper.querySelector('input');
                const icon  = button.querySelector('i');
                if (input && icon) {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                    button.style.transform = 'translateY(-50%) scale(1.2)';
                    setTimeout(() => { button.style.transform = 'translateY(-50%) scale(1)'; }, 200);
                    input.focus();
                }
            });
        });
    }

    /* ===== FORM SUBMISSION ===== */
    initializeFormSubmission() {
        const loginForm = document.getElementById('loginForm') || document.getElementById('staffLoginForm');
        if (!loginForm) return;
        const submitBtn = loginForm.querySelector('button[type="submit"]');

        loginForm.addEventListener('submit', (e) => {
            if (!this.validateForm(loginForm)) { e.preventDefault(); return; }
            if (loginForm.dataset.submitted) { e.preventDefault(); return; }
            loginForm.dataset.submitted = 'true';
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                submitBtn.dataset.originalHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Signing in...';
            }
            setTimeout(() => {
                if (submitBtn && submitBtn.disabled) {
                    this.resetButton(submitBtn);
                    loginForm.dataset.submitted = '';
                }
            }, 10000);
        });

        loginForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => this.validateInput(input));
            input.addEventListener('blur',  () => this.validateInput(input));
        });
    }

    validateInput(input) {
        const value = input.value.trim();
        if (input.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && !emailRegex.test(value)) {
                this.showInputError(input, 'Please enter a valid email address');
                return false;
            }
        }
        if (input.type === 'password' && value && value.length < 6) {
            this.showInputError(input, 'Password must be at least 6 characters');
            return false;
        }
        this.clearInputError(input);
        return true;
    }

    showInputError(input, message) {
        input.classList.add('is-invalid');
        let errorDiv = input.nextElementSibling;
        if (!errorDiv?.classList.contains('invalid-feedback')) errorDiv = input.parentElement.nextElementSibling;
        if (errorDiv?.classList.contains('invalid-feedback')) errorDiv.textContent = message;
    }

    clearInputError(input) {
        input.classList.remove('is-invalid');
        let errorDiv = input.nextElementSibling;
        if (!errorDiv?.classList.contains('invalid-feedback')) errorDiv = input.parentElement.nextElementSibling;
        if (errorDiv?.classList.contains('invalid-feedback')) errorDiv.textContent = '';
    }

    validateForm(form) {
        let isValid = true;
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) { this.showInputError(input, 'This field is required'); isValid = false; }
            else if (!this.validateInput(input)) { isValid = false; }
        });
        return isValid;
    }

    resetButton(button) {
        button.classList.remove('loading');
        button.disabled = false;
        if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
    }

    /* ===== ALERTS ===== */
    initializeAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.addEventListener('click', () => this.closeAlert(alert));
            setTimeout(() => { if (alert.parentElement) this.closeAlert(alert); }, 6000);
        });
    }

    closeAlert(alert) {
        alert.style.transition = 'all 0.3s ease';
        alert.style.opacity   = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => { if (alert.parentElement) alert.remove(); }, 300);
    }

    /* ===== FLOATING LABELS ===== */
    initializeFloatingLabels() {
        document.querySelectorAll('.form-control').forEach(input => {
            const label = input.previousElementSibling;
            if (!label?.classList.contains('form-label')) return;
            if (input.value) label.style.color = 'var(--primary-blue)';
            input.addEventListener('focus', () => { label.style.color = 'var(--primary-blue)'; });
            input.addEventListener('blur',  () => { if (!input.value) label.style.color = ''; });
        });
    }

    /* ===== REMEMBER ME ===== */
    initializeRememberMe() {
        const rememberCheck = document.getElementById('remember');
        if (rememberCheck && localStorage.getItem('rememberEmail')) {
            rememberCheck.checked = true;
            const emailInput = document.querySelector('input[name="email"]');
            if (emailInput) emailInput.value = localStorage.getItem('rememberEmail');
        }
        rememberCheck?.addEventListener('change', (e) => {
            const emailInput = document.querySelector('input[name="email"]');
            if (e.target.checked && emailInput?.value) localStorage.setItem('rememberEmail', emailInput.value);
            else localStorage.removeItem('rememberEmail');
        });
    }

    /* ===== BACKGROUND EFFECTS ===== */
    initializeBackgroundEffects() {
        if (!document.querySelector('.bubble-container')) return;
        this.createBubbles();
        this.createParticles();
        window.addEventListener('resize', () => this.recreateBackgroundEffects());
    }

    createBubbles() {
        const container = document.querySelector('.bubble-container');
        if (!container) return;
        container.innerHTML = '';
        const fragment = document.createDocumentFragment();

        for (let i = 0; i < 35; i++) {
            const bubble   = document.createElement('div');
            bubble.className = 'bubble';
            const size     = Math.random() * 90 + 18;
            const left     = Math.random() * 100;
            const duration = Math.random() * 14 + 9;
            const delay    = Math.random() * 8;
            const opacity  = Math.random() * 0.35 + 0.55;
            const drift    = (Math.random() - 0.5) * 80;

            bubble.style.cssText = `
                width:${size}px;height:${size}px;
                left:${left}%;
                animation-duration:${duration}s;
                animation-delay:-${delay}s;
                opacity:${opacity};
                --drift:${drift}px;
            `;
            fragment.appendChild(bubble);
        }
        container.appendChild(fragment);
    }

    createParticles() {
        const container = document.querySelector('.floating-particles');
        if (!container) return;
        container.innerHTML = '';
        const types    = ['particle-drop', 'particle-shimmer', 'particle-soap'];
        const fragment = document.createDocumentFragment();

        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            const type     = types[Math.floor(Math.random() * types.length)];
            particle.className = `particle ${type}`;
            particle.style.cssText = `
                left:${Math.random() * window.innerWidth}px;
                top:${Math.random() * window.innerHeight}px;
                animation:${type === 'particle-shimmer' ? 'shimmer' : 'float'} ${Math.random() * 25 + 15}s linear ${Math.random() * 5}s infinite;
            `;
            fragment.appendChild(particle);
        }
        container.appendChild(fragment);
    }

    recreateBackgroundEffects() {
        if (this._debounceTimer) clearTimeout(this._debounceTimer);
        this._debounceTimer = setTimeout(() => {
            if (document.querySelector('.bubble-container')) {
                this.createBubbles();
                this.createParticles();
            }
        }, 250);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.loginManager = new LoginManager();
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = LoginManager;
}
