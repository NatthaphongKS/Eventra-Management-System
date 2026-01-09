// tailwind.config.js (ESM)
export default {
  content: ["./resources/**/*.blade.php", "./resources/**/*.{js,ts,vue}"],
  theme: { extend: {} },
  plugins: [
    require('@tailwindcss/forms'),
  ],
};
