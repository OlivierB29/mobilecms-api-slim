# Configuration
## Main configuration

-  "publicdir": "/public"
Directory of public content. The real path will be something like /var/www/html/public

-  "privatedir": "/../private"
Directory of public content. The real path will be something like /var/www/private

-  "media": "media"
Directory of media files. The real path will be something like /var/www/html/media

- "mailsender": "sendmail@example.org"

## Security (be cautious)


-  "enablecleaninputs": "true"
Should stay to true, for safety. When enabled, sanitize inputs.

-  "https": "true|false"
Set if https is mandatory.


-  "errorlog": "true"
Enable error log on backend


-  "enablemail": "true|false"
Enable send mail for password reset

-  "passwordreseturl": "https://example.org/adminpage/temporary-login"
Absolute URL of the admin frontend's temporary-login page. Password reset links
expire after 30 minutes. Use the frontend's production URL in production and
the Vite development URL when testing locally.

-  "debugnotifications": "true|false"
Legacy notification setting. Password reset links are never returned by the API.
