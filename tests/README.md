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
