# Primeiros passos

## Requisitos

- PHP 7.0 ou mais recente
- Extensão JSON
- Composer

## Instalação durante o desenvolvimento

Até a primeira publicação no Packagist, instale diretamente do repositório Git:

```bash
composer config repositories.xtream-php-client vcs https://github.com/giovanipatrick/xtream-php-client
composer require giovanipatrick/xtream-php-client:dev-main
```

## Configurar o cliente

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Xtream\Client;

$client = new Client([
    'url' => 'https://example.com',
    'username' => 'username',
    'password' => 'password',
    'preferred_format' => 'm3u8',
]);
```

Nunca faça commit de credenciais reais. Nas aplicações, carregue-as por
variáveis de ambiente ou por um gerenciador de segredos.

