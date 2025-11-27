FROM node:20-alpine

WORKDIR /var/www/frontend

COPY package.json package-lock.json ./
RUN npm install

COPY . .

# Build production
RUN npm run build

EXPOSE 5173

CMD ["npm", "run", "dev"]
