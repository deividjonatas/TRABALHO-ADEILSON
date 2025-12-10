<?php
session_start();
include('verifica_login.php');
include('conexao.php');

// Verifica se o ID do aluno foi fornecido na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem_erro'] = "ID do aluno não fornecido para exclusão.";
    header('Location: lista_alunos.php');
    exit();
}

$aluno_id = mysqli_real_escape_string($conexao, $_GET['id']);

try {
    // Instrução SQL para DELETAR o registro
    // Usamos 'aluno_id' como a chave primária
    $sql_delete = "DELETE FROM alunos WHERE aluno_id = '$aluno_id'";

    if (mysqli_query($conexao, $sql_delete)) {
        // Verifica se alguma linha foi realmente afetada (se o aluno existia)
        if (mysqli_affected_rows($conexao) > 0) {
            $_SESSION['mensagem_sucesso'] = "Aluno excluído com sucesso!";
        } else {
            $_SESSION['mensagem_erro'] = "Erro: Aluno com ID $aluno_id não foi encontrado.";
        }
    } else {
        throw new Exception("Erro ao excluir o aluno: " . mysqli_error($conexao));
    }
    
} catch (Exception $e) {
    $_SESSION['mensagem_erro'] = "Falha na exclusão: " . $e->getMessage();
}

// Redireciona de volta para a lista de alunos
header('Location: lista_alunos.php');
exit();
?>