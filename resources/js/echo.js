import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

if (window.reportChannelToken) {
    window.Echo
        .channel(`reports.${window.reportChannelToken}`)
        .listen('.report.updated', (event) => {
            console.log('Report update:', event.message);

            const status = document.getElementById('report-status');
            if (status) {
                status.className = event.reportUrl
                    ? 'alert alert-success'
                    : 'alert alert-info';
                status.textContent = event.message;
            }

            if (event.reportUrl) {
                console.log('Report URL:', event.reportUrl);

                if (status) {
                    const link = document.createElement('a');
                    link.href = event.reportUrl;
                    link.textContent = ' Download the report';
                    link.target = '_blank';
                    link.rel = 'noopener';
                    status.appendChild(link);
                }
            }
        });
}
