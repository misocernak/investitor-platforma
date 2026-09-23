/**
 * Tailwind konfiguracija — CSS se pravi unapred (GitHub Actions pri svakom deploy-u:
 * `npm run build:css` → public/css/app.css), umesto Tailwind CDN-a u browseru.
 * Tokeni (boje, font Inter, razmaci) su iz dizajna "Temelj Workspace".
 */
const inter = ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'sans-serif'];

module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './resources/css/**/*.css',
    './app/**/*.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: '#000000', 'on-primary': '#ffffff',
        'primary-container': '#131b2e', 'on-primary-container': '#7c839b',
        'primary-fixed': '#dae2fd', 'primary-fixed-dim': '#bec6e0',
        'on-primary-fixed': '#131b2e', 'on-primary-fixed-variant': '#3f465c',
        secondary: '#4059aa', 'on-secondary': '#ffffff',
        'secondary-container': '#8fa7fe', 'on-secondary-container': '#1d3989',
        'secondary-fixed': '#dce1ff', 'secondary-fixed-dim': '#b6c4ff',
        'on-secondary-fixed': '#00164e', 'on-secondary-fixed-variant': '#264191',
        tertiary: '#000000', 'on-tertiary': '#ffffff',
        'tertiary-container': '#271901', 'on-tertiary-container': '#98805d',
        'tertiary-fixed': '#fcdeb5', 'tertiary-fixed-dim': '#dec29a',
        'on-tertiary-fixed': '#271901', 'on-tertiary-fixed-variant': '#574425',
        error: '#ba1a1a', 'on-error': '#ffffff',
        'error-container': '#ffdad6', 'on-error-container': '#93000a',
        background: '#f8f9ff', 'on-background': '#0b1c30',
        surface: '#f8f9ff', 'surface-bright': '#f8f9ff', 'surface-dim': '#cbdbf5',
        'surface-container-lowest': '#ffffff', 'surface-container-low': '#eff4ff',
        'surface-container': '#e5eeff', 'surface-container-high': '#dce9ff',
        'surface-container-highest': '#d3e4fe', 'surface-variant': '#d3e4fe', 'surface-tint': '#565e74',
        'on-surface': '#0b1c30', 'on-surface-variant': '#45464d',
        'inverse-surface': '#213145', 'inverse-on-surface': '#eaf1ff', 'inverse-primary': '#bec6e0',
        outline: '#76777d', 'outline-variant': '#c6c6cd',
      },
      borderRadius: {
        // Blago zaobljeno, kao u dizajnu. "full" ostaje pravi krug (avatari, tačke statusa).
        DEFAULT: '0.25rem',
        lg: '0.375rem',
        xl: '0.5rem',
      },
      spacing: {
        'space-xs': '0.25rem', 'space-sm': '0.5rem', 'space-md': '0.75rem',
        'space-lg': '1.25rem', 'space-xl': '2rem',
        gutter: '1rem', 'gutter-dense': '0.5rem',
        margin: '1.5rem', 'margin-mobile': '1rem',
      },
      fontFamily: {
        sans: inter,
        'body-lg': inter, 'body-md': inter, 'body-sm': inter,
        'headline-lg': inter, 'headline-md': inter, 'headline-sm': inter,
        'label-md': inter, 'label-sm': inter, 'mono-num': inter,
      },
      fontSize: {
        'headline-lg': ['1.625rem', { lineHeight: '2rem', letterSpacing: '-0.02em', fontWeight: '600' }],
        'headline-md': ['1.25rem', { lineHeight: '1.75rem', letterSpacing: '-0.015em', fontWeight: '600' }],
        'headline-sm': ['1rem', { lineHeight: '1.5rem', letterSpacing: '-0.01em', fontWeight: '600' }],
        'body-lg': ['0.9375rem', { lineHeight: '1.375rem', letterSpacing: '-0.005em', fontWeight: '400' }],
        'body-md': ['0.8125rem', { lineHeight: '1.25rem', letterSpacing: '0em', fontWeight: '400' }],
        'body-sm': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0em', fontWeight: '400' }],
        'label-md': ['0.8125rem', { lineHeight: '1.125rem', letterSpacing: '-0.005em', fontWeight: '500' }],
        'label-sm': ['0.6875rem', { lineHeight: '0.875rem', letterSpacing: '0.04em', fontWeight: '600' }],
        'mono-num': ['0.8125rem', { lineHeight: '1.125rem', letterSpacing: '-0.01em', fontWeight: '500' }],
      },
    },
  },
  plugins: [],
};
