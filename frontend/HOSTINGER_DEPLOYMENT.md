# Production deployment checklist

This application now supports real-time messaging through Laravel Echo. Reverb is the default broadcaster; the application stays usable if the WebSocket service temporarily cannot be reached, but live updates need a running WebSocket provider.

## Choose the WebSocket setup first

- **Hostinger VPS:** use Laravel Reverb. Run `php artisan reverb:start --host=127.0.0.1 --port=8080` under Supervisor or systemd and proxy HTTPS/WSS traffic to it with Nginx or Apache.
- **Hostinger Web or Cloud shared hosting:** use a hosted Pusher-compatible WebSocket provider instead. Those plans cannot host Reverb's permanent incoming WebSocket process. Set `BROADCAST_CONNECTION=pusher` and `VITE_BROADCASTER=pusher`, then fill the matching `PUSHER_*` and `VITE_PUSHER_*` variables.

For either option, set exact HTTPS origins in `REVERB_ALLOWED_ORIGINS`, use `wss` in production, and rebuild the front-end after changing any `VITE_*` variable because Vite embeds them into the generated assets.

Example Reverb settings for a VPS (replace the domain and credentials):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
SESSION_SECURE_COOKIE=true
BROADCAST_CONNECTION=reverb
VITE_BROADCASTER=reverb

REVERB_APP_ID=your-id
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_HOST=ws.example.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_ALLOWED_ORIGINS=https://example.com
REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=8080
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Deploy safely

1. Keep the Laravel project outside the public web root whenever possible. Point the domain document root to `frontend/public`; never expose `.env`, `app`, `config`, `vendor`, or `storage` to the web.
2. Set a real `APP_KEY`, database credentials, SMTP credentials, `APP_URL`, and unique `ADMIN_EMAIL` / `ADMIN_PASSWORD` values in the production `.env`. Do not run the default database seeder in production without those values.
3. On the server, run:

   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. Build the assets with the final production `VITE_*` variables, then deploy `public/build` with the release:

   ```bash
   npm ci
   npm run build
   ```

   Do not deploy `public/hot`; it is only a local Vite development marker and would make production pages look for a local development server.
5. Ensure `storage` and `bootstrap/cache` are writable by the web user. New PWD IDs and employer documents are stored on Laravel's private `local` disk. Existing legacy documents in `storage/app/public` should be moved out of the public disk after verifying the new secure admin download route.
6. Set PHP `upload_max_filesize` to at least `10M` and `post_max_size` higher than `10M` so employer document uploads work reliably.
7. Add the Hostinger cron entry below once per minute so employer document expiry is refreshed daily by Laravel:

   ```cron
   * * * * * cd /path/to/frontend && php artisan schedule:run >> /dev/null 2>&1
   ```

## Verify after deployment

- Register an employer and verify the four private documents receive system-assigned expiry dates.
- Publish a job, open the applicant job-match page, and apply. The employer's configured requirements should appear immediately in the private conversation.
- Open the same conversation in two sessions and send a message. It should appear live when the WebSocket provider is connected; if the provider is offline, the message remains saved and appears on refresh.
- Test email verification over the public HTTPS domain. Signed links depend on the correct `APP_URL`.

Hostinger references: [WebSocket/socket policy](https://www.hostinger.com/support/1583738-are-sockets-supported-at-hostinger/), [Laravel deployment and cron guidance](https://www.hostinger.com/support/6152127-how-to-deploy-laravel-8-at-hostinger/), and [PHP upload settings](https://www.hostinger.com/support/4622479-how-to-change-values-of-php-parameters-in-hostinger/).
