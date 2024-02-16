# Mini cheat sheet

## List all containers
`sudo docker container ps -a`

## start, stop
`sudo docker-compose up -d`

`sudo docker-compose down`

`sudo docker-compose pull`

`sudo docker-compose restart`

## recreate
`sudo docker-compose up --force-recreate --build -d`

## remove
`docker-compose stop && docker-compose rm -f`
or
`sudo docker container stop ID`
`sudo docker rm ID`

## bash

`sudo docker exec -it php74-dev_app_1 bash`
