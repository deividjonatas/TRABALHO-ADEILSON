<?php
session_start();
include('verifica_login.php');
include('conexao.php');

$mensagem_sucesso = '';
$mensagem_erro = '';

// Processamento do Formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Dados do Formulário (Todos para a tabela ALUNOS) ---
    // NOVO CAMPO NOME COMPLETO
    $nome            = mysqli_real_escape_string($conexao, trim($_POST['nome']));        
    
    // Outros campos de detalhe
    $data_nascimento = mysqli_real_escape_string($conexao, trim($_POST['data_nascimento']));
    $rua             = mysqli_real_escape_string($conexao, trim($_POST['endereco_rua']));
    $numero          = mysqli_real_escape_string($conexao, trim($_POST['endereco_numero']));
    $bairro          = mysqli_real_escape_string($conexao, trim($_POST['endereco_bairro']));
    $cep             = mysqli_real_escape_string($conexao, trim($_POST['endereco_cep']));
    $responsavel     = mysqli_real_escape_string($conexao, trim($_POST['nome_responsavel']));
    $tipo_resp       = mysqli_real_escape_string($conexao, trim($_POST['tipo_responsavel']));
    $curso           = mysqli_real_escape_string($conexao, trim($_POST['curso']));

    // 1. Validação básica
    if (empty($nome) || empty($data_nascimento) || empty($curso)) {
        $mensagem_erro = "Preencha os campos obrigatórios (Nome Completo, Data de Nascimento e Curso).";
    } else {
        
        try {
            // 2. Insere TODOS os detalhes, incluindo o nome, diretamente na tabela alunos.
            // A coluna usada para o nome deve ser 'nome_aluno'.
            $sql_insert_aluno = "INSERT INTO alunos (
                                    nome_aluno, 
                                    data_nascimento, 
                                    endereco_rua, 
                                    endereco_numero, 
                                    endereco_bairro, 
                                    endereco_cep, 
                                    nome_responsavel, 
                                    tipo_responsavel, 
                                    curso
                                ) VALUES (
                                    '$nome', 
                                    '$data_nascimento', 
                                    '$rua', 
                                    '$numero', 
                                    '$bairro', 
                                    '$cep', 
                                    '$responsavel', 
                                    '$tipo_resp', 
                                    '$curso'
                                )";

            if (!mysqli_query($conexao, $sql_insert_aluno)) {
                throw new Exception("Erro ao cadastrar detalhes do aluno: " . mysqli_error($conexao));
            }
            
            $mensagem_sucesso = "Aluno **$nome** cadastrado com sucesso na tabela de detalhes.";
            
            // Limpa as variáveis POST para resetar o formulário após o sucesso
            unset($_POST); 
            
        } catch (Exception $e) {
            $mensagem_erro = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Completo de Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar {
            background-color: #0d6efd !important;
        }
        .container-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="painel.php">Sistema Escolar</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="painel.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="cadastro_aluno_completo.php">Cadastrar Novo</a></li>
                    <li class="nav-item"><a class="nav-link" href="lista_alunos.php">Alunos</a></li>
                </ul>
                <span class="navbar-text text-white">
                    Olá, <?php echo $_SESSION['email']; ?> | <a href="logout.php" class="text-white text-decoration-none fw-bold">Sair</a>
                </span>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="container-form">
                    <h2 class="mb-4">Cadastro de Detalhes do Aluno</h2>
                    
                    <?php if($mensagem_sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $mensagem_sucesso; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <?php if($mensagem_erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $mensagem_erro; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form action="cadastro_aluno_completo.php" method="POST">
                        
                        <h5 class="mb-3 border-bottom pb-2">Dados Pessoais</h5>
                        
                        <div class="mb-3">
                            <label class="mb-2 text-muted">Nome Completo</label>
                            <input type="text" class="form-control" name="nome" value="<?php echo $_POST['nome'] ?? ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="mb-2 text-muted">Curso</label>
                            <select class="form-select" name="curso" required>
                                <option value="" disabled selected>Selecione o Curso...</option>
                                <option value="Desenvolvimento de Sistemas" <?php echo (($_POST['curso'] ?? '') == 'Desenvolvimento de Sistemas' ? 'selected' : ''); ?>>Desenvolvimento de Sistemas</option>
                                <option value="Informática" <?php echo (($_POST['curso'] ?? '') == 'Informática' ? 'selected' : ''); ?>>Informática</option>
                                <option value="Administração" <?php echo (($_POST['curso'] ?? '') == 'Administração' ? 'selected' : ''); ?>>Administração</option>
                                <option value="Enfermagem" <?php echo (($_POST['curso'] ?? '') == 'Enfermagem' ? 'selected' : ''); ?>>Enfermagem</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">Data de Nascimento</label>
                                <input type="date" class="form-control" name="data_nascimento" value="<?php echo $_POST['data_nascimento'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <h5 class="mb-3 border-bottom pb-2 mt-4">Endereço</h5>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="mb-2 text-muted">Rua</label>
                                <input type="text" class="form-control" name="endereco_rua" value="<?php echo $_POST['endereco_rua'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-2 text-muted">Número</label>
                                <input type="text" class="form-control" name="endereco_numero" value="<?php echo $_POST['endereco_numero'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">Bairro</label>
                                <input type="text" class="form-control" name="endereco_bairro" value="<?php echo $_POST['endereco_bairro'] ?? ''; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">CEP</label>
                                <input type="text" class="form-control" name="endereco_cep" value="<?php echo $_POST['endereco_cep'] ?? ''; ?>">
                            </div>
                        </div>

                        <h5 class="mb-3 border-bottom pb-2 mt-4">Responsável</h5>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="mb-2 text-muted">Nome do Responsável</label>
                                <input type="text" class="form-control" name="nome_responsavel" value="<?php echo $_POST['nome_responsavel'] ?? ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-2 text-muted">Tipo (Ex: Pai/Mãe)</label>
                                <input type="text" class="form-control" name="tipo_responsavel" value="<?php echo $_POST['tipo_responsavel'] ?? ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-success btn-lg">Cadastrar Aluno</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>