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
        panel.style.top = rect.bottom + 'px';
        if (panel.classList.contains('tw-dropdown-menu-right')) {
            panel.style.left = 'auto';
            panel.style.right = Math.max(0, window.innerWidth - rect.right) + 'px';
        } else {
            panel.style.right = 'auto';
            panel.style.left = rect.left + 'px';
        }
    };

    document.querySelectorAll('.tw-dropdown').forEach((dropdown) => {
        // Place on the pointer/focus entering, i.e. just before CSS reveals it.
        dropdown.addEventListener('mouseenter', () => placeDropdown(dropdown));
        dropdown.addEventListener('focusin', () => placeDropdown(dropdown));
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
