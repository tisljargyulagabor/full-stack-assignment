# 🚀 UCC Project — Full Stack Web Application
Vue 3 + Laravel 11 + Docker + Caddy + Gemini AI

Ez a projekt egy modern, teljes stack webalkalmazás, amely Laravel 11 API backendből, Vue 3 frontendből és egy Caddy alapú HTTPS reverse proxyból áll.  
A teljes rendszer Docker környezetben fut, automatikus SSL kezeléssel és integrált AI chatbot funkcióval.

---

# 🛠 Technológiai Stack

## Backend
- PHP 8.5
- Laravel 11
- Laravel Sanctum
- Laravel Fortify
- PostgreSQL

## Frontend
- Vue 3
- Vite
- TailwindCSS
- Axios

## AI Integráció
- Google Gemini API
- Chatbot funkció

## Infrastructure
- Docker
- Docker Compose
- Caddy reverse proxy
- HTTPS / SSL (self-signed development CA)

---

# 🏁 Gyorsindítás

## 1️⃣ Előfeltételek

Telepítve kell legyen:

- Docker
- Docker Compose
- Git
- Google Gemini API kulcs

---

## 2️⃣ Projekt klónozása

```bash
git clone <repo_url>
cd vue-laravel-ucc-project-task
```

---

## 3️⃣ Konténerek felépítése és indítása

```bash
docker-compose up -d --build
```

Ez elindítja:

- backend (Laravel API)
- frontend (Vue + Vite)
- PostgreSQL adatbázis
- Caddy reverse proxy

---

## 4️⃣ Backend adatbázis inicializálás

Migrációk és seed adatok futtatása:

```bash
docker exec -it backend php artisan migrate --seed
```

---

# 🌐 Elérési címek

A rendszer HTTPS-en keresztül érhető el:

Frontend:
https://uccproject.localhost

Backend API:
https://api.uccproject.localhost

---

# 🔒 HTTPS és SSL beállítása (Manuális — fejlesztői környezet)

A projekt kizárólag HTTPS-en fut az alábbi funkciók miatt:

- secure cookie kezelés
- Sanctum auth
- MFA
- modern browser security policy

Self-signed tanúsítvány miatt a böngésző figyelmeztethet — ezt manuálisan hitelesíteni kell.

---

## 🪪 Tanúsítvány kinyerése a Caddy konténerből

```bash
docker exec -it caddy_proxy cat /data/caddy/pki/authorities/local/root.crt
```

---

## 💾 Tanúsítvány mentése

Másold ki a teljes kimenetet:

-----BEGIN CERTIFICATE-----
...
-----END CERTIFICATE-----

Mentsd el:

uccproject.localhost.crt

---

## 🖥 Windows tanúsítvány telepítés

1. Dupla kattintás a fájlra
2. Tanúsítvány telepítése
3. Helyi gép
4. Minden tanúsítvány elhelyezése a következő tárolóba
5. Tallózás →
   Trusted Root Certification Authorities
6. OK → Tovább → Befejezés

---

## 🔄 Chrome újraindítása

chrome://restart

Ha blokkol:

- kattints az oldalra
- gépeld be: thisisunsafe

---

# 🔑 Környezeti változók

Backend env fájl:

backend/.env

```env
GEMINI_API_KEY=a_te_google_gemini_api_kulcsod
```

---

# 🤖 AI Chatbot

Google Gemini API alapú chatbot.

Funkciók:

- természetes nyelvű válaszadás
- backend API integráció
- Vue chat UI
- token alapú kommunikáció

---

# 👤 Teszt admin felhasználó

Migráció + seed után:

Email: admin@example.com  
Jelszó: password

---

# 📁 Projekt szerkezet

/frontend  
Vue 3 + Vite + Tailwind

/backend  
Laravel 11 API  
Sanctum + Fortify

docker-compose.yml  
Infrastruktúra leírás

Caddyfile  
HTTPS + reverse proxy config

.gitignore  
Tiltólista

uccproject.localhost.crt  
Root tanúsítvány

---

# 🧪 Fejlesztői parancsok

Backend shell:

```bash
docker exec -it backend bash
```

Artisan parancsok:

```bash
php artisan migrate
php artisan db:seed
php artisan route:list
php artisan tinker
```

---

# 📜 Logok megtekintése

```bash
docker logs backend
docker logs frontend
docker logs caddy_proxy
```

---

# 🔧 Új build futtatása

Frontend rebuild:

```bash
docker-compose build frontend
```

Teljes rebuild:

```bash
docker-compose up -d --build
```

---

# 🧹 Konténerek leállítása

```bash
docker-compose down
```

Teljes reset volume törléssel:

```bash
docker-compose down -v
```

---

# ⚠️ Gyakori hibák

Port ütközés:
- állítsd át a docker-compose.yml-ben
- vagy állíts le más web szervert

Tanúsítvány hiba:
- ellenőrizd a root.crt telepítést
- indítsd újra a böngészőt

---

# 📅 Projekt dátum

2026. február

---

# 📄 Licenc

Oktatási és demonstrációs célra készült projekt.

---

# ✨ Kész

Teljesen konténerizált, HTTPS-képes, AI integrációval bővített modern full-stack alkalmazás.
