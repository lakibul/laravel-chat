import './bootstrap';
import { createApp } from 'vue';
import Chat from './components/Chat.vue';

const app = createApp({
    components: {
        Chat
    }
});

app.mount('#app');
