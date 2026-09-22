/*
 * Site behaviour. Deliberately small and dependency-free.
 *
 * Livewire 3 ships Alpine - do NOT `npm install alpinejs` or import it here,
 * that double-registers Alpine and breaks every x-data binding on the site.
 *
 * Every effect here follows the same three rules:
 *   1. Content never depends on it - if a function throws, the page still reads.
 *   2. prefers-reduced-motion turns it off.
 *   3. It tears itself down before re-running, because wire:navigate swaps pages
 *      without a reload and anything left running would pile up.
 */

const root = document.documentElement;
const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* --------------------------------------------------------------------------
 | Scroll reveal, hero entrance, staggered grids
 * ------------------------------------------------------------------------ */

let revealObserver = null;
let revealFallback = null;

function initReveal() {
    if (revealObserver) {
        revealObserver.disconnect();
        revealObserver = null;
    }

    if (revealFallback) {
        window.clearTimeout(revealFallback);
        revealFallback = null;
    }

    if (reduceMotion() || !('IntersectionObserver' in window)) {
        return;
    }

    const main = document.querySelector('main');

    if (!main) {
        return;
    }

    const sections = Array.from(main.querySelectorAll(':scope > section'));

    if (sections.length === 0) {
        return;
    }

    // The first section animates on load, not on scroll, so the page is never
    // blank at rest.
    const lead = sections.shift();
    const leadInner = lead.querySelector('.wb-container > div');

    if (leadInner) {
        leadInner.setAttribute('data-enter', '');
    }

    const revealed = [...sections, document.querySelector('footer')].filter(Boolean);

    revealed.forEach((element) => {
        element.classList.remove('is-visible');
        element.setAttribute('data-reveal', '');

        // Opt-out: a container that reveals its own children row by row.
        const grid = element.querySelector('.grid:not([data-no-stagger])');

        if (grid && grid.children.length > 1 && grid.children.length <= 12) {
            grid.setAttribute('data-stagger', '');
            Array.from(grid.children).forEach((child, index) => {
                child.style.setProperty('--wb-i', String(index));
            });
        }
    });

    root.classList.add('wb-anim');

    const show = (element) => element.classList.add('is-visible');

    // The observer reports every target once straight after observe(). That
    // first report proves it works - and is the only thing the fallback waits
    // for. (An earlier version revealed everything after 4s regardless, so by
    // the time anyone scrolled down, nothing was left to reveal.)
    let reported = false;

    revealObserver = new IntersectionObserver(
        (entries) => {
            reported = true;
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    show(entry.target);
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.04 }
    );

    revealed.forEach((element) => revealObserver.observe(element));

    // Tall sections whose rows should each arrive as they are reached, rather
    // than all at once when the section's top edge appears.
    const items = Array.from(main.querySelectorAll('[data-reveal-item]'));
    items.forEach((item) => {
        item.classList.remove('is-visible');
        revealObserver.observe(item);
    });
    revealed.push(...items);

    revealFallback = window.setTimeout(() => {
        if (!reported) {
            revealed.forEach(show);
        }
    }, 2500);
}

/* --------------------------------------------------------------------------
 | Hero network
 |
 | Nodes drift, link when close, and reach out to the pointer. Roughly one
 | node per 14,000px of hero, capped at 90, so a phone draws far fewer than a
 | desktop. Stops drawing when the hero is off screen or the tab is hidden.
 * ------------------------------------------------------------------------ */

let network = null;

function initNetwork() {
    if (network) {
        network.destroy();
        network = null;
    }

    const canvas = document.querySelector('[data-wb-network]');

    if (!canvas || !canvas.getContext) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const host = canvas.parentElement;
    const still = reduceMotion();

    let width = 0;
    let height = 0;
    let nodes = [];
    let frameId = null;
    let onScreen = true;
    const pointer = { x: -9999, y: -9999, active: false };

    const NAVY_LINE = '157, 170, 197';
    const LIME = '137, 199, 38';

    function build() {
        const rect = canvas.getBoundingClientRect();
        const dpr = Math.min(window.devicePixelRatio || 1, 2);

        width = rect.width;
        height = rect.height;
        canvas.width = Math.round(width * dpr);
        canvas.height = Math.round(height * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const count = Math.max(24, Math.min(90, Math.round((width * height) / 14000)));

        nodes = Array.from({ length: count }, () => ({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.28,
            vy: (Math.random() - 0.5) * 0.28,
            r: Math.random() * 1.6 + 0.9,
            lime: Math.random() < 0.14,
        }));
    }

    function draw() {
        ctx.clearRect(0, 0, width, height);

        const reach = Math.min(150, Math.max(90, width / 8));
        const pointerReach = 190;

        for (const n of nodes) {
            if (!still) {
                n.x += n.vx;
                n.y += n.vy;

                if (n.x < 0 || n.x > width) n.vx *= -1;
                if (n.y < 0 || n.y > height) n.vy *= -1;

                // Nodes close to the pointer ease out of its way, strongest
                // at the centre. A pull would collapse them into a clump if the
                // pointer rested; a soft push keeps the network spread out
                // while the links below still reach to the cursor.
                if (pointer.active) {
                    const dx = n.x - pointer.x;
                    const dy = n.y - pointer.y;
                    const d = Math.hypot(dx, dy);
                    const push = 110;

                    if (d < push && d > 0.5) {
                        const force = (1 - d / push) * 0.9;
                        n.x += (dx / d) * force;
                        n.y += (dy / d) * force;
                    }
                }

                // keep strays inside the frame
                n.x = Math.max(0, Math.min(width, n.x));
                n.y = Math.max(0, Math.min(height, n.y));
            }
        }

        // Links between nearby nodes
        for (let i = 0; i < nodes.length; i++) {
            const a = nodes[i];

            for (let j = i + 1; j < nodes.length; j++) {
                const b = nodes[j];
                const d = Math.hypot(a.x - b.x, a.y - b.y);

                if (d < reach) {
                    const alpha = (1 - d / reach) * (a.lime || b.lime ? 0.45 : 0.28);
                    ctx.strokeStyle = `rgba(${a.lime || b.lime ? LIME : NAVY_LINE}, ${alpha})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }

        // Links to the pointer: the "connecting" moment
        if (pointer.active) {
            for (const n of nodes) {
                const d = Math.hypot(pointer.x - n.x, pointer.y - n.y);

                if (d < pointerReach) {
                    ctx.strokeStyle = `rgba(${LIME}, ${(1 - d / pointerReach) * 0.6})`;
                    ctx.lineWidth = 1.2;
                    ctx.beginPath();
                    ctx.moveTo(pointer.x, pointer.y);
                    ctx.lineTo(n.x, n.y);
                    ctx.stroke();
                }
            }

            ctx.fillStyle = `rgba(${LIME}, 0.9)`;
            ctx.beginPath();
            ctx.arc(pointer.x, pointer.y, 3, 0, Math.PI * 2);
            ctx.fill();
        }

        // Nodes
        for (const n of nodes) {
            ctx.fillStyle = n.lime ? `rgba(${LIME}, 0.95)` : 'rgba(199, 208, 224, 0.75)';
            ctx.beginPath();
            ctx.arc(n.x, n.y, n.lime ? n.r + 0.8 : n.r, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function loop() {
        draw();
        frameId = !still && onScreen && !document.hidden ? requestAnimationFrame(loop) : null;
    }

    function resume() {
        if (!frameId && !still && onScreen && !document.hidden) {
            frameId = requestAnimationFrame(loop);
        }
    }

    function onMove(event) {
        const rect = canvas.getBoundingClientRect();
        pointer.x = event.clientX - rect.left;
        pointer.y = event.clientY - rect.top;
        pointer.active = true;

        if (still) {
            draw(); // even with motion reduced, respond to the pointer by redrawing
        }
    }

    function onLeave() {
        pointer.active = false;

        if (still) {
            draw();
        }
    }

    let resizeTimer = null;

    function onResize() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => {
            build();
            draw();
        }, 150);
    }

    const visibility = new IntersectionObserver((entries) => {
        onScreen = entries[0].isIntersecting;
        resume();
    });

    build();
    draw();
    resume();

    visibility.observe(canvas);
    host.addEventListener('pointermove', onMove);
    host.addEventListener('pointerleave', onLeave);
    window.addEventListener('resize', onResize);
    document.addEventListener('visibilitychange', resume);

    network = {
        destroy() {
            if (frameId) cancelAnimationFrame(frameId);
            visibility.disconnect();
            host.removeEventListener('pointermove', onMove);
            host.removeEventListener('pointerleave', onLeave);
            window.removeEventListener('resize', onResize);
            document.removeEventListener('visibilitychange', resume);
            window.clearTimeout(resizeTimer);
        },
    };
}

/* --------------------------------------------------------------------------
 | Counters: count up once, when they come into view
 * ------------------------------------------------------------------------ */

let counterObserver = null;

function initCounters() {
    if (counterObserver) {
        counterObserver.disconnect();
        counterObserver = null;
    }

    const counters = document.querySelectorAll('[data-count]');

    if (counters.length === 0 || reduceMotion() || !('IntersectionObserver' in window)) {
        return; // the final figure is already in the markup
    }

    const run = (el) => {
        const target = Number(el.dataset.count) || 0;
        const duration = 1400;
        const start = performance.now();

        const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = String(Math.round(target * eased));

            if (t < 1) {
                requestAnimationFrame(tick);
            }
        };

        el.textContent = '0';
        requestAnimationFrame(tick);
    };

    counterObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    run(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.6 }
    );

    counters.forEach((el) => counterObserver.observe(el));
}

/* --------------------------------------------------------------------------
 | Cursor glow on sector cards (delegated once, survives navigation)
 * ------------------------------------------------------------------------ */

document.addEventListener('pointermove', (event) => {
    const card = event.target.closest && event.target.closest('.wb-industry');

    if (!card) {
        return;
    }

    const rect = card.getBoundingClientRect();
    card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
    card.style.setProperty('--my', `${event.clientY - rect.top}px`);
});

/* --------------------------------------------------------------------------
 | Page transition after an instant (wire:navigate) navigation
 * ------------------------------------------------------------------------ */

function playPageTransition() {
    if (reduceMotion()) {
        return;
    }

    const main = document.querySelector('main');

    if (!main) {
        return;
    }

    main.classList.remove('wb-page-in');
    void main.offsetWidth;
    main.classList.add('wb-page-in');
    main.addEventListener('animationend', () => main.classList.remove('wb-page-in'), { once: true });
}

/* --------------------------------------------------------------------------
 | Boot
 * ------------------------------------------------------------------------ */

function boot() {
    const run = (fn) => {
        try {
            fn();
        } catch (error) {
            // An effect failing must never take the page with it.
            console.warn('[westbridge]', error);
        }
    };

    run(initReveal);
    run(initNetwork);
    run(initCounters);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}

// wire:navigate fires `livewire:navigate` when a navigation starts and
// `livewire:navigated` when the new page is in place (and, in current Livewire,
// once on the first load as well). Keying the transition off the start event
// means it plays only for a real navigation, whichever way that first-load
// behaviour goes. boot() is safe to run twice: every effect tears itself down
// before starting again.
let navigating = false;

document.addEventListener('livewire:navigate', () => {
    navigating = true;
});

document.addEventListener('livewire:navigated', () => {
    if (navigating) {
        navigating = false;
        playPageTransition();
    }

    boot();
});
