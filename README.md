# ryotcc

## Cloudflare Wrangler

This is a static Cloudflare Worker deployment. Wrangler serves and deploys the
contents of `app/`, including the `/qr/` and `/np/` pages.

Install the dependencies and authenticate with Cloudflare:

```sh
npm install
npx wrangler login
```

Run the site locally with Wrangler:

```sh
npm run dev
```

Build the service worker and deploy the site:

```sh
npm run deploy
```