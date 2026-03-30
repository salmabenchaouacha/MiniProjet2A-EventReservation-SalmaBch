<img width="350" height="150" alt="Gemini_Generated_Image_m72hrvm72hrvm72h-removebg-preview" src="https://github.com/user-attachments/assets/b8c6264d-8539-4e1e-a01b-9dde227c3141" />


# Web Application for Event Reservation Management


- JWT authentication
- refresh tokens
- WebAuthn / passkeys
- event management
- reservation management
- MYSQL + Docker

## Stack

- Symfony 7.4
- PHP 8.2+
- MYSQL
- Docker Compose
- LexikJWTAuthenticationBundle
- Gesdinet JWT Refresh Token Bundle
- WebAuthn Symfony Bundle
## Features
## User Side:
- Register and login with password or Passkey (biometric/PIN)
- Browse upcoming events
- View event details
- Reserve a spot at an event
- Confirmation page after reservation
## Admin Side
- Secure login (separate from users)
- Full CRUD on events (with image upload)
- View all reservations per event
- Secure logout
## Security
- JWT tokens for stateless API authentication
- Passkeys (WebAuthn/FIDO2) — passwordless login
- Refresh tokens 
- Role-based access control (ROLE_USER, ROLE_ADMIN)

## Run The Project
Clone the repository
```bash
git clone https://github.com/salmabenchaouacha/MiniProjet2A-EventReservation-SalmaBch
cd MiniProjet2A-EventReservation-SalmaBch
```
From the project root:

```bash
docker compose up --build -d
docker compose exec php composer install
```

## Environment

Set your real JWT passphrase in .env

```env
JWT_PASSPHRASE=your_real_secure_passphrase_here
```

Then generate the JWT keypair:

```bash
docker compose exec php php bin/console lexik:jwt:generate-keypair --overwrite
docker compose exec php php bin/console cache:clear
```


Development database:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```
Open in browser

```bash
http://localhost:8000
```

# Seeded Accounts

Fixtures create:

- **Admin:** `admin@example.com` / `admin123`
- **Users:** `user@example.com` / `user123`
# DEMO:
<img width="1653" height="811" alt="image" src="https://github.com/user-attachments/assets/6f1f99ee-4f93-49e4-8838-7487285b292e" />
<img width="1667" height="392" alt="image" src="https://github.com/user-attachments/assets/a8b8e9e4-e8a0-42fa-abc7-75087b2b9eb1" />
=============================================================================
<img width="1665" height="822" alt="image" src="https://github.com/user-attachments/assets/26c7ecff-c3cc-4c65-af0a-963ac8775dfa" />
=============================================================================
<img width="1677" height="812" alt="image" src="https://github.com/user-attachments/assets/b2433d9d-ff6e-4590-9a70-6bce73baddbe" />
<img width="1647" height="821" alt="image" src="https://github.com/user-attachments/assets/2b5ad012-2b7b-41dc-8de9-cdbdaefd07cb" />
=============================================================================
<img width="1651" height="795" alt="image" src="https://github.com/user-attachments/assets/ae3800ed-2564-45ba-9ff0-297872f1094a" />

<img width="1647" height="562" alt="image" src="https://github.com/user-attachments/assets/bd9f1381-fd5e-4dbf-8093-2dd6f0759ef9" />
=============================================================================
ADMIN :
<img width="1673" height="807" alt="image" src="https://github.com/user-attachments/assets/615bdfb0-bccc-4ed6-97bd-51ebbe5b16e9" />
=============================================================================
<img width="1671" height="612" alt="image" src="https://github.com/user-attachments/assets/9179ad8e-524f-4b8d-b67e-0404f1815faf" />
=============================================================================
<img width="1676" height="705" alt="image" src="https://github.com/user-attachments/assets/6257ffae-e981-48c4-bb28-251be345b888" />
<img width="1673" height="632" alt="image" src="https://github.com/user-attachments/assets/512f874e-7303-40c0-b87f-2e9ce58c70f2" />
=============================================================================
<img width="1672" height="652" alt="image" src="https://github.com/user-attachments/assets/c2852c61-c8b4-41eb-8e1a-a809acdc45e4" />
<img width="1618" height="657" alt="image" src="https://github.com/user-attachments/assets/26b44137-292d-4895-a37c-efdb7895be2b" />

