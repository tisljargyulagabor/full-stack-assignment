FROM node:20-alpine

WORKDIR /app

# 1. Csak a package fájlokat másoljuk át
COPY package*.json ./

# 2. Itt települnek a konténernek megfelelő (Linux) függőségek
RUN npm install

# 3. Másoljuk az összes forráskódot
COPY . .

EXPOSE 5173

# A --host 0.0.0.0 kötelező, hogy a Caddy kívülről elérje
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]