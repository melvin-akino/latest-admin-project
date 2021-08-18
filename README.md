Initial setup for ml-admin-v2:

Copy the .env.example and create a .env file

The .env file must have the following properties:

APP_URL=[site_url]
MIX_API_URL=[site_url]/api

WALLET_URL=[wallet_url]/api/v1
WALLET_CLIENT_ID=[wallet_client]
WALLET_CLIENT_SECRET=[wallet_secret]

DB_CONNECTION=pgsql
DB_HOST=[host]
DB_PORT=[port]
DB_DATABASE=[dbname]
DB_USERNAME=[dbuser]
DB_PASSWORD=[dbpassword]

KAFKA_BROKERS=[kafka_broker]
KAFKA_DEBUG=[true/false]
KAFKA_GROUP_ID=[kafka_group_id]
KAFKA_REACTOR=[true/false]

REDIS_CLIENT=[redis_client]
REDIS_CLUSTER=[redis_cluster]
REDIS_HOST=[redis_host]
QUEUE_CONNECTION=[queue_connection]
REDIS_PORT=[redis_port]
REDIS_DB=[redis_db]
REDIS_CACHE_DB=[redis_cache]

If a key already exists just override its value depending on what you need

Run the following command in order:

$ git fetch origin
$ git checkout master
$ git pull origin master
$ composer install # only if first time or there is a new PHP package
$ php artisan key:generate # one time run
$ php artisan cache:clear
$ php artisan config:clear
$ composer dump-autoload
$ php artisan migrate # only if first time or there is a new migration file (checking can be done using php artisan migrate:status command)
$ php artisan passport:install # one time run
$ npm install  # only if first time or there is a new JS package
$ npm run prod
$ cd /var/www/html/ && chmod -R 775 storage && chown -R $USER:www-data storage
$ cp /etc/php/7.3/cli/conf.d/200-rdkafka.ini /etc/php/7.3/fpm/conf.d/200-rdkafka.ini

Add to supervisor and add
automatching - php artisan automatching
sidebarleagues - php artisan sidebar:leagues



Create an HG client in Multiline Admin

https://dev.admin.multiline.io/
https://uat.admin.multiline.io/
https://admin.multiline.io/

Login Details

email: superadmin@ninepinetech.com
password: 9pinesecurity@dmin

Go to Wallet Clients, click New Client and add an HG client with the following parameters:

Example:
name: HG //ISN //PIN
client_id: hg //isn //pin
client_secret: "40fe9ad4949331a12f5f19b477133924" // md5('hg') | md5('isn') | md5('pin')



