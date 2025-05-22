# Chat Web em PHP

Este é um projeto simples de chat web implementado em PHP puro, com suporte a múltiplas salas e logs de mensagens.

## Funcionalidades

- Interface web moderna e responsiva
- Múltiplas salas de chat (Geral, Jogos, Tecnologia)
- Logs de mensagens por sala
- Atualização automática das mensagens
- Nomes de usuário aleatórios

## Requisitos

- PHP 7.0 ou superior
- Servidor web (Apache, XAMPP, WAMP, etc.)

## Como Usar

1. Coloque o arquivo `chat.php` na pasta do seu servidor web
2. Acesse através do navegador: `http://localhost/chat.php`

## Estrutura

- `chat.php` - Arquivo principal do chat (interface e lógica)
- `logs/` - Pasta onde são armazenados os logs das mensagens (criada automaticamente)

## Observações

- As mensagens são salvas em arquivos de log por sala
- Cada usuário recebe um nome aleatório ao entrar
- As mensagens são atualizadas automaticamente a cada 2 segundos 