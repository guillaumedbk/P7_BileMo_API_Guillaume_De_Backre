# BileMo API

Create a web service exposing an API

## Git Clone

```bash
git clone https://github.com/guillaumedbk/P7_BileMo_API_Guillaume_De_Backre.git
```
## Composer install

```bash
composer install
```
## Configure DB
Change the database connection values for correct ones in the .env file.

## Launch the creation of the database
```bash
bin/console doctrine:database:create
```
## Build the database structure
```bash
bin/console doctrine:migrations:migrate
```
## Load the initial dataset
```bash
bin/console hautelook:fixtures:load
```
## Lauch symfony server
```bash
symfony server:start
```


## Documentation
All available endpoints are described in the API documentation accessible in your web browser at https://localhost:8000/api/doc/

## Symfony Insight
https://insight.symfony.com/projects/91052858-c3dc-4074-a7a9-eecc01eb0792
