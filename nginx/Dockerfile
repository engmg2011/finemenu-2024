#Create NGINX
FROM nginx:1.19.1-alpine AS prod-stage
#COPY --from=build /app/dist /usr/share/nginx/html
#### docker cp ./default.conf backend-nginx-1:/etc/nginx/conf.d/default.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx","-g","daemon off;"]
