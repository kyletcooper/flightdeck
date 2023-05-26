/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './admin/src/**/*.js',
    './admin/src/*.js',
  ],

  important: '#flightdeck',

  theme: {
    container: {
      center: true,
      padding: '2rem',
    },

    extend: {
      spacing: {
        'inherit': 'inherit',
      }
    },
  },

  plugins: [
    require('@tailwindcss/typography'),
  ],
}
