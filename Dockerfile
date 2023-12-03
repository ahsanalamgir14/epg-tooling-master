FROM php:8.0-apache
RUN apt update && apt upgrade -y
EXPOSE 80