import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./app/**/*.{ts,tsx}', './components/**/*.{ts,tsx}'],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        background: '#0b0f17',
        foreground: '#eaf0ff',
        accent: '#7df9ff',
        secondary: '#8b5cf6'
      },
      fontFamily: {
        sans: ['var(--font-sans)', 'system-ui', 'sans-serif']
      },
      borderRadius: {
        xl: '1.25rem'
      },
      boxShadow: {
        glow: '0 0 40px rgba(125, 249, 255, 0.3)'
      }
    }
  },
  plugins: []
};

export default config;
