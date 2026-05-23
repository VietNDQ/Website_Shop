# Stage 1: Build the Vue application
FROM node:18-alpine AS build-stage
WORKDIR /app
COPY fe-3d/package*.json ./
RUN npm install
COPY fe-3d/ ./
RUN npm run build

# Stage 2: Serve the built assets using Nginx
FROM nginx:alpine
COPY --from=build-stage /app/dist /usr/share/nginx/html
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80 443
CMD ["nginx", "-g", "daemon off;"]
