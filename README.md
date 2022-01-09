# API_Framework

A PHP Framework for creating APIs

<a href="http://jwt.io">
  <img src="http://jwt.io/img/badge-compatible.svg" />
</a>

## PHP Version

This project is created using PHP 8.0+

Coding Standard is PSR-12

## Installation

```bash
$ composer update
$ composer dump-autoload
```

Import api.sql to your phpmyadmin

Generate 256-bit key from here [allkeysgenerator](https://www.allkeysgenerator.com/Random/Security-Encryption-Key-Generator.aspx)

Update .env file

The system has 2 authentication methods
  
1. JWT Token [token]
2. API Key [key]

After that just create an account using /register endpoint and POST method

```bash
$ httpie post http://localhost/api/register name="Root" username="admin" password="admin"
```

Enjoy

## Example Project

TaskController.php

TaskGateway.php

Are an example project to show how the system work

## License

MIT

## Copyright

2022 - fariscode
