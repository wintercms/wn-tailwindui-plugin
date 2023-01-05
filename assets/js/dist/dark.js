window.tailwindUIBackend = {
    isDarkModeEnabled: function() {
        return (
            typeof localStorage.tailwindUIBackendDarkMode !== 'undefined'
            ? JSON.parse(localStorage.tailwindUIBackendDarkMode)
            : window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
        );
    },
    toggleDarkMode: function() {
        localStorage.tailwindUIBackendDarkMode = !window.tailwindUIBackend.isDarkModeEnabled();
        window.tailwindUIBackend.updateDarkMode();
    },
    updateDarkMode: function () {
        if (window.tailwindUIBackend.isDarkModeEnabled()) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },
};

tailwindUIBackend.updateDarkMode();
