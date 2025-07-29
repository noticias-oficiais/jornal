<?php

session_start();
date_default_timezone_set('America/Sao_Paulo');

function log_debug($mensagem) {
    $logPath = __DIR__ . '/debug.log';
    $data = date('Y-m-d H:i:s');
    $linha = "[$data] $mensagem\n";
    file_put_contents($logPath, $linha, FILE_APPEND);
}

if (!isset($_POST['cpf'])) {
    http_response_code(400);
    log_debug("CPF não informado");
    echo json_encode(['error' => 'CPF não informado']);
    exit;
}

$cpf = preg_replace('/\D/', '', $_POST['cpf']);
log_debug("Recebido CPF: $cpf");

$url = "https://apela-api.tech?user=ab34229f-cd38-4621-97a9-6c1c7cbd354b&cpf={$cpf}";
$response = @file_get_contents($url);

if ($response === false) {
    http_response_code(500);
    log_debug("Erro ao consultar a URL externa");
    echo json_encode(['error' => 'Erro ao consultar dados']);
    exit;
}

$data = json_decode($response, true);

if (!$data || !isset($data['status']) || $data['status'] !== 200) {
    http_response_code(500);
    log_debug("Resposta inválida da API: " . $response);
    echo json_encode(['error' => 'Erro ao consultar dados']);
    exit;
}

$_SESSION['nome'] = $data['nome'];
$_SESSION['mae'] = $data['mae'];
$_SESSION['nascimento'] = $data['nascimento'];
$_SESSION['sexo'] = $data['sexo'];
$_SESSION['cpf'] = $data['cpf'];

log_debug("Consulta bem-sucedida para CPF $cpf");

echo json_encode([
    'status' => 200,
    'dadosBasicos' => [
        'nome' => $_SESSION['nome'],
        'cpf' => $_SESSION['cpf']
    ]
]);
