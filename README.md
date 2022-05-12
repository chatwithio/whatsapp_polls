# Symfony Docker with Whatapp Api


## Whatsapp API 

There are detailed video in [indepth.webm](indepth.webm) and [intro.webd](intro.webd). This is definitely the best way to get understand it.


![CI](https://github.com/dunglas/symfony-docker/workflows/CI/badge.svg)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/)
2. Run `docker-compose build --pull --no-cache` to build fresh images
3. Run `docker-compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker-compose down --remove-orphans` to stop the Docker containers.

## Features

* Production, development and CI ready
* Automatic HTTPS (in dev and in prod!)
* HTTP/2, HTTP/3 and [Preload](https://symfony.com/doc/current/web_link.html) support
* Built-in [Mercure](https://symfony.com/doc/current/mercure.html) hub
* [Vulcain](https://vulcain.rocks) support
* Just 2 services (PHP FPM and Caddy server)
* Super-readable configuration

# How this widget works 

With this widget you will be able to create surveys with the question of your choice and with four possible answers. 
You can send this survey to as many users as you want. To do this, you will have to use postman to send the messages. 


You will have to fill in the following json where:
1. Number: the number of the creator of the poll.
  2. numbers_poll: the numbers of the users you want to participate in the poll,.
  3. q1: the question of the poll.
  4. a1: first answer.
  5. a2: second answer.
  6. a3: third answer.
  7. a4: fourth answer.



The json will look like this:


    "number":"34000000000",
    
    "numbers_poll": ["34000000000", "34000000000", "34000000000"],
    
    "q1":"What is your favourite season?",
    
    "a1":"Spring",
   
    "a2":"Summer",
    
    "a3":"Autumn",
    
    "a4":"Winter"
    
    



The user will have to send a POST request to the following server: https://poll.wardcampbell.com/chatwith-endpoint.

After sending the request, users should receive the survey message on whatsapp. Users will have to reply with the number that matches the chosen answer. 
In the case of the json above, if their favorite season is summer they will answer with a 2.
Finally, after two hours all users who have participated will receive a message with the results of the final poll.

**Enjoy!**

**Enjoy!**

## Docs

1. [Build options](docs/build.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Installing Xdebug](docs/xdebug.md)
6. [Using a Makefile](docs/makefile.md)
7. [Troubleshooting](docs/troubleshooting.md)

## Credits

Created by [Kévin Dunglas](https://dunglas.fr), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
