<?php
session_start();
include('verifica_login.php');
include('conexao.php');

$mensagem_sucesso = '';
$mensagem_erro = '';

// 1. OBTÉM O ID DO ALUNO DA URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Se o ID não for fornecido, redireciona de volta para a lista
    header('Location: lista_alunos.php');
    exit();
}

$aluno_id = mysqli_real_escape_string($conexao, $_GET['id']);

// Variável para armazenar os dados do aluno para preencher o formulário
$dados_aluno = null;

// 2. PROCESSAMENTO DA ATUALIZAÇÃO (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Recebe os dados atualizados do Formulário ---
    $nome            = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $data_nascimento = mysqli_real_escape_string($conexao, trim($_POST['data_nascimento']));
    $rua             = mysqli_real_escape_string($conexao, trim($_POST['endereco_rua']));
    $numero          = mysqli_real_escape_string($conexao, trim($_POST['endereco_numero']));
    $bairro          = mysqli_real_escape_string($conexao, trim($_POST['endereco_bairro']));
    $cep             = mysqli_real_escape_string($conexao, trim($_POST['endereco_cep']));
    $responsavel     = mysqli_real_escape_string($conexao, trim($_POST['nome_responsavel']));
    $tipo_resp       = mysqli_real_escape_string($conexao, trim($_POST['tipo_responsavel']));
    $curso           = mysqli_real_escape_string($conexao, trim($_POST['curso']));

    // 2.1. Validação básica
    if (empty($nome) || empty($data_nascimento) || empty($curso)) {
        $mensagem_erro = "Preencha os campos obrigatórios (Nome Completo, Data de Nascimento e Curso).";
    } else {
        
        try {
            // 2.2. QUERY SQL para Atualizar os dados na tabela alunos
            $sql_update = "UPDATE alunos SET
                                nome_aluno = '$nome', 
                                data_nascimento = '$data_nascimento', 
                                endereco_rua = '$rua', 
                                endereco_numero = '$numero', 
                                endereco_bairro = '$bairro', 
                                endereco_cep = '$cep', 
                                nome_responsavel = '$responsavel', 
                                tipo_responsavel = '$tipo_resp', 
                                curso = '$curso'
                           WHERE aluno_id = '$aluno_id'";

            if (!mysqli_query($conexao, $sql_update)) {
                throw new Exception("Erro ao atualizar o aluno: " . mysqli_error($conexao));
            }
            
            $mensagem_sucesso = "Aluno **$nome** atualizado com sucesso!";
            
            // Para garantir que o formulário exiba os dados recém-salvos
            // Não limpamos o POST, apenas seguimos para o passo 3 novamente.
            
        } catch (Exception $e) {
            $mensagem_erro = $e->getMessage();
        }
    }
}

// 3. BUSCA OS DADOS DO ALUNO PARA EXIBIÇÃO NO FORMULÁRIO
$sql_select = "SELECT * FROM alunos WHERE aluno_id = '$aluno_id'";
$result_select = mysqli_query($conexao, $sql_select);

if (mysqli_num_rows($result_select) === 0) {
    // Se o ID for válido, mas o aluno não existir
    header('Location: lista_alunos.php');
    exit();
}

$dados_aluno = mysqli_fetch_assoc($result_select);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno: <?= htmlspecialchars($dados_aluno['nome_aluno'] ?? 'Aluno'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar { background-color: #0d6efd !important; }
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
                    <li class="nav-item"><a class="nav-link" href="cadastro_aluno_completo.php">Cadastrar Novo</a></li>
                    <li class="nav-item"><a class="nav-link" href="lista_alunos.php">Lista de Alunos</a></li>
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
                    <h2 class="mb-4">Editar Aluno: <?= htmlspecialchars($dados_aluno['nome_aluno'] ?? 'Detalhes'); ?></h2>
                    
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

                    <form action="editar_aluno.php?id=<?= htmlspecialchars($aluno_id); ?>" method="POST">
                        
                        <h5 class="mb-3 border-bottom pb-2">Dados Pessoais</h5>
                        
                        <div class="mb-3">
                            <label class="mb-2 text-muted">Nome Completo</label>
                            <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($dados_aluno['nome_aluno'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="mb-2 text-muted">Curso</label>
                            <select class="form-select" name="curso" required>
                                <option value="" disabled>Selecione o Curso...</option>
                                <?php 
                                $cursos = ['Desenvolvimento de Sistemas', 'Informática', 'Administração', 'Enfermagem'];
                                $curso_atual = $dados_aluno['curso'] ?? '';
                                foreach ($cursos as $c):
                                ?>
                                <option value="<?= $c; ?>" <?= ($curso_atual === $c ? 'selected' : ''); ?>>
                                    <?= $c; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">Data de Nascimento</label>
                                <input type="date" class="form-control" name="data_nascimento" value="<?= htmlspecialchars($dados_aluno['data_nascimento'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <h5 class="mb-3 border-bottom pb-2 mt-4">Endereço</h5>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="mb-2 text-muted">Rua</label>
                                <input type="text" class="form-control" name="endereco_rua" value="<?= htmlspecialchars($dados_aluno['endereco_rua'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-2 text-muted">Número</label>
                                <input type="text" class="form-control" name="endereco_numero" value="<?= htmlspecialchars($dados_aluno['endereco_numero'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">Bairro</label>
                                <input type="text" class="form-control" name="endereco_bairro" value="<?= htmlspecialchars($dados_aluno['endereco_bairro'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 text-muted">CEP</label>
                                <input type="text" class="form-control" name="endereco_cep" value="<?= htmlspecialchars($dados_aluno['endereco_cep'] ?? ''); ?>">
                            </div>
                        </div>

                        <h5 class="mb-3 border-bottom pb-2 mt-4">Responsável</h5>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="mb-2 text-muted">Nome do Responsável</label>
                                <input type="text" class="form-control" name="nome_responsavel" value="<?= htmlspecialchars($dados_aluno['nome_responsavel'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="mb-2 text-muted">Tipo (Ex: Pai/Mãe)</label>
                                <input type="text" class="form-control" name="tipo_responsavel" value="<?= htmlspecialchars($dados_aluno['tipo_responsavel'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning btn-lg">Salvar Edição</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>