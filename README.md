# Ecliptix

## Development

Show available project shortcuts:

```bash
make
```

Start the Docker environment:

```bash
make build
```

Open the application at `https://localhost:8443`.

### Local HTTPS certificate

The development server uses HTTPS with a local Caddy/FrankenPHP certificate authority. On a fresh machine, the browser may show `ERR_CERT_AUTHORITY_INVALID` until you trust that local root certificate.

Export the certificate from the running `app` container:

```bash
docker compose cp app:/data/caddy/pki/authorities/local/root.crt ./localhost-root.crt
```

Linux:

```bash
sudo cp ./localhost-root.crt /usr/local/share/ca-certificates/ecliptix-localhost.crt
sudo update-ca-certificates
```

Windows:

1. Export the certificate:

   ```powershell
   docker compose cp app:/data/caddy/pki/authorities/local/root.crt .\localhost-root.crt
   ```

2. Open `localhost-root.crt`.
3. Click `Install Certificate...`.
4. Choose `Local Machine`.
5. Select `Place all certificates in the following store`.
6. Choose `Trusted Root Certification Authorities`.
7. Finish the wizard and restart the browser.

After the certificate is trusted, open `https://localhost:8443` again.

Run Symfony and Composer commands through the app container unless your local PHP is 8.4:

```bash
docker compose exec app composer install
make migrate
```

The database is exposed only on `127.0.0.1:5432` for local tools.

Database credentials:

```text
Network type: PostgreSQL (TCP/IP)
Hostname / IP: 127.0.0.1
Port: 5432
User: app
Password: DBpassword
Database: app
```
