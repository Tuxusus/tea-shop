FROM php:7.4-cli
WORKDIR /app
COPY . .
EXPOSE 10000
CMD ["php", "-S", "0.0.0.0:10000"]
