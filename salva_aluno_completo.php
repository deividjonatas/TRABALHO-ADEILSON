<?php
session_start();
include('verifica_login.php');
include('conexao.php');

// 1. Obter o user_id do usuário logado (chave estrangeira)
$email_logado = mysqli_real_escape_string($conexao, $_SESSION['email']);
$query_user = "SELECT user_id FROM users WHERE user_email = '$email_logado'";
$result_user = mysqli_query($conexao, $query_user);
$user_data = mysqli_fetch_assoc($result_user);

if (!$user_data) {
    $_SESSION['mensagem'] = "Erro: Usuário de login não encontrado.";
    header('Location: painel.php');
    exit();
}
$user_id = $user_data['user_id'];

// 2. Verifica se o cadastro completo já existe (para evitar duplicidade)
$query_check = "SELECT aluno_id FROM alunos WHERE user_fk = '$user_id'";
if (mysqli_num_rows(mysqli_query($conexao, $query_check)) > 0) {
    $_SESSION['mensagem'] = "O cadastro completo para este usuário já existe. Use a página 'Alunos' para editar.";
    header('Location: cadastro_aluno_completo.php');
    exit();
}


// 3. Captura e sanitiza os dados do formulário
$data_nascimento = mysqli_real_escape_string($conexao, trim($_POST['data_nascimento']));
$rua             = mysqli_real_escape_string($conexao, trim($_POST['endereco_rua']));
$numero          = mysqli_real_escape_string($conexao, trim($_POST['endereco_numero']));
$bairro          = mysqli_real_escape_string($conexao, trim($_POST['endereco_bairro']));
$cep             = mysqli_real_escape_string($conexao, trim($_POST['endereco_cep']));
$responsavel     = mysqli_real_escape_string($conexao, trim($_POST['nome_responsavel']));
$tipo_resp       = mysqli_real_escape_string($conexao, trim($_POST['tipo_responsavel']));
$curso           = mysqli_real_escape_string($conexao, trim($_POST['curso']));


// 4. Insere na tabela 'alunos'
$sql = "INSERT INTO alunos (
            user_fk, 
            data_nascimento, 
            endereco_rua, 
            endereco_numero, 
            endereco_bairro, 
            endereco_cep, 
            nome_responsavel, 
            tipo_responsavel, 
            curso
        ) VALUES (
            '$user_id',
            '$data_nascimento',
            '$rua',
            '$numero',
            '$bairro',
            '$cep',
            '$responsavel',
            '$tipo_resp',
            '$curso'
        )";

if (mysqli_query($conexao, $sql)) {
    $_SESSION['mensagem'] = "Cadastro completo do aluno salvo com sucesso!";
    header('Location: painel.php');
    exit();
} else {
    $_SESSION['mensagem'] = "Erro ao salvar cadastro do aluno: " . mysqli_error($conexao);
    header('Location: cadastro_aluno_completo.php');
    exit();
}
?>