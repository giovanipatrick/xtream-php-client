# Xtream PHP Client

[![CI](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml)
[![Documentação](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml)
[![Última versão estável](https://poser.pugx.org/giovanipatrick/xtream-php-client/v/stable)](https://packagist.org/packages/giovanipatrick/xtream-php-client)
[![Licença: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

[English](README.md) | Português do Brasil

Um cliente PHP 7.0+ para APIs IPTV Player compatíveis com Xtream.

> Este projeto está em desenvolvimento ativo. A API pública poderá mudar antes
> da primeira versão estável `1.0.0`.

## Recursos

- Informações do perfil da conta e do servidor.
- Endpoints de TV ao vivo, VOD, séries, episódios e EPG.
- Filtros por categoria e paginação local.
- Geração de URLs para live, filme, episódio, timeshift e XMLTV.
- Credenciais codificadas com segurança e exceções sem respostas ou segredos.
- Transporte HTTP injetável para testes determinísticos.
- Respostas raw, Camel Case, Standardized, JSON:API e callbacks customizados.
- Permanecer compatível do PHP 7.0 às versões atuais do PHP.
- Manter o runtime leve e independente de frameworks.

Por padrão, os métodos retornam arrays associativos raw. Serializadores
opcionais podem normalizar todas as respostas sem alterar as chamadas.

## Requisitos

- PHP 7.0 ou mais recente
- Extensão cURL
- Extensão JSON
- Composer

## Instalação

Instale o pacote pelo Packagist:

```bash
composer require giovanipatrick/xtream-php-client
```

## Uso

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Xtream\Client;
use Xtream\Serializer\StandardizedSerializer;

$client = new Client([
    'url' => 'https://example.com',
    'username' => 'username',
    'password' => 'password',
    'preferred_format' => 'm3u8',
    'serializer' => new StandardizedSerializer(),
]);

$profile = $client->getProfile();
$server = $client->getServerInfo();

$channels = $client->getChannels([
    'category_id' => 10,
    'page' => 1,
    'limit' => 50,
]);

$movies = $client->getMovies(['category_id' => 20]);
$movie = $client->getMovie(['movie_id' => 123]);
$shows = $client->getShows(['category_id' => 30]);
$show = $client->getShow(['show_id' => 456]);

$shortEpg = $client->getShortEpg(['channel_id' => 789, 'limit' => 5]);
$fullEpg = $client->getFullEpg(['channel_id' => 789]);
```

Os serializadores disponíveis são `CamelCaseSerializer`,
`StandardizedSerializer` e `JsonApiSerializer`. Use `CallbackSerializer` para
sobrescrever apenas respostas específicas. Omita `serializer` para manter os
arrays raw do provedor.

São aceitos tanto os nomes de opções em `snake_case` quanto as alternativas
camelCase da inspiração em TypeScript. Consulte a
[referência da API](https://giovanipatrick.github.io/xtream-php-client/api-reference/)
para opções de transporte, geração de URLs e comportamento dos erros.

## Desenvolvimento

Instale as dependências de desenvolvimento e execute todas as verificações:

```bash
composer install
composer check
```

Para executar a suíte opcional contra o provedor real, exporte as variáveis de
um arquivo local `.env`, ignorado pelo Git, e execute:

```bash
composer test:integration
```

Testes unitários não devem depender de um provedor IPTV real. O código dos
testes de integração pode ser versionado, mas credenciais, fixtures privadas,
respostas do provedor, URLs de stream geradas e resultados são exclusivamente
locais e ignorados pelo Git. A CI pública não executa testes contra o provedor
real.

## Fluxo de trabalho e releases

- `main` contém somente código revisado e testado.
- O trabalho começa em uma branch dedicada para recurso, correção, documentação
  ou manutenção.
- Pull requests devem passar por toda a matriz de compatibilidade com PHP.
- Commits seguem Conventional Commits, são escritos em inglês e podem incluir
  opcionalmente o emoji descrito no [guia de contribuição](CONTRIBUTING.md).
- Releases seguem Versionamento Semântico e são criados a partir de tags
  assinadas no formato `vX.Y.Z`.

Consulte o [site da documentação](https://giovanipatrick.github.io/xtream-php-client/)
e o [guia de contribuição](CONTRIBUTING.md) para mais detalhes.

## Agradecimentos

Este projeto é inspirado em
[`@iptv/xtream-api`](https://github.com/ektotv/xtream-api). Esta é uma
implementação PHP independente e não possui vínculo com Xtream Codes ou
provedores IPTV.

## Licença

Distribuído sob a [Licença MIT](LICENSE).
