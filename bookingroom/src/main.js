import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import 'bootstrap/dist/css/bootstrap.min.css' /* Ini yang ditambahkan */
import './style.css'

const app = createApp(App)

app.use(router)

app.mount('#app')
