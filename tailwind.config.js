/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        cnps: {
          blue:     '#27357E',
          blueDk:   '#1D2865',
          orange:   '#DE7C0F',
          orangeDk: '#C26C0B',
          green:    '#088E39',
          yellow:   '#F6D01D',
          bg:       '#F5F6FA',
          text:     '#1B2140',
          muted:    '#6B7190',
          border:   '#E2E5F0',
          error:    '#C8102E',
        },
      },
      fontFamily: {
        sans:    ['Inter', 'system-ui', 'sans-serif'],
        heading: ['Poppins', 'Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}