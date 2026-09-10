
![compatible](https://img.shields.io/badge/PHP%20%3E=8-Compatible-brightgreen.svg)

# mobilecms-api-slim
REST API written in PHP, for managing JSON files and images, with Slim 4

- [website](https://github.com/OlivierB29/mobilecms-vue)
- [admin app](https://github.com/OlivierB29/mobilecms-admin-vue) which uses this API

# Features
It is initially intended to manage a sport organization : News, calendar events, public pages, documents, ...

- Hosted on a cheap server, with no database available
- Authentication with JSON web tokens
- PHP password encryption
- All the data is public, by default. (except users)
- Automatic thumbnails creation

[REST routes](app/routes.php)


# Notes and FAQ
[FAQ](FAQ.md)
