# Chat via Socket em PHP

Este é um projeto simples de chat implementado usando sockets em PHP, permitindo comunicação entre múltiplos clientes através de um servidor central.

## Requisitos

- PHP 7.0 ou superior
- Extensão de sockets do PHP habilitada

## Como Executar

1. Primeiro, inicie o servidor:
```bash
php server.php
```

2. Em outro terminal, inicie um ou mais clientes:
```bash
php client.php
```

3. Digite suas mensagens no terminal do cliente e pressione Enter para enviar.

## Funcionalidades

- Conexão múltipla de clientes
- Comunicação em tempo real
- Suporte para Windows e Linux
- Processamento assíncrono de mensagens

## Observações

- O servidor deve ser iniciado antes dos clientes
- Para encerrar o programa, pressione Ctrl+C
- As mensagens são enviadas para todos os clientes conectados, exceto o remetente 