/**
 * plugins/vuetify.ts
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Composables
import { createVuetify } from 'vuetify'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#1e293b', // Azul escuro profissional
          secondary: '#475569', // Cinza azulado neutro
          accent: '#334155', // Cinza escuro para destaques
          error: '#dc2626', // Vermelho mais sóbrio
          info: '#1e40af', // Azul corporativo
          success: '#059669', // Verde mais escuro e profissional
          warning: '#d97706', // Laranja mais sério
          background: '#f8fafc',
          surface: '#ffffff',
          'surface-variant': '#f1f5f9',
          'on-surface': '#1e293b',
          'on-surface-variant': '#64748b',
        },
      },
      dark: {
        colors: {
          primary: '#334155', // Cinza escuro profissional
          secondary: '#475569', // Cinza azulado neutro
          accent: '#64748b', // Cinza médio para destaques
          error: '#ef4444', // Vermelho mais sóbrio
          info: '#2563eb', // Azul corporativo
          success: '#10b981', // Verde profissional
          warning: '#f59e0b', // Laranja sério
          background: '#0f172a',
          surface: '#1e293b',
          'surface-variant': '#334155',
          'on-surface': '#f1f5f9',
          'on-surface-variant': '#94a3b8',
        },
      },
    },
  },
})
