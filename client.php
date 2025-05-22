<?php
$host = "127.0.0.1"; // IP do servidor
$port = 12345; // Mesma porta do servidor

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, $host, $port);

echo "Ligado ao chat! Digite uma mensagem:\n";

// Configurar o socket para não bloquear
socket_set_nonblock($socket);

while (true) {
    // Verificar se há mensagens para ler
    $mensagem = @socket_read($socket, 1024);
    if ($mensagem !== false && $mensagem !== "") {
        echo "\nMensagem recebida: " . trim($mensagem) . "\n";
    }
    
    // Verificar se há input do usuário
    $read = [STDIN];
    $write = [];
    $except = [];
    
    if (stream_select($read, $write, $except, 0, 200000) > 0) {
        $input = trim(fgets(STDIN));
        if ($input !== "") {
            socket_write($socket, $input . "\n");
        }
    }
    
    // Pequena pausa para não sobrecarregar a CPU
    usleep(100000); // 100ms
}

socket_close($socket);
?> 