/**
 * Firebase Web SDK (modular) — initialize App + Analytics.
 * Auth/other products can import from './firebase-init.js' and use exported `app`.
 */
import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js';
import { getAnalytics, isSupported } from 'https://www.gstatic.com/firebasejs/11.6.0/firebase-analytics.js';

const firebaseConfig = {
  apiKey: 'AIzaSyAU7J-Hn40cyktbMFplDwUTYKA2D2-h6ec',
  authDomain: 'vride-93a8c.firebaseapp.com',
  projectId: 'vride-93a8c',
  storageBucket: 'vride-93a8c.firebasestorage.app',
  messagingSenderId: '56364906742',
  appId: '1:56364906742:web:7c0bcc00e50505922dd4d7',
  measurementId: 'G-8YT8QV331M',
};

export const app = initializeApp(firebaseConfig);

/* Optional: use from non-module scripts later */
window.__VRIDE_FIREBASE_APP__ = app;

(async () => {
  try {
    if (await isSupported()) {
      getAnalytics(app);
    }
  } catch (_) {
    /* Analytics unavailable (e.g. blocked, privacy tools) */
  }
})();
