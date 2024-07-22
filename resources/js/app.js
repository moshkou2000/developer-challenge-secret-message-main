/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';
import { createApp, ref, onMounted } from 'vue';
import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */

import ChatMessages from './components/ChatMessages.vue';
import ChatForm from './components/ChatForm.vue';

const app = createApp({
    setup() {
        const messages = ref([]);

        const fetchMessages = async () => {
            try {
                const response = await axios.get('/messages');
                messages.value = response.data;
            } catch (error) {
                console.error("Error fetching messages:", error);
            }
        };

        const addMessage = async (message) => {
            messages.value.push(message);

            try {
                await axios.post('/messages', message);
            } catch (error) {
                console.error("Error adding message:", error);
            }
        };

        onMounted(() => {
            fetchMessages();

            window.Echo.private('chat')
                .listen('MessageSent', (e) => {
                    messages.value.push({
                        message: e.message.message,
                        user: e.user
                    });
                });
        });

        return {
            messages,
            fetchMessages,
            addMessage
        };
    }
});

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

app.component('chat-messages', ChatMessages);
app.component('chat-form', ChatForm);

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
