<?php
session_start();

// Criar diretório de logs e usuários se não existir
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}
if (!file_exists('users')) {
    mkdir('users', 0777, true);
}

// Função para salvar usuário
function salvarUsuario($username, $password) {
    $usersFile = "users/usuarios.json";
    $users = [];
    
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true);
    }
    
    // Verificar se usuário já existe
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return false;
        }
    }
    
    // Adicionar novo usuário
    $users[] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];
    
    file_put_contents($usersFile, json_encode($users));
    return true;
}

// Função para verificar login
function verificarLogin($username, $password) {
    $usersFile = "users/usuarios.json";
    
    if (!file_exists($usersFile)) {
        return false;
    }
    
    $users = json_decode(file_get_contents($usersFile), true);
    
    foreach ($users as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            return true;
        }
    }
    
    return false;
}

// Função para salvar mensagens
function salvarMensagem($sala, $usuario, $mensagem) {
    $logFile = "logs/sala_{$sala}.log";
    $data = date('Y-m-d H:i:s');
    $logEntry = "[{$data}] {$usuario}: {$mensagem}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Função para ler mensagens
function lerMensagens($sala) {
    $logFile = "logs/sala_{$sala}.log";
    if (file_exists($logFile)) {
        return file_get_contents($logFile);
    }
    return "";
}

// Processar requisições
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['acao'])) {
        switch ($data['acao']) {
            case 'registrar':
                if (isset($data['username']) && isset($data['password'])) {
                    $sucesso = salvarUsuario($data['username'], $data['password']);
                    echo json_encode(['status' => $sucesso ? 'sucesso' : 'erro', 'mensagem' => $sucesso ? 'Usuário registrado com sucesso!' : 'Nome de usuário já existe']);
                }
                break;
                
            case 'login':
                if (isset($data['username']) && isset($data['password'])) {
                    $sucesso = verificarLogin($data['username'], $data['password']);
                    if ($sucesso) {
                        $_SESSION['usuario'] = $data['username'];
                    }
                    echo json_encode(['status' => $sucesso ? 'sucesso' : 'erro', 'mensagem' => $sucesso ? 'Login realizado com sucesso!' : 'Usuário ou senha incorretos']);
                }
                break;
                
            case 'enviar':
                if (isset($_SESSION['usuario']) && isset($data['sala']) && isset($data['mensagem'])) {
                    salvarMensagem($data['sala'], $_SESSION['usuario'], $data['mensagem']);
                    echo json_encode(['status' => 'sucesso']);
                }
                break;
                
            case 'ler':
                if (isset($data['sala'])) {
                    echo json_encode(['mensagens' => lerMensagens($data['sala'])]);
                }
                break;
        }
    }
    exit;
}

// Se não estiver logado, mostrar tela de login
if (!isset($_SESSION['usuario'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Chat Web</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f0f2f5;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .login-container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                width: 100%;
                max-width: 400px;
            }
            .tabs {
                display: flex;
                margin-bottom: 20px;
            }
            .tab {
                flex: 1;
                padding: 10px;
                text-align: center;
                cursor: pointer;
                border-bottom: 2px solid #ddd;
            }
            .tab.ativa {
                border-bottom-color: #1a73e8;
                color: #1a73e8;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                color: #333;
            }
            input {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            button {
                width: 100%;
                padding: 10px;
                background: #1a73e8;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 1em;
            }
            button:hover {
                background: #1557b0;
            }
            .mensagem {
                margin-top: 10px;
                padding: 10px;
                border-radius: 4px;
                display: none;
            }
            .mensagem.sucesso {
                background: #d4edda;
                color: #155724;
                display: block;
            }
            .mensagem.erro {
                background: #f8d7da;
                color: #721c24;
                display: block;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="tabs">
                <div class="tab ativa" onclick="trocarTab('login')">Login</div>
                <div class="tab" onclick="trocarTab('registro')">Registro</div>
            </div>
            
            <form id="loginForm" onsubmit="fazerLogin(event)">
                <div class="form-group">
                    <label for="loginUsername">Nome de Usuário</label>
                    <input type="text" id="loginUsername" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Senha</label>
                    <input type="password" id="loginPassword" required>
                </div>
                <button type="submit">Entrar</button>
                <div id="loginMensagem" class="mensagem"></div>
            </form>
            
            <form id="registroForm" onsubmit="fazerRegistro(event)" style="display: none;">
                <div class="form-group">
                    <label for="registroUsername">Nome de Usuário</label>
                    <input type="text" id="registroUsername" required>
                </div>
                <div class="form-group">
                    <label for="registroPassword">Senha</label>
                    <input type="password" id="registroPassword" required>
                </div>
                <button type="submit">Registrar</button>
                <div id="registroMensagem" class="mensagem"></div>
            </form>
        </div>

        <script>
            function trocarTab(tab) {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('ativa'));
                document.querySelector(`.tab:${tab === 'login' ? 'first-child' : 'last-child'}`).classList.add('ativa');
                
                document.getElementById('loginForm').style.display = tab === 'login' ? 'block' : 'none';
                document.getElementById('registroForm').style.display = tab === 'registro' ? 'block' : 'none';
            }
            
            function fazerLogin(event) {
                event.preventDefault();
                const username = document.getElementById('loginUsername').value;
                const password = document.getElementById('loginPassword').value;
                
                fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        acao: 'login',
                        username: username,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const mensagem = document.getElementById('loginMensagem');
                    mensagem.textContent = data.mensagem;
                    mensagem.className = `mensagem ${data.status}`;
                    
                    if (data.status === 'sucesso') {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                });
            }
            
            function fazerRegistro(event) {
                event.preventDefault();
                const username = document.getElementById('registroUsername').value;
                const password = document.getElementById('registroPassword').value;
                
                fetch('chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        acao: 'registrar',
                        username: username,
                        password: password
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const mensagem = document.getElementById('registroMensagem');
                    mensagem.textContent = data.mensagem;
                    mensagem.className = `mensagem ${data.status}`;
                    
                    if (data.status === 'sucesso') {
                        setTimeout(() => trocarTab('login'), 1000);
                    }
                });
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Web</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f2f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 20px;
        }
        .salas {
            width: 250px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .salas h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #1a73e8;
            text-align: center;
            font-size: 1.5em;
        }
        .chat {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .mensagens {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .mensagem {
            margin-bottom: 10px;
            padding: 8px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .usuario {
            font-weight: bold;
            color: #1a73e8;
        }
        .tempo {
            font-size: 0.8em;
            color: #666;
        }
        .form-envio {
            display: flex;
            gap: 10px;
        }
        input[type="text"] {
            flex: 1;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 8px 16px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #1557b0;
        }
        .sala-btn {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.3s ease;
            color: #000000;
            text-decoration: none;
            font-weight: 500;
        }
        .sala-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            color: #000000;
        }
        .sala-btn.ativa {
            background: #1a73e8;
            color: white;
            border-color: #1a73e8;
            box-shadow: 0 2px 4px rgba(26,115,232,0.2);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .usuario-atual {
            color: #1a73e8;
            font-weight: bold;
        }
        .logout {
            padding: 5px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }
        .logout:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="salas">
            <h3>Salas de Chat</h3>
            <button class="sala-btn" onclick="trocarSala('geral')">Sala Geral</button>
            <button class="sala-btn" onclick="trocarSala('jogos')">Sala de Jogos</button>
            <button class="sala-btn" onclick="trocarSala('tecnologia')">Sala de Tecnologia</button>
        </div>
        <div class="chat">
            <div class="header">
                <div class="usuario-atual">Usuário: <?php echo htmlspecialchars($_SESSION['usuario']); ?></div>
                <a href="?logout=1" class="logout">Sair</a>
            </div>
            <div id="mensagens" class="mensagens"></div>
            <form class="form-envio" onsubmit="enviarMensagem(event)">
                <input type="text" id="mensagem" placeholder="Digite sua mensagem..." required>
                <button type="submit">Enviar</button>
            </form>
        </div>
    </div>

    <script>
        let salaAtual = 'geral';
        
        // Função para trocar de sala
        function trocarSala(sala) {
            salaAtual = sala;
            document.querySelectorAll('.sala-btn').forEach(btn => {
                btn.classList.remove('ativa');
                if (btn.textContent.includes(sala)) {
                    btn.classList.add('ativa');
                }
            });
            carregarMensagens();
        }
        
        // Função para enviar mensagem
        function enviarMensagem(event) {
            event.preventDefault();
            const mensagem = document.getElementById('mensagem').value;
            
            fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    acao: 'enviar',
                    sala: salaAtual,
                    mensagem: mensagem
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    document.getElementById('mensagem').value = '';
                    carregarMensagens();
                }
            });
        }
        
        // Função para carregar mensagens
        function carregarMensagens() {
            fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    acao: 'ler',
                    sala: salaAtual
                })
            })
            .then(response => response.json())
            .then(data => {
                const mensagensDiv = document.getElementById('mensagens');
                mensagensDiv.innerHTML = '';
                
                if (data.mensagens) {
                    const linhas = data.mensagens.split('\n');
                    linhas.forEach(linha => {
                        if (linha.trim()) {
                            const match = linha.match(/\[(.*?)\] (.*?): (.*)/);
                            if (match) {
                                const [_, tempo, usuario, mensagem] = match;
                                const mensagemDiv = document.createElement('div');
                                mensagemDiv.className = 'mensagem';
                                mensagemDiv.innerHTML = `
                                    <span class="usuario">${usuario}</span>
                                    <span class="tempo">${tempo}</span>
                                    <div>${mensagem}</div>
                                `;
                                mensagensDiv.appendChild(mensagemDiv);
                            }
                        }
                    });
                    mensagensDiv.scrollTop = mensagensDiv.scrollHeight;
                }
            });
        }
        
        // Carregar mensagens a cada 2 segundos
        setInterval(carregarMensagens, 2000);
        
        // Carregar mensagens iniciais
        trocarSala('geral');
    </script>
</body>
</html> 