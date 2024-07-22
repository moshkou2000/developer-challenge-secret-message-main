# Assessment Developer - Secret message

At BAS Tech we primarily use PHP for our service development. We create web applications, 
APIs, microservices, (native) apps and a monolithic intranet. 

We're very interested to see how you develop. Having something in a working fashion is
secondary to be being able to explain why you did what you did. 

This is because we believe that anyone can learn a language, framework and ecosystem 
surrounding a language/framework. The hard parts are explaining choices you made, following
design patterns and writing human-readable code. 

## Requirements

- Use Git versioning system
- Your application must run with a maximum of 3 commands:
  - clone
  - install dependencies
  - (optional) start Docker containers

If you forego the Docker part, we will be running it in a Docker instance running: 

- PHP ^8.1
- MySQL ^8.0
- Node ^17

Make sure to upload your project to a VCS system (preferably Github) and provide us with
the (public) link to be able to clone it. 

_**Spend an approximate maximum of 4 hours on the assignment**_

_Remember: your choices of how to work matter more than the final product_

### Optional things

- Use a framework of choice
- If instructions are needed for us to run your project, make sure to include them in the ReadMe
- Have the project be (partially) tested

## Assignment

Be able to share an encrypted message with a colleague. 

Message: 

- text
- recipient
- created at

Expiry:

- read once, then delete
- delete after X period

Reading Message:

- Provide identifier for message
- Provide decryption key

Recipient:

- identifier

_**Remember: it's not a requirement to have everything ready! Your choices matter more than
a finished product!**_



# Implementation

- default laravel cipher AES-256-CBC 

## CLI

```
composer install
php artisan migrate
```

> Note: ENV -> DB_HOST= if use docker 'db' else '127.0.0.1'

> NOTE: Swagger http://localhost:8000/api/documentation
    composer require darkaonline/l5-swagger
    php artisan l5-swagger:generate

## CronJob

```
php artisan schedule:work
php artisan schedule:run
```


## Test

### Create a Message

```
curl -X POST http://localhost:8000/messages \
    -H "Content-Type: application/json" \
    -d '{"message": "This is a secret message", "recipient": "recipient@example.com", "expiry": 60}'
```


### Read a Message

```
curl -X GET http://localhost:8000/messages/{identifier} \
    -H "Content-Type: application/json" \
    -d '{"decryption_key": "provided_decryption_key"}'
```


### Delete a Message

```
curl -X DELETE http://localhost:8000/messages/{identifier}
```