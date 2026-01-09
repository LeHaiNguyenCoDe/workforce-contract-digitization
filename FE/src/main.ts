import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { initConsoleSanitizer } from './helpers/consoleSanitizer'

// Initialize log optimization
initConsoleSanitizer()

import App from './App.vue'
import router from './router'
import i18n, { loadLocaleMessages, getLocale } from './plugins/i18n'
import { vClickOutside } from './directives'

import './assets/scss/main.scss'
import 'sweetalert2/dist/sweetalert2.min.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

// Register global directives
app.directive('click-outside', vClickOutside)

// Ensure initial locale is loaded before mounting
loadLocaleMessages(getLocale()).then(() => {
  app.mount('#app')
})

