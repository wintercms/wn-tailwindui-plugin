/*
 * Dark Mode / Theme Appearance switcher
 */
window.updateColorScheme = function (scheme) {
    const docEl = document.documentElement;
    if (scheme !== undefined) {
        docEl.setAttribute('data-color-scheme', scheme);
    }

    const colorScheme = docEl.getAttribute('data-color-scheme') || 'light';

    if (
        colorScheme === 'dark' || (
            colorScheme === 'auto' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
        )
    ) {
        docEl.classList.add('dark');
    } else {
        docEl.classList.remove('dark');
    }
}
updateColorScheme();
