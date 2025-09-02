// public/js/push.js

const VAPID_PUBLIC_KEY = 'BFg5rMhX3mXfsyjof8tqTSeUL8wy-3LZGE835Sh58j1UDLXIBo3aYoa4UrN_dHS0eRvzXSFjZ9Cdtb_bhtn2iHk';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
}

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        console.warn('Service workers not supported');
        return null;
    }
    try {
        return await navigator.serviceWorker.register('/sw.js');
    } catch (err) {
        console.error('Service worker registration failed', err);
        return null;
    }
}

async function subscribeForPush(controlRoomId) {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        alert('Push not supported in this browser.');
        return;
    }

    const registration = await registerServiceWorker();
    if (!registration) return;

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        alert('Notification permission denied');
        return;
    }

    let subscription = await registration.pushManager.getSubscription();
    if (!subscription) {
        subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        });
    }

    // send to backend
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    await fetch('/subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf || ''
        },
        body: JSON.stringify({
            subscription,
            control_room_id: controlRoomId
        })
    });

    return subscription;
}

async function unsubscribePush() {
    const registration = await navigator.serviceWorker.getRegistration();
    if (!registration) return;

    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) return;

    // backend delete
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    await fetch('/unsubscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf || ''
        },
        body: JSON.stringify({ endpoint: subscription.endpoint })
    });

    await subscription.unsubscribe();
}
