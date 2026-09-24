# Primeiros passos

## Requisitos

- PHP 7.0 ou mais recente
- Extensão cURL
- Extensão JSON
- Composer

## Instalação durante o desenvolvimento

Instale o pacote pelo Packagist:

```bash
composer require giovanipatrick/xtream-php-client
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

## Fazer requisições

```php
$profile = $client->getProfile();
$categories = $client->getChannelCategories();
$channels = $client->getChannels(['category_id' => 10]);
```

Atualmente, os métodos retornam arrays associativos raw recebidos do provedor.
