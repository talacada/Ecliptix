# Registration Improvements — Design Spec

**Date**: 2026-07-28
**Status**: approved

## Overview

Four features bundled into one change:
1. **Email verification** — account inactive until email confirmed, 24h token expiry
2. **Character customization at registration** — race + appearance (hair, eyes, mouth, nose, ears), purely cosmetic
3. **Password reset** — forgot password flow via email, same token pattern as verification
4. **Rate limiting** — brute-force protection on login, register, and password reset endpoints

## New Entities

### Race

| Column | Type | Notes |
|---|---|---|
| `id` | int, PK, auto | |
| `name` | string(255) | "Člověk", "Elf", "Démon", "Goblin", "Trpaslík" |

### AppearanceOption

| Column | Type | Notes |
|---|---|---|
| `id` | int, PK, auto | |
| `race_id` | FK → Race | |
| `type` | string / enum | `hair`, `eyes`, `mouth`, `nose`, `ears` |
| `label` | string(255) | "Krátké", "Dlouhé", "Cop" |
| `sort_order` | int | Pořadí v UI šipkách |

**Validace na úrovni procesoru:** `hair_id` musí mít `type = 'hair'` a `race_id = zvolené_race_id`. Obdobně pro ostatní kategorie.

### EmailVerificationToken

| Column | Type | Notes |
|---|---|---|
| `id` | int, PK, auto | |
| `character_id` | FK → Character, unique (OneToOne) | Jeden character = max 1 token |
| `token` | string, unique | UUID |
| `expires_at` | datetime | now + 24h |
| `used_at` | datetime, nullable | null = nepoužitý |

### ---- PasswordResetToken

| Column | Type | Notes |
|---|---|---|
| `id` | int, PK, auto | |
| `character_id` | FK → Character, unique (OneToOne) | Jeden character = max 1 aktivní reset token |
| `token` | string, unique | UUID |
| `expires_at` | datetime | now + 1h (kratší než verifikace — bezpečnost) |
| `used_at` | datetime, nullable | null = nepoužitý |

## Character Changes

Nové sloupce:

| Column | Type | Default |
|---|---|---|
| `race_id` | FK → Race, required | — |
| `hair_id` | FK → AppearanceOption, required | — |
| `eyes_id` | FK → AppearanceOption, required | — |
| `mouth_id` | FK → AppearanceOption, required | — |
| `nose_id` | FK → AppearanceOption, required | — |
| `ears_id` | FK → AppearanceOption, required | — |
| `email_verified` | bool | `false` |

## API Contract

### POST /api/auth/register — extended

Request:
```json
{
  "username": "Ragnar",
  "email": "ragnar@example.com",
  "password": "tajneheslo",
  "raceId": 1,
  "hairId": 5,
  "eyesId": 12,
  "mouthId": 8,
  "noseId": 3,
  "earsId": 20
}
```

Response: `201 Created`, empty body or status message. **No token returned** — user must verify email first.

### GET /api/auth/verify-email?token=<uuid>

Public, no auth required.

| Outcome | Redirect |
|---|---|
| Success | `{FRONTEND_URL}/login?verified=ok` |
| Token not found | `{FRONTEND_URL}/login?verified=error&reason=not-found` |
| Already used | `{FRONTEND_URL}/login?verified=error&reason=already-used` |
| Expired (>24h) | `{FRONTEND_URL}/login?verified=error&reason=expired` |

### POST /api/auth/login — change

After password validation, check `email_verified`. If `false` → `403 Forbidden "Email not verified"`.

No other changes. Credential error stays ambiguous (`401 "Invalid credentials"`).

### GET /api/auth/register/options

Public, no auth required. Response:

```json
{
  "races": [
    {
      "id": 1,
      "name": "Člověk",
      "appearance": {
        "hair": [{"id": 1, "label": "Krátké"}, {"id": 2, "label": "Dlouhé"}],
        "eyes": [{"id": 10, "label": "Modré"}, {"id": 11, "label": "Hnědé"}],
        "mouth": [...],
        "nose": [...],
        "ears": [...]
      }
    }
  ]
}
```

### PATCH /character — extended

Accepts `raceId`, `hairId`, `eyesId`, `mouthId`, `noseId`, `earsId`.

**Cost**: 5 diamonds per change. Validated before update — if `character.diamonds < 5` → `402 Payment Required`.

Same validation as registration: appearance IDs must match race and type.

On race change: all appearance IDs must be re-sent (old ones won't match new race).

### POST /api/auth/request-password-reset

Public, no auth required. Request:

```json
{
  "email": "ragnar@example.com"
}
```

**Always returns 200** with same message — neprozrazuje, jestli email v systému existuje:
```json
{
  "message": "If the email exists, a reset link has been sent."
}
```

Pokud email existuje: vygeneruje `PasswordResetToken` (UUID, expires_at = now+1h), smaže starý token pokud existuje, pošle email s reset odkazem.

### POST /api/auth/reset-password

Public, no auth required. Request:

```json
{
  "token": "uuid-z-emailu",
  "password": "noveTajneHeslo123"
}
```

Response 200:
```json
{
  "message": "Password has been reset."
}
```

| Condition | HTTP | Message |
|---|---|---|
| Token not found | 422 | `Invalid or expired token.` |
| Token expired (>1h) | 422 | `Invalid or expired token.` |
| Token already used | 422 | `Invalid or expired token.` |
| New password too short | 422 | Standardní validační chyba |

Stejná chybová zpráva pro všechny selhání tokenu — neprozrazuje, který scénář nastal.

## Rate Limiting

Používá se Symfony RateLimiter komponenta. Konfigurace v `config/packages/rate_limiter.yaml`.

| Endpoint | Limit | Co se stane při překročení |
|---|---|---|
| `POST /api/auth/login` | 5 pokusů/minutu na IP | 429 Too Many Requests |
| `POST /api/auth/register` | 3 pokusy/minutu na IP | 429 Too Many Requests |
| `POST /api/auth/request-password-reset` | 3 pokusy/minutu na IP | 429 Too Many Requests |

Rate limiter se kontroluje **před** jakoukoliv business logikou — na úrovni listeneru/event subscriberu, nebo jako první krok v procesoru. Tím se zabrání zbytečným DB dotazům při útoku.

## Data Flow

### Registration

```
RegisterInput (DTO) → RegisterProcessor
  1. Validate email/username uniqueness
  2. Validate raceId exists
  3. For each appearance ID: exists + type match + race match
  4. Create Character, set all fields (emailVerified = false)
  5. Generate EmailVerificationToken (UUID, expires_at = now+24h)
  6. Dispatch verification email (async via Messenger)
  7. Persist + flush (single transaction)
  8. Return 201
```

### Email Verification

```
GET /auth/verify-email?token=uuid → VerifyEmailAction (controller)
  1. Find token by value
  2. Check not used (usedAt === null)
  3. Check not expired (expiresAt > now)
  4. Set Character.emailVerified = true
  5. Set token.usedAt = now
  6. flush → 302 redirect
```

### Login

```
LoginInput → LoginProcessor (existing + 1 check)
  After password validation:
  if (!character.isEmailVerified()) → 403
  else → JWT token as before
```

### Password Reset — Request

```
POST /auth/request-password-reset → RequestPasswordResetProcessor
  1. Rate limit check (3/min/IP)
  2. Find character by email
  3. If found:
     a. Remove any existing PasswordResetToken for this character
     b. Generate new PasswordResetToken (UUID, expires_at = now+1h)
     c. Dispatch reset email (async via Messenger)
     d. Persist + flush
  4. If NOT found: do nothing (stejná odpověď)
  5. Always return 200 (stejná zpráva)
```

### Password Reset — Apply

```
POST /auth/reset-password → ResetPasswordProcessor
  1. Rate limit check (3/min/IP)
  2. Find token by value
  3. Check not used + not expired (jednotná chyba "Invalid or expired token")
  4. Validate new password (@Length(min: 8))
  5. Hash new password, set on Character
  6. Mark token.usedAt = now
  7. flush → 200
```

### Rate Limiting — Implementation Pattern

```
RateLimitListener (event subscriber on kernel.controller)
  1. Map route name → rate limiter config
  2. Create limiter for request IP
  3. $limiter->consume(1)
  4. If rejected → throw TooManyRequestsHttpException (429)
  5. Otherwise → continue to controller/processor
```

Používáme IP-based limiting — pro MVP stačí. Do budoucna lze přidat composite limit (IP + email).

## Error Reference

| Condition | HTTP | Message |
|---|---|---|
| Email exists | 422 | `Email already registered` |
| Username exists | 422 | `Username already registered` |
| Invalid race | 422 | `Invalid race` |
| Invalid appearance option | 422 | `Invalid hair option` / `Invalid eyes option` / ... |
| Option doesn't belong to race | 422 | `Hair option does not belong to selected race` |
| Option has wrong type | 422 | `hairId must reference an option of type 'hair'` |
| Login: email not verified | 403 | `Email not verified` |
| Login: bad credentials | 401 | `Invalid credentials` |
| PATCH: insufficient diamonds | 402 | `Insufficient diamonds. Required: 5` |
| Password reset: invalid/expired token | 422 | `Invalid or expired token.` |
| Rate limit exceeded (any endpoint) | 429 | `Too many attempts. Try again later.` |

## Email

- Sender: `noreply@<domain>` (configured via env)
- Subject: "Vítej v Ecliptixu — ověř svůj účet"
- Template: `templates/email/verify.html.twig` + `verify.txt.twig`
- No CSS, simple text + verification link
- Link base URL: `VERIFY_EMAIL_URL` env variable (set to frontend URL)
- Dispatched via `Symfony\Mailer` → Messenger async transport

### Password Reset Email

- Subject: "Ecliptix — obnova hesla"
- Template: `templates/email/reset-password.html.twig` + `reset-password.txt.twig`
- Link vede na frontend: `{FRONTEND_URL}/reset-password?token={token}`
- Stejný odesílatel a dispatch pattern jako verifikační email

## Files to Create / Modify

| File | Action |
|---|---|
| `src/Entity/Character/Race.php` | New entity |
| `src/Entity/Character/AppearanceOption.php` | New entity |
| `src/Entity/Character/EmailVerificationToken.php` | New entity |
| `src/Entity/Character/PasswordResetToken.php` | New entity |
| `src/Repository/Character/PasswordResetTokenRepository.php` | New |
| `src/Repository/Character/RaceRepository.php` | New |
| `src/Repository/Character/AppearanceOptionRepository.php` | New |
| `src/Repository/Character/EmailVerificationTokenRepository.php` | New |
| `src/Entity/Character/Character.php` | Modify — add new fields + update ApiResource operations |
| `src/ApiResource/Auth/RegisterInput.php` | Modify — add appearance fields |
| `src/ApiResource/Auth/RegisterOptionsResponse.php` | New DTO (output) |
| `src/State/Processor/Auth/RegisterProcessor.php` | Modify — validation + token + email |
| `src/State/Processor/Auth/LoginProcessor.php` | Modify — email_verified check |
| `src/State/Processor/Character/CharacterUpdateProcessor.php` | New — handles PATCH /character with diamond cost |
| `src/State/Provider/Auth/RegisterOptionsProvider.php` | New — provides race/appearance data |
| `src/Controller/Auth/VerifyEmailAction.php` | New — handles token verification + redirect |
| `src/State/Processor/Auth/RequestPasswordResetProcessor.php` | New |
| `src/State/Processor/Auth/ResetPasswordProcessor.php` | New |
| `src/ApiResource/Auth/RequestPasswordResetInput.php` | New DTO |
| `src/ApiResource/Auth/ResetPasswordInput.php` | New DTO |
| `src/EventSubscriber/RateLimitSubscriber.php` | New — rate limit before controller |
| `config/packages/rate_limiter.yaml` | Modify — add limiter configs |
| `templates/email/verify.html.twig` | New |
| `templates/email/verify.txt.twig` | New |
| `templates/email/reset-password.html.twig` | New |
| `templates/email/reset-password.txt.twig` | New |
| Migration files (auto-generated) | New |
| Data fixtures for Race + AppearanceOption | New (Foundry) |

## Future Improvements (out of scope for this change)

- OAuth (Google, Discord) login — přirozené pro herní komunitu
- Two-factor authentication — až bude co krást (diamanty prémiového hráče)
- Welcome flow / tutorial after first login — navazuje na `emailVerified` flag
- Invite-only registration with codes — pro řízené spouštění
- Unlockable appearance options (event skins, achievements)
