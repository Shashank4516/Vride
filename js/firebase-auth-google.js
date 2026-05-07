/**
 * Firebase Google sign-in → PHP session via firebase_session.php
 * Uses popup first (works reliably on PHP dev servers); falls back to redirect if popup is blocked.
 */
import { app } from './firebase-init.js';
import {
  getAuth,
  GoogleAuthProvider,
  signInWithPopup,
  signInWithRedirect,
  getRedirectResult,
} from 'https://www.gstatic.com/firebasejs/11.6.0/firebase-auth.js';

/** Resolve session endpoint vs current page (subfolders, trailing paths). */
function sessionEndpoint() {
  return new URL('firebase_session.php', window.location.href).href;
}

async function bridgePhpSession(token) {
  const r = await fetch(sessionEndpoint(), {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ idToken: token }),
  });

  const text = await r.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    const preview = text.replace(/\s+/g, ' ').slice(0, 280);
    throw new Error(
      preview
        ? `Server returned non-JSON (${r.status}). ${preview}`
        : `Bad response (${r.status}). Check PHP errors on firebase_session.php.`
    );
  }

  if (!r.ok || !data.ok) {
    throw new Error(data.error || `Sign-in failed (${r.status})`);
  }
  return typeof data.redirect === 'string' && data.redirect !== '' ? data.redirect : 'index.php';
}

async function finishSignIn(user) {
  const token = await user.getIdToken();
  const path = await bridgePhpSession(token);
  window.location.assign(path);
}

function showFirebaseError(message) {
  const el = document.getElementById('firebase-auth-alert');
  if (!el) return;
  el.textContent = message;
  el.removeAttribute('hidden');
  el.style.display = 'flex';
}

(async () => {
  const auth = getAuth(app);
  const provider = new GoogleAuthProvider();
  provider.setCustomParameters({ prompt: 'select_account' });

  /* Return from OAuth redirect */
  try {
    const result = await getRedirectResult(auth);
    if (result?.user) {
      await finishSignIn(result.user);
      return;
    }
  } catch (e) {
    console.error(e);
    showFirebaseError(
      e?.message ||
        'Google redirect sign-in failed. Enable Google provider in Firebase and add this host under Authorized domains.'
    );
  }

  const goGoogle = async () => {
    try {
      const cred = await signInWithPopup(auth, provider);
      await finishSignIn(cred.user);
    } catch (e) {
      if (e?.code === 'auth/popup-closed-by-user') {
        return;
      }
      if (
        e?.code === 'auth/popup-blocked' ||
        e?.code === 'auth/cancelled-popup-request' ||
        e?.code === 'auth/operation-not-supported-in-this-environment'
      ) {
        try {
          await signInWithRedirect(auth, provider);
        } catch (e2) {
          console.error(e2);
          showFirebaseError(e2?.message || 'Redirect sign-in failed. Allow pop-ups or try again.');
        }
        return;
      }
      console.error(e);
      showFirebaseError(e?.message || 'Google sign-in failed.');
    }
  };

  document.getElementById('firebase-google-login')?.addEventListener('click', (ev) => {
    ev.preventDefault();
    goGoogle();
  });
  document.getElementById('firebase-google-register')?.addEventListener('click', (ev) => {
    ev.preventDefault();
    goGoogle();
  });

  if (window.location.search.includes('social=google')) {
    await goGoogle();
  }
})();
