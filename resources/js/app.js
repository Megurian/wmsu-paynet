import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function initLandingParticles() {
    const canvas = document.getElementById('landing-particles');
    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const particles = [];
    const particleCount = 45;
    const maxSpeed = 0.35;

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * window.devicePixelRatio;
        canvas.height = rect.height * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
    };

    const randomBetween = (min, max) => Math.random() * (max - min) + min;

    const createParticle = () => ({
        x: randomBetween(0, canvas.width / window.devicePixelRatio),
        y: randomBetween(0, canvas.height / window.devicePixelRatio),
        vx: randomBetween(-maxSpeed, maxSpeed),
        vy: randomBetween(-maxSpeed, maxSpeed),
        radius: randomBetween(1.2, 3),
        alpha: randomBetween(0.15, 0.35),
    });

    const init = () => {
        resize();
        particles.length = 0;
        for (let i = 0; i < particleCount; i += 1) {
            particles.push(createParticle());
        }
    };

    const draw = () => {
        const width = canvas.width / window.devicePixelRatio;
        const height = canvas.height / window.devicePixelRatio;

        ctx.clearRect(0, 0, width, height);

        particles.forEach((particle) => {
            particle.x += particle.vx;
            particle.y += particle.vy;

            // subtle random drift for unpredictable motion
            particle.vx += randomBetween(-0.015, 0.015);
            particle.vy += randomBetween(-0.015, 0.015);

            particle.vx = Math.max(Math.min(particle.vx, maxSpeed), -maxSpeed);
            particle.vy = Math.max(Math.min(particle.vy, maxSpeed), -maxSpeed);

            if (particle.x < -20) particle.x = width + 20;
            if (particle.x > width + 20) particle.x = -20;
            if (particle.y < -20) particle.y = height + 20;
            if (particle.y > height + 20) particle.y = -20;

            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(220, 38, 38, ${particle.alpha})`;
            ctx.fill();
        });

        requestAnimationFrame(draw);
    };

    init();
    draw();

    window.addEventListener('resize', () => {
        resize();
    });
}

function initAuthParticles() {
    const canvas = document.getElementById('auth-particles');
    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const particles = [];
    const particleCount = 35;
    const maxSpeed = 0.28;

    const resize = () => {
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * window.devicePixelRatio;
        canvas.height = rect.height * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);
    };

    const randomBetween = (min, max) => Math.random() * (max - min) + min;

    const createParticle = () => ({
        x: randomBetween(0, canvas.width / window.devicePixelRatio),
        y: randomBetween(0, canvas.height / window.devicePixelRatio),
        vx: randomBetween(-maxSpeed, maxSpeed),
        vy: randomBetween(-maxSpeed, maxSpeed),
        radius: randomBetween(1.2, 2.7),
        alpha: randomBetween(0.12, 0.28),
    });

    const init = () => {
        resize();
        particles.length = 0;
        for (let i = 0; i < particleCount; i += 1) {
            particles.push(createParticle());
        }
    };

    const draw = () => {
        const width = canvas.width / window.devicePixelRatio;
        const height = canvas.height / window.devicePixelRatio;

        ctx.clearRect(0, 0, width, height);

        particles.forEach((particle) => {
            particle.x += particle.vx;
            particle.y += particle.vy;

            particle.vx += randomBetween(-0.012, 0.012);
            particle.vy += randomBetween(-0.012, 0.012);

            particle.vx = Math.max(Math.min(particle.vx, maxSpeed), -maxSpeed);
            particle.vy = Math.max(Math.min(particle.vy, maxSpeed), -maxSpeed);

            if (particle.x < -20) particle.x = width + 20;
            if (particle.x > width + 20) particle.x = -20;
            if (particle.y < -20) particle.y = height + 20;
            if (particle.y > height + 20) particle.y = -20;

            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(238, 72, 72, ${particle.alpha})`;
            ctx.fill();
        });

        requestAnimationFrame(draw);
    };

    init();
    draw();

    window.addEventListener('resize', resize);
}

function initPageTransition() {
    const shell = document.querySelector('.page-shell');

    document.querySelectorAll('a.js-page-transition').forEach((link) => {
        link.addEventListener('click', (event) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#')) {
                return;
            }

            event.preventDefault();

            if (shell) {
                shell.classList.add('page-transition-exit');
            }

            window.setTimeout(() => {
                window.location.href = href;
            }, 420);
        });
    });
}

function initPasswordToggle() {
    const eyeIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' +
        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />' +
        '</svg>';
    const eyeOffIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.88 21.88 0 0 1 5-5.94" />' +
        '<path d="M1 1l22 22" />' +
        '</svg>';

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        const inputId = toggle.getAttribute('data-password-toggle');
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        const updateIcon = (isPassword) => {
            toggle.innerHTML = isPassword ? eyeIcon : eyeOffIcon;
            toggle.setAttribute('aria-label', isPassword ? 'Show password' : 'Hide password');
        };

        updateIcon(input.type === 'password');

        toggle.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            updateIcon(!isPassword);
            input.focus();
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initLandingParticles();
    initAuthParticles();
    initPasswordToggle();
    initPageTransition();
});

