/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Set base URL for API requests - automatically detect current domain and path
const currentUrl = window.location.origin + window.location.pathname.replace(/\/+$/, '');
const baseUrl = currentUrl.endsWith('.php') ? currentUrl.substring(0, currentUrl.lastIndexOf('/')) : currentUrl;
window.axios.defaults.baseURL = baseUrl;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Set up authentication header for API requests
const token = localStorage.getItem('auth_token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Echo will be initialized after login in the Vue component
window.Echo = null;
