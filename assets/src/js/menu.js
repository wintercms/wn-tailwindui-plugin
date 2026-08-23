document.addEventListener('DOMContentLoaded', function () {
    
    const fnmap = {
        toggle: 'toggle',
        show: 'add',
        hide: 'remove',
    }

    const collapseTriggers = Array.from(
        document.querySelectorAll('[data-toggle="collapse"]'),
    );

    const collapse = (elm, selector, cmd) => {
        elm.classList[fnmap[cmd]]('open');
        const targets = Array.from(document.querySelectorAll(selector));
        targets.forEach((target) => {
            target.classList[fnmap[cmd]]('show');
        });
    };

    document.addEventListener(
        'click',
        (ev) => {
            const elm = ev.target;

            if (collapseTriggers.includes(elm)) {
                const selector = elm.getAttribute('data-target');
                collapse(elm, selector, 'toggle');
                elm.setAttribute('aria-expanded', elm.classList.contains('open') ? 'true' : 'false');
            }
        },
        false,
    );

    // Position dropdown panels under their trigger. The panels are position:fixed
    // so the top menu bar can scroll horizontally on overflow without clipping
    // them; place them here from the trigger's live getBoundingClientRect (robust
    // against page layout such as the fancy form header — no offset maths).
    const placeDropdown = (dropdown) => {
        const panel = dropdown.querySelector(':scope > .tw-dropdown-menu');
        if (!panel) {
            return;
        }
        const rect = dropdown.getBoundingClientRect();
        // Panel may be display:none when this runs (placed just before CSS reveals
        // it), so fall back to its min-width for the off-screen clamp.
        const panelWidth = panel.offsetWidth || 224;
        panel.style.top = rect.bottom + 'px';
        if (panel.classList.contains('tw-dropdown-menu-right')) {
            panel.style.left = 'auto';
            panel.style.right = Math.max(0, window.innerWidth - rect.right) + 'px';
        } else {
            panel.style.right = 'auto';
            // Clamp so a trigger near the right edge doesn't push the panel
            // off-screen (its content would otherwise be unreachable).
            panel.style.left = Math.min(rect.left, Math.max(0, window.innerWidth - panelWidth)) + 'px';
        }
    };

    document.querySelectorAll('.tw-dropdown').forEach((dropdown) => {
        // Place on the pointer/focus entering, i.e. just before CSS reveals it.
        dropdown.addEventListener('mouseenter', () => placeDropdown(dropdown));
        dropdown.addEventListener('focusin', () => placeDropdown(dropdown));

        // Touch/click support: iOS doesn't focus a <button> on tap, so
        // :focus-within never fires and a button-triggered dropdown (the user
        // menu) can't be opened by touch. Toggle an explicit class on click.
        const button = dropdown.querySelector(':scope > button');
        if (button) {
            button.addEventListener('click', function (ev) {
                ev.preventDefault();
                if (dropdown.classList.toggle('is-open')) {
                    placeDropdown(dropdown);
                }
            });
        }
    });

    // Keep fixed panels aligned with their trigger as the bar scrolls / window resizes.
    const repositionDropdowns = () => document.querySelectorAll('.tw-dropdown').forEach(placeDropdown);
    window.addEventListener('resize', repositionDropdowns, { passive: true });
    const menuScroller = document.querySelector('.layout-topmenu .overflow-x-auto');
    if (menuScroller) {
        menuScroller.addEventListener('scroll', repositionDropdowns, { passive: true });
    }

    // Close click-opened dropdowns on an outside click or Escape.
    document.addEventListener('click', function (ev) {
        document.querySelectorAll('.tw-dropdown.is-open').forEach((dropdown) => {
            if (!dropdown.contains(ev.target)) {
                dropdown.classList.remove('is-open');
            }
        });
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') {
            document.querySelectorAll('.tw-dropdown.is-open').forEach((dropdown) => dropdown.classList.remove('is-open'));
        }
    });

    // Mobile menu toggle (replaces the Headless UI Disclosure). The button shows
    // the panel on small screens; a `hidden` class controls visibility and
    // `aria-expanded` / an `is-open` class drive the hamburger/close icon swap.
    const mobileToggle = document.querySelector('[data-control="mobile-menu-toggle"]');
    const mobilePanel = document.querySelector('[data-control="mobile-menu-panel"]');

    if (mobileToggle && mobilePanel) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = mobilePanel.classList.toggle('hidden') === false;
            mobileToggle.classList.toggle('is-open', isOpen);
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }
});
