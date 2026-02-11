/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./frontend/**/*.php",
    "./frontend/**/*.html",
    "./frontend/**/*.js",
    "./index.php",
    "./api/**/*.php"
  ],
  // Ativar purge para reduzir tamanho do CSS final (já é padrão no Tailwind v3)
  purge: {
    enabled: process.env.NODE_ENV === 'production',
    content: [
      "./frontend/**/*.php",
      "./frontend/**/*.html",
      "./frontend/**/*.js",
      "./index.php",
      "./api/**/*.php"
    ],
    // Preservar classes que podem ser adicionadas dinamicamente
    safelist: [
      'animate-fade-in',
      'fade-in',
      'fade-out',
      'event-card-image',
      'hero-gradient-text',
      'hero-content',
      'hero-energy-container',
      'energy-wave',
      'floating-particle',
      'slide-in-left',
      'slide-in-right'
    ]
  },
  theme: {
    extend: {
      screens: {
        'md-lg': {'min': '999px', 'max': '1600px'},
      },
      colors: {
        'brand-green': '#0b4340',
        'brand-yellow': '#f5c113',
        'brand-red': '#ad1f22',
        primary: '#0b4340',
        accent: '#f5c113',
        danger: '#ad1f22',
        graybg: '#f8fafc',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      }
    },
  },
  plugins: [],
  // Otimizações de performance
  corePlugins: {
    // Desabilitar plugins não utilizados pode reduzir o tamanho do CSS
    // Deixar todos habilitados por padrão para flexibilidade
  },
} 