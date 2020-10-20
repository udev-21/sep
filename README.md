<p align="center"><a href="http://voyager.ecrm.uz" target="_blank"><h2>Search Expired Passports</h2></a></p>

## SEP


- ``git clone https://github.com/udev-21/sep.git``
- ``cd sep``
- ``composer install``
- ``cp .env.example .env`` (then configure .env file)
- ``php artisan key:generate``
- ``php artisan migrate``
- ``php artisan --debug=1 import:data`` (for updating data)
- ``php artisan serve``
