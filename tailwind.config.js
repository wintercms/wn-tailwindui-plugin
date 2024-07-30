
const colors = require('tailwindcss/colors');

module.exports = {
    content: [
        './skins/tailwindui/**/*.php',
        './assets/js/src/**/*.{js,vue}',
    ],
    theme: {
        colors: {
            transparent: colors.transparent,
            white: colors.white,
            black: colors.black,
            current: colors.current,
            neutral: colors.slate,
            primary: {
                50: '#e8edef',
                100: '#d1dbe0',
                200: '#a3b6c0',
                300: '#7492a1',
                400: '#466d81',
                500: '#184962',
                600: '#133a4e',
                700: '#0e2c3b',
                800: '#0a1d27',
                900: '#050f14',
            },
            accent: {
                50: '#eaf6f9',
                100: '#d5edf4',
                200: '#abdce9',
                300: '#81cadd',
                400: '#57b9d2',
                500: '#2da7c7',
                600: '#24869f',
                700: '#1b6477',
                800: '#124350',
                900: '#092128',
            },
            positive: {
                50: '#f0f9ee',
                100: '#e2f3dc',
                200: '#c4e8b9',
                300: '#a7dc97',
                400: '#89d174',
                500: '#6cc551',
                600: '#569e41',
                700: '#417631',
                800: '#2b4f20',
                900: '#162710',
            },
            negative: {
                50: '#fbefea',
                100: '#f7dfd4',
                200: '#efbea9',
                300: '#e69e7f',
                400: '#de7d54',
                500: '#d65d29',
                600: '#ab4a21',
                700: '#803819',
                800: '#562510',
                900: '#2b1308',
            },
        },
        extend: {
            boxShadow: {
                'bottom': '0px 0px 3px rgba(0, 0, 0, 0.25)',
            },
            fontFamily: {
                base: ['Public Sans', 'sans-serif'],
                heading: ['Mulish', 'sans-serif'],
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
            letterSpacing: {
                tighter: '-0.75px',
                tight: '-0.5px',
                normal: '-0.225px',
                wide: '0',
                wider: '0.225px',
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
