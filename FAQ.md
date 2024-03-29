# Requirements
- php-xdebug
- php-imagick
- php-gd
- php-mbstring

# Dev requirements
- php-xdebug
- libapache2-mod-php

# Directory structure
Assume `www/html/` is the web root context.

- `www/html/public` : public database
- `www/html/api` : PHP files for API
- `www/private` : private directory (users)


# Development with Composer
- `composer start`
- `php composer.phar start`

# Debian 12
`sudo apt install php-gd php-mbstring php-xml php-imagick`
`sudo apt install composer php-curl php-xdebug`

# Server on Ubuntu
With a default Ubuntu, you may need : 
PHP 7.x
`sudo apt install php-xdebug libapache2-mod-php php-gd php-mbstring php-xml php-imagick`

PHP 8.0
`sudo apt install  php8.0-gd php8.0-mbstring php8.0-xml php8.0-imagick`

# VS Code Debugging
- Check version
Ubuntu 20.04 : XDebug v2.x.x
Debian 12


- Install and enable in php.ini :
https://marketplace.visualstudio.com/items?itemName=felixfbecker.php-debug

- Start a debug session
- Add a breakpoint
- Run `composer test`


# FAQ

- Why not using a true CMS on a web hosting package ?
Value for money. A true CMS embeds too much unwanted features, such as public comments. Calendar events are not shipped with a CMS, and require a plugin.

- And a hosted CMS ?
I prefer a domain name, instead of mysite.company.com

- Why JSON files over a SQL database ?
Some entry level offers don't have any database, shipped with 10MB of file storage, such as a domain name package.
In future plans, with the growing data, the database may become useful. For now, we have 10-20 news per year, and roughly the same for calendar events.

# Common issues

- Q : "@ error/constitute.c/ReadImage/412" or "Web : ImagickException: attempt to perform an operation not allowed by the security policy `PDF’" when running PHPUnit on Ubuntu 18.04/20.04
- A: [Imagick - ImagickException not authorized @ error/constitute.c/ReadImage/412 error](https://stackoverflow.com/questions/52817741/imagick-imagickexception-not-authorized-error-constitute-c-readimage-412-err)

- alternate 
```
sudo cp /etc/ImageMagick-6/policy.xml /etc/ImageMagick-6/policy.xml.bak
sudo sed -i "s/rights\=\"none\" pattern\=\"PS\"/rights\=\"read\|write\" pattern\=\"PS\"/" /etc/ImageMagick-6/policy.xml
sudo sed -i "s/rights\=\"none\" pattern\=\"PDF\"/rights\=\"read\|write\" pattern\=\"PDF\"/" /etc/ImageMagick-6/policy.xml
sudo sed -i "s/rights\=\"none\" pattern\=\"XPS\"/rights\=\"read\|write\" pattern\=\"XPS\"/" /etc/ImageMagick-6/policy.xml
```

- Q: When running phpunit : `Class 'DOMDocument' not found`
- A: Install php-xml (https://stackoverflow.com/questions/14395239/class-domdocument-not-found#14395414)

- Q: Can't login and browser debugger prints 404
- A: Install [mod_rewrite](https://stackoverflow.com/questions/17745310/how-to-enable-mod-rewrite-in-lamp-on-ubuntu#17745379)
On Ubuntu 16.04, the file path is /etc/apache2/sites-available/000-default.conf
