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
                'primary-dark': 'color-mod(var(--primary) blackness(20%))',
                'primary-darker': 'color-mod(var(--primary) blackness(30%))',
                'primary-darkest': 'color-mod(var(--primary) blackness(40%))',
                'primary-light': 'color-mod(var(--primary) whiteness(25%))',
                'primary-lighter': 'color-mod(var(--primary) whiteness(30%))',
                'primary-lightest': 'color-mod(var(--primary) whiteness(35%))',
                secondary: 'var(--secondary)',
                'secondary-dark': 'color-mod(var(--secondary) blackness(20%))',
                'secondary-darker': 'color-mod(var(--secondary) blackness(30%))',
                'secondary-darkest': 'color-mod(var(--secondary) blackness(40%))',
                'secondary-light': 'color-mod(var(--secondary) whiteness(25%))',
                'secondary-lighter': 'color-mod(var(--secondary) whiteness(30%))',
                'secondary-lightest': 'color-mod(var(--secondary) whiteness(35%))',
            },
            fontFamily: {
                sans: ['Inter var'],
            },
            fontSize: {
                'xxs': '.725rem',
            },
            gridTemplateColumns: {
                'layout-inline': '200px 1fr',
                'layout-only': '80px 1fr',
                'layout-hidden': '175px 1fr',
                'layout-tile': '120px 1fr',
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
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
    darkMode: 'class',
}
