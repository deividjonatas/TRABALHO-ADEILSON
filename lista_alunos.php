<?php
session_start();
include('verifica_login.php');
include('conexao.php');

// Variável para armazenar o termo de pesquisa (se houver)
$termo_pesquisa = isset($_GET['pesquisa']) ? mysqli_real_escape_string($conexao, trim($_GET['pesquisa'])) : '';

// 1. CONSTRUÇÃO DA QUERY SQL PARA LISTAGEM E PESQUISA
$sql = "SELECT 
            aluno_id,             
            nome_aluno,
            data_nascimento,
            endereco_rua,
            endereco_numero,
            endereco_bairro,
            endereco_cep,
            nome_responsavel,
            tipo_responsavel,
            curso 
        FROM 
            alunos";

// Adiciona o filtro de pesquisa, se um termo for fornecido
if (!empty($termo_pesquisa)) {
    $sql .= " WHERE nome_aluno LIKE '%$termo_pesquisa%' 
              OR curso LIKE '%$termo_pesquisa%'
              OR nome_responsavel LIKE '%$termo_pesquisa%'";
}
        
$sql .= " ORDER BY nome_aluno ASC";

$result = mysqli_query($conexao, $sql);

if (!$result) {
    die("Erro na consulta de listagem: " . mysqli_error($conexao));
}

$alunos = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .navbar { background-color: #0d6efd !important; }
        .container-list {
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
                    <li class="nav-item"><a class="nav-link active" href="lista_alunos.php">Alunos</a></li>
                </ul>
                <span class="navbar-text text-white">
                    Olá, <?php echo $_SESSION['email']; ?> | <a href="logout.php" class="text-white text-decoration-none fw-bold">Sair</a>
                </span>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="container-list">
                    <h2 class="mb-4">Lista de Alunos Cadastrados</h2>
                    
                    <form method="GET" action="lista_alunos.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Pesquisar por Nome, Curso ou Responsável..." name="pesquisa" value="<?= htmlspecialchars($termo_pesquisa); ?>">
                            <button class="btn btn-primary" type="submit">Pesquisar</button>
                            <?php if (!empty($termo_pesquisa)): ?>
                                <a href="lista_alunos.php" class="btn btn-secondary">Limpar</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if (empty($alunos)): ?>
                        <div class="alert alert-warning" role="alert">
                            Nenhum aluno encontrado.
                        </div>
                    <?php else: ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome Completo</th>
                                    <th>Curso</th>
                                    <th>Nascimento</th>
                                    <th>Responsável</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alunos as $aluno): ?>
                                <tr>
                                    <td><?= htmlspecialchars($aluno['aluno_id']); ?></td>
                                    <td><?= htmlspecialchars($aluno['nome_aluno']); ?></td>
                                    <td><?= htmlspecialchars($aluno['curso']); ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($aluno['data_nascimento']))); ?></td>
                                    <td><?= htmlspecialchars($aluno['nome_responsavel']); ?></td>
                                    <td>
                                        <a href="editar_aluno.php?id=<?= $aluno['aluno_id']; ?>" class="btn btn-sm btn-warning me-1">Editar</a>
                                        
                                        <a href="excluir_aluno.php?id=<?= $aluno['aluno_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este aluno?');">Excluir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>