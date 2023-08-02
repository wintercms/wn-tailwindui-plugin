const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    content: [
        './skins/tailwindui/**/*.php',
        './assets/js/src/**/*.{js,vue}',
    ],
    theme: {
        extend: {
            colors: {
                primary: 'var(--primary)',
                'primary-dark': 'var(--primary-dark)',
                'primary-darker': 'var(--primary-darker)',
                'primary-darkest': 'var(--primary-darkest)',
                'primary-light': 'var(--primary-light)',
                'primary-lighter': 'var(--primary-lighter)',
                'primary-lightest': 'var(--primary-lightest)',
                secondary: 'var(--secondary)',
                'secondary-dark': 'var(--secondary-dark)',
                'secondary-darker': 'var(--secondary-darker)',
                'secondary-darkest': 'var(--secondary-darkest)',
                'secondary-light': 'var(--secondary-light)',
                'secondary-lighter': 'var(--secondary-lighter)',
                'secondary-lightest': 'var(--secondary-lightest)',
            },
            boxShadow: {
                'bottom': '0px 0px 3px rgba(0, 0, 0, 0.25)',
            },
            fontFamily: {
                sans: ['Inter var'],
            },
            fontSize: {
                'xxs': '.725rem',
            },
            gridTemplateColumns: {
                'layout-inline': '180px 1fr',
                'layout-only': '71px 1fr',
                'layout-hidden': '148px 1fr',
                'layout-tile': '124px 1fr',
            },
            width: {
                '7.5': '1.875rem', // 30px
            },
            height: {
                '7.5': '1.875rem', // 30px
            },
            maxWidth: {
                '1/2': '50%',
                '1/3': '33%',
                '1/4': '25%',
                'vw': '100vw',
            },
            minWidth: {
                '6': '1.5rem',
                '12': '3rem',
                '16': '4rem',
            },
            transitionDuration: {
                '0': '0ms',
                '2000': '2000ms',
            },
            zIndex: {
                'topmenu': '99',
                'sidemenu': '100',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
    darkMode: 'class',
}
