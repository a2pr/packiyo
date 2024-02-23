# run project
## Native Laravel
- install mysql 8.0 and php  8.2
- composer install
- php artisan migrate
- php artisan db:seed
- php artisan serve

## Using docker-compose 
modify .env to match values to for db service
- docker-compose up

To get endpoints: php artisan route:list
# Postman collection
In the root of the project there is a postman collection to make each request, just need updates in json body to make request
