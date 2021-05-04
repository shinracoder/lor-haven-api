
## Local setup requirements

- Docker
- Composer

## Setup

Run composer install

```
$ composer install
```

From the root of the projet:

```
$ cd docker/
$ docker-compose up -d
```

Next we need to create database and run migrations

```
$ docker exec -it runeterra_api_php bash
$ php artisan migrate:fresh
```

Now we need to download the Latest LOR version by running a console command.

```
$ php artisan runeterra:update
```

If successfull you should be ready to go to test the api is working just check the following url in the browser:
[http://localhost:8091](http://localhost:8091)

