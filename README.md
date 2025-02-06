# V1.1.1
**Full Changelog**: https://github.com/princepines/asset-inventory-web/compare/1.1.0...1.1.1

## Changes (In general)
- Fixed Editing Asset
- Fixed Help

### Install
#### How to use this system

1. From here download the .zip file, extract it, and open the folder
2. Move all files from the unzipped folder to your webserver (if using xampp: it is on htdocs)
3. create a folder called `uploads`
`If you are using Linux, please set the chmod to 777 of uploads folder`
4. Do not start the webserver, proceed to Setup

#### Setup

1. on your mysql/mariadb database server, create a database
2. edit the `config.php` from your webserver and edit the following:
```php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', "insert db server/ip");
define('DB_USERNAME', "insert username");
define('DB_PASSWORD', "insert password");
define('DB_NAME', "insert your db name you created earlier");
define('DB_PORT', 3306); # edit the 3306 to the specific db server port
```
5. Start your webserver

##### Default Login (please do this so that you can add users)
Username: superadmin

Password: superadmin

### Acknowledgements
I would like to give thanks to:
- Alyza Facundo
- Angel Lou Cahinhinan
- Harvey De Vera

For such a fantastic groupmates, and also i would like to give thanks to:
- Prof. Reagan Ricafort
- Mr. Christian Jay Lanzar
- Ms. Jabelle Gatpo

For throughly checking our documentation and system, although it is not perfect.

And also i would like to thank my providers from development to deployment:
- GitHub
- Azure
- Informatics College Northgate
