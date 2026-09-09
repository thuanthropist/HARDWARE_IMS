import './bootstrap';

import * as bootstrap from 'bootstrap';
import AOS from 'aos';
import Alpine from 'alpinejs';

window.bootstrap = bootstrap;

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

AOS.init({
    duration: prefersReducedMotion ? 1 : 350,
    easing: 'ease-out',
    once: true,
    offset: 40,
    disable: prefersReducedMotion,
});

// Navbar solidifies on scroll — but pages without a dark hero (most of the
// site) need it solid immediately, since transparent white-on-white text is
// unreadable without a dark backdrop behind it.
const navbar = document.querySelector('.sf-navbar');
const hasHero = document.querySelector('.sf-hero') !== null;
if (navbar) {
    const solidify = () => {
        navbar.classList.toggle('is-solid', !hasHero || window.scrollY > 40);
    };
    solidify();
    window.addEventListener('scroll', solidify, { passive: true });
}

// On hero pages the fixed WhatsApp FAB sits right on top of the hero's stat
// cards on mobile until the visitor scrolls past it — hide it until then.
// Pages without a hero show it immediately.
const whatsappFab = document.querySelector('.sf-whatsapp-fab');
if (whatsappFab && hasHero) {
    const revealFab = () => {
        whatsappFab.classList.toggle('is-pending', window.scrollY < 350);
    };
    revealFab();
    window.addEventListener('scroll', revealFab, { passive: true });
}

window.Alpine = Alpine;
Alpine.start();
