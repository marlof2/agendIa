/**
 * main.ts
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */

// Plugins
import { registerPlugins } from '@/plugins'

// Components
import App from './App.vue'

// Composables
import { createApp } from 'vue'

// Styles
import 'unfonts.css'

// CSS global para SweetAlert2
import './styles/swal.css'

// Correções para tema escuro
import './styles/dark-theme-fixes.scss'

const app = createApp(App)

registerPlugins(app)

app.mount('#app')
