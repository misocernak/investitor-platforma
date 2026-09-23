/**
 * Tailwind konfiguracija — CSS se pravi unapred (GitHub Actions pri svakom deploy-u:
 * `npm run build:css` → public/css/app.css), umesto Tailwind CDN-a u browseru.
 * Tokeni (boje, fontovi, razmaci) su iz dizajna StructureOps.
 */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './app/**/*.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        'on-surface-variant': '#45474c', 'on-primary-fixed-variant': '#3c475a', 'on-surface': '#0b1c30',
        'on-secondary-container': '#fefcff', 'tertiary-fixed-dim': '#6bd8cb', 'inverse-on-surface': '#eaf1ff',
        'on-tertiary': '#ffffff', 'secondary-fixed': '#dbe1ff', 'surface-container': '#e5eeff', 'surface-tint': '#545f73',
        'error-container': '#ffdad6', 'surface-container-highest': '#d3e4fe', 'tertiary-container': '#002f2a',
        'surface-container-high': '#dce9ff', error: '#ba1a1a', 'outline-variant': '#c5c6cd', 'on-primary': '#ffffff',
        'on-error-container': '#93000a', 'on-secondary-fixed': '#00174b', 'surface-container-low': '#eff4ff',
        'tertiary-fixed': '#89f5e7', 'on-background': '#0b1c30', 'on-primary-container': '#8590a6',
        'on-secondary-fixed-variant': '#003ea8', 'on-primary-fixed': '#111c2d', 'inverse-primary': '#bcc7de',
        'surface-dim': '#cbdbf5', 'on-error': '#ffffff', primary: '#091426', 'on-tertiary-container': '#28a094',
        'primary-container': '#1e293b', 'primary-fixed': '#d8e3fb', surface: '#f8f9ff', background: '#f8f9ff',
        'surface-variant': '#d3e4fe', 'primary-fixed-dim': '#bcc7de', 'inverse-surface': '#213145',
        'secondary-fixed-dim': '#b4c5ff', 'on-secondary': '#ffffff', 'on-tertiary-fixed-variant': '#005049',
        'surface-container-lowest': '#ffffff', outline: '#75777d', 'secondary-container': '#316bf3',
        tertiary: '#001815', 'on-tertiary-fixed': '#00201d', 'surface-bright': '#f8f9ff', secondary: '#0051d5',
      },
      borderRadius: {
        DEFAULT: '0.25rem',
        lg: '0.375rem',
        xl: '0.625rem',
        // "full" mora ostati pravi krug/pilula (u originalnoj konfiguraciji je greškom bio 0.75rem)
      },
      spacing: {
        'space-lg': '1rem', 'space-md': '0.75rem', 'space-xl': '1.5rem', margin: '1rem', gutter: '1rem',
        'space-xs': '0.25rem', 'margin-md': '1.5rem', 'space-sm': '0.5rem', 'gutter-lg': '1.5rem', 'margin-lg': '2rem',
      },
      fontFamily: {
        'body-lg': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'body-md': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'body-sm': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-xl': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-xl-mobile': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-lg': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-lg-mobile': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-md': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'headline-sm': ['"Hanken Grotesk"', 'system-ui', 'sans-serif'],
        'label-md': ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
        'label-sm': ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
        'label-xs': ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
      },
      fontSize: {
        'body-lg': ['0.9375rem', { lineHeight: '1.5rem', letterSpacing: '0em', fontWeight: '400' }],
        'body-md': ['0.875rem', { lineHeight: '1.3125rem', letterSpacing: '0em', fontWeight: '400' }],
        'body-sm': ['0.8125rem', { lineHeight: '1.1875rem', letterSpacing: '0.005em', fontWeight: '400' }],
        'headline-xl': ['2rem', { lineHeight: '2.5rem', letterSpacing: '-0.03em', fontWeight: '700' }],
        'headline-xl-mobile': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.02em', fontWeight: '700' }],
        'headline-lg': ['1.5rem', { lineHeight: '2rem', letterSpacing: '-0.02em', fontWeight: '600' }],
        'headline-lg-mobile': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.01em', fontWeight: '600' }],
        'headline-md': ['1.125rem', { lineHeight: '1.5rem', letterSpacing: '-0.01em', fontWeight: '600' }],
        'headline-sm': ['0.9375rem', { lineHeight: '1.375rem', letterSpacing: '0em', fontWeight: '600' }],
        'label-md': ['0.8125rem', { lineHeight: '1.125rem', letterSpacing: '-0.01em', fontWeight: '500' }],
        'label-sm': ['0.6875rem', { lineHeight: '0.875rem', letterSpacing: '0.02em', fontWeight: '500' }],
        'label-xs': ['0.6875rem', { lineHeight: '0.875rem', letterSpacing: '0.03em', fontWeight: '600' }],
      },
    },
  },
  plugins: [],
};
