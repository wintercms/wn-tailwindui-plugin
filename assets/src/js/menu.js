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
