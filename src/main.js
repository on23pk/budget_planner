import { createApp } from 'vue';
import App from './App.vue';
import 'bootstrap/dist/css/bootstrap.min.css';
import router from './router'; // Importiere den Router

const app = createApp(App);

app.use(router); // Router verwenden
app.mount('#app');
