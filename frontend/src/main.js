import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router';
import axios from 'axios'; // 1. Importáld az axio-t

// 2. Alapértelmezett axios konfiguráció
axios.defaults.baseURL = 'https://api.uccproject.localhost';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// 3. Interceptor a tokenhez
// Ez minden egyes kérés elé beszúrja a tokent, ha az létezik
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// 4. Tedd az axio-t globálisan elérhetővé (opcionális, de kényelmes)
window.axios = axios;

const app = createApp(App);
app.use(router);
app.mount('#app');