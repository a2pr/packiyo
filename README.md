# Requirements
- The *create order* and *retrieve order* endpoints should follow the JSON-API spec.
- The models must be such that:
- A customer has products;
- A product has inventory;
- An order has line items;
- The line items in an order reference a given quantity of a given product;
- Orders have a ready_to_ship property which determines whether there is enough inventory to fulfill the order given other existing orders for the same products.

# Run project
## Native Laravel
- install mysql 8.0 and php  8.2
- composer install
- php artisan migrate
- php artisan db:seed
- php artisan serve

## Using docker-compose 
modify .env to match values to for db service in docker-compose
- docker-compose up

To get endpoints: php artisan route:list
run in container

- php artisan migrate
- php artisan db:seed
To set up your database
# Postman collection
In the root of the project there is a postman collection to make each request, just need updates in json body to make request
