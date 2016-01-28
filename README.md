# pay4later/phpcomodo-server

A web front-end to pass POST requests to cmdscan to verify files are free from viruses.

## Requirements

- PHP >= 5.5
- [Comodo Antivirus for Linux](https://www.comodo.com/home/internet-security/antivirus-for-linux.php)

## Installation

```sh
composer install
composer generate-hydrators
cd config/
cp local.php.dist local.php
vim local.php
```

## Usage

Start a php web server in the public directory if required: `php -S localhost:8080 -t public/`.

@TODO see demo.php for supported POST formats.
