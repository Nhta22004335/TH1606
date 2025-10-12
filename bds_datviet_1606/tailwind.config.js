/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/views/**/*.{php,html,js}",
    ".app/models/**/*.{php,html,js}",
    "./app/controllers/**/*.{php,html,js}",
    "./public/**/*.{php,html,js}"
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}