import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import i18n from './plugins/i18n'
import { vClickOutside } from './shared/directives'

import './shared/styles/main.css'
import 'sweetalert2/dist/sweetalert2.min.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

// Register global directives
app.directive('click-outside', vClickOutside)

app.mount('#app')

