self.addEventListener('push', (event) => {
    if (!event.data) return;
    const data = event.data.json();
    const title = data.title || 'DeliveryApp';
    const options = {
        body: data.body || '',
        icon: 'assets/img/logo-icon.png',
        tag: data.tag || 'delivery-reminder',
        timestamp: Date.now()
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
            const url = 'index.php';
            for (const client of clientList) {
                if (client.url.includes(url) && 'focus' in client) return client.focus();
            }
            return clients.openWindow(url);
        })
    );
});