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
});
