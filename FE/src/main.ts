import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { initConsoleSanitizer } from './helpers/consoleSanitizer'

// Initialize log optimization
initConsoleSanitizer()

import App from './App.vue'
import router from './router'
import i18n, { loadLocaleMessages, getLocale } from './plugins/i18n'
import { vClickOutside } from './directives'
import VueApexCharts from "vue3-apexcharts";
import { createBootstrap } from 'bootstrap-vue-next'
import * as BootstrapVueNext from 'bootstrap-vue-next'
import VueFeather from 'vue-feather'

import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import './assets/scss/main.scss'
import './assets/admin-panel/scss/app.scss'
import 'sweetalert2/dist/sweetalert2.min.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)
app.use(VueApexCharts)
app.use(createBootstrap())
app.component(VueFeather.name || 'VueFeather', VueFeather)



// Register global directives
app.directive('click-outside', vClickOutside)

// Ensure initial locale is loaded before mounting
loadLocaleMessages(getLocale()).then(() => {
  app.mount('#app')
})

