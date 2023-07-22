/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode:'class',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      screens: {
        'print': { 'raw': 'print' },
      },
      backgroundImage:{
          'bg-dashboard':"url('/images/background.jpeg')",
          'bg-login':"url('/images/login-background.jpeg')",
          'bg-logo':"url('/images/logo.jpg')"
      },
      colors:{
        'basic': {
          '50':  '#f7f7f2',
          '100': '#edefd7',
          '200': '#d5e3aa',
          '300': '#a7c475',
          '400': '#65a146',
          '500': '#498427',
          '600': '#3b6c1b',
          '700': '#315217',
          '800': '#223814',
          '900': '#17230f',
          'red': '#ba0000',
        },
      }
    },
  },
  variants:{
      extend:{
          lineClamp:['hover'],
          textColor:['group-hover']
      }
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/line-clamp'),
    require('tailwind-scrollbar'),
  ],
}
