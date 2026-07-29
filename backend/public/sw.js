/* LatestDeal.in Enterprise Native Service Worker v2.0 */

self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'LatestDeal Alert',
            body: event.data.text(),
            url: '/'
        };
    }

    const title = payload.title || '🔥 New Deal Alert - LatestDeal.in';
    const options = {
        body: payload.body || 'Check out the latest verified discount now!',
        icon: payload.icon || '/images/logo.png',
        badge: payload.badge || '/images/logo.png',
        image: payload.image || null,
        tag: payload.tag || 'latestdeal-notification',
        renotify: true,
        requireInteraction: false,
        actions: payload.actions || [
            { action: 'open_url', title: 'View Deal' }
        ],
        data: {
            url: payload.url || '/',
            extra: payload.extra || {}
        }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = (event.notification.data && event.notification.data.url) 
        ? event.notification.data.url 
        : '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});

self.addEventListener('notificationclose', function (event) {
    // Optional telemetry hook for notification dismissals
    console.log('LatestDeal notification dismissed:', event.notification.tag);
});
