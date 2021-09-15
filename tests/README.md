# Unit tests
- Requirements : Composer, Xdebug
- `composer install`
- `vendor/bin/phpunit --configuration phpunit.xml`
- `vendor/bin/phpunit --configuration phpunit.xml --filter testPostBBCode`

# Debug unit tests
## Xdebug on Ubuntu 16.04 
- [Xdebug on Ubuntu 16.04](http://www.dieuwe.com/blog/xdebug-ubuntu-1604-php7)

## Xdebug on Ubuntu 20.04 
- `sudo apt-get install php-xdebug`
- edit /etc/php/7.4/cli/php.ini
```
[XDebug]
xdebug.remote_enable = 1
xdebug.remote_autostart = 1
```
## Xdebug with PHP 8.0 and pecl install
sudo pecl channel-update pecl.php.net
sudo pecl install xdebug-3.0.0 

- edit /etc/php/8.0/cli/php.ini
```
[xdebug]
zend_extension=/usr/lib/php/20200930/xdebug.so
xdebug.mode=debug
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.start_upon_error=yes
```

## Xdebug 3 and VS Code
.vscode/launch.json
"port": 9003

## Slim API doesn't work 
Especially after a long time...

- Double check in a browser
http://localhost:8888/mobilecmsapi/v2/cmsapi/status
Should return HTTP 401

- 2nd test
http://localhost:8888/mobilecmsapi/v2/foo/bar
Should return HTTP 400 with

{
    "type": "NOT_ALLOWED",
    "description": "Method not allowed. Must be one of: OPTIONS"
}

For a successful login, check existing users !


## Docker
- install and run
`sudo docker-compose up -d`

- list containers
`sudo docker container ps -a`