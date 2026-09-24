# Xtream PHP Client

[![CI](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml)
[![Documentação](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml)
[![Licença: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

[English](README.md) | Português do Brasil

Um cliente PHP 7.0+ para APIs IPTV Player compatíveis com Xtream.

> Este projeto está em desenvolvimento ativo. A API pública poderá mudar antes
> da primeira versão estável `1.0.0`.

## Objetivos

- Suportar TV ao vivo, VOD, séries, episódios e dados de EPG.
- Gerar URLs de streams ao vivo, filmes, episódios e timeshift.
- Normalizar respostas inconsistentes de provedores compatíveis com Xtream.
- Oferecer serializadores raw, camelCase, padronizado, JSON:API e personalizados.
- Permanecer compatível do PHP 7.0 às versões atuais do PHP.
- Manter o runtime leve e independente de frameworks.

## Requisitos

- PHP 7.0 ou mais recente
- Extensão JSON
- Composer

O transporte HTTP utilizará cURL quando as operações da Player API forem
adicionadas.

## Instalação

O pacote ainda não foi publicado no Packagist. Durante o desenvolvimento,
adicione o repositório como uma dependência VCS:

```bash
composer config repositories.xtream-php-client vcs https://github.com/giovanipatrick/xtream-php-client
composer require giovanipatrick/xtream-php-client:dev-main
```

Após a publicação, versões etiquetadas poderão ser instaladas com:

```bash
composer require giovanipatrick/xtream-php-client
```

## Uso atual

O primeiro marco de desenvolvimento fornece validação das configurações. Os
métodos de rede serão adicionados nos próximos marcos.

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

## Desenvolvimento

Instale as dependências de desenvolvimento e execute todas as verificações:

```bash
composer install
composer check
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
- Commits seguem Conventional Commits, são escritos em inglês e incluem o emoji
  correspondente descrito no [guia de contribuição](CONTRIBUTING.md).
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
