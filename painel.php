<?php
session_start();
include('verifica_login.php');
include('conexao.php');

// --- CONSULTAS AO BANCO DE DADOS PARA OS INDICADORES (CARDS) ---

// 1. Total de Alunos (Contando apenas os que têm cadastro completo na tabela alunos)
$query_total = "SELECT COUNT(*) as total FROM alunos";
$result_total = mysqli_query($conexao, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_alunos = $row_total['total'];

// 2. Total Curso: Desenvolvimento de Sistemas
$query_ds = "SELECT COUNT(*) as total FROM alunos WHERE curso = 'Desenvolvimento de Sistemas'";
$result_ds = mysqli_query($conexao, $query_ds);
$ds_total = mysqli_fetch_assoc($result_ds)['total'];

// 3. Total Curso: Enfermagem
$query_enf = "SELECT COUNT(*) as total FROM alunos WHERE curso = 'Enfermagem'";
$result_enf = mysqli_query($conexao, $query_enf);
$enf_total = mysqli_fetch_assoc($result_enf)['total'];

// 4. Total Curso: Administração
$query_adm = "SELECT COUNT(*) as total FROM alunos WHERE curso = 'Administração'";
$result_adm = mysqli_query($conexao, $query_adm);
$adm_total = mysqli_fetch_assoc($result_adm)['total'];

// --- CONSULTAS PARA OS GRÁFICOS (4 Gráficos) ---

// GRÁFICO 1: Alunos por Curso (Barra Vertical)
$query_cursos = "SELECT IF(curso IS NULL OR curso = '', 'Não Informado', curso) AS nome_curso, COUNT(*) as quantidade 
                 FROM alunos 
                 GROUP BY nome_curso 
                 ORDER BY quantidade DESC";
$result_cursos = mysqli_query($conexao, $query_cursos);

$cursos_labels = [];
$cursos_data = [];

while ($row = mysqli_fetch_assoc($result_cursos)) {
    $cursos_labels[] = $row['nome_curso'];
    $cursos_data[] = $row['quantidade'];
}


// GRÁFICO 2: Distribuição por Tipo de Responsável (Doughnut)
$query_resp = "SELECT IF(tipo_responsavel IS NULL OR tipo_responsavel = '', 'Não Informado', tipo_responsavel) AS tipo, COUNT(*) as quantidade 
               FROM alunos 
               GROUP BY tipo 
               ORDER BY quantidade DESC";
$result_resp = mysqli_query($conexao, $query_resp);

$resp_labels = [];
$resp_data = [];

while ($row = mysqli_fetch_assoc($result_resp)) {
    $resp_labels[] = $row['tipo'];
    $resp_data[] = $row['quantidade'];
}


// GRÁFICO 3: Distribuição por Faixa Etária (Barra Vertical) - Lógica PHP para calcular idade
$query_idades = "SELECT data_nascimento FROM alunos WHERE data_nascimento IS NOT NULL AND data_nascimento != '0000-00-00'";
$result_idades = mysqli_query($conexao, $query_idades);

$age_groups = [
    '18-25 anos' => 0, 
    '26-35 anos' => 0, 
    '36-50 anos' => 0,
    '51+ anos' => 0, 
];
$current_year = date('Y');

while ($row = mysqli_fetch_assoc($result_idades)) {
    $birth_year = date('Y', strtotime($row['data_nascimento']));
    $age = $current_year - $birth_year;
    
    if ($age >= 18 && $age <= 25) {
        $age_groups['18-25 anos']++;
    } elseif ($age >= 26 && $age <= 35) {
        $age_groups['26-35 anos']++;
    } elseif ($age >= 36 && $age <= 50) {
        $age_groups['36-50 anos']++;
    } elseif ($age > 50) {
        $age_groups['51+ anos']++;
    }
}
$idades_labels = array_keys($age_groups);
$idades_data = array_values($age_groups);

// GRÁFICO 4: Top 5 Bairros (Barra Horizontal)
$query_bairros = "SELECT IFNULL(endereco_bairro, 'Não Informado') AS bairro, COUNT(*) as quantidade 
                  FROM alunos 
                  GROUP BY bairro 
                  ORDER BY quantidade DESC 
                  LIMIT 5";
$result_bairros = mysqli_query($conexao, $query_bairros);

$bairros_labels = [];
$bairros_data = [];

while ($row = mysqli_fetch_assoc($result_bairros)) {
    $bairros_labels[] = $row['bairro'];
    $bairros_data[] = $row['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f4f6f9;
        }
        .navbar {
            background-color: #0d6efd !important;
        }
        .nav-link {
            color: white !important;
            margin-right: 15px;
        }
        .card-kpi {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            padding: 20px;
        }
        .kpi-title {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 2.5rem;
            color: #333;
            font-weight: 300;
        }
        .chart-header {
            background-color: #0d6efd;
            color: white;
            padding: 10px 15px;
            font-weight: 500;
            border-top-left-radius: 5px;
            border-top-right-radius: 5px;
        }
        .chart-container {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
            padding-bottom: 20px;
            height: 100%; /* Garante altura total na coluna */
        }
        .chart-body {
             padding: 15px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="painel.php">Sistema Escolar</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="painel.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="cadastro_aluno_completo.php">Cadastrar Novo</a></li> 
                    <li class="nav-item"><a class="nav-link" href="lista_alunos.php">Alunos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Relatórios</a></li>
                </ul>
                <span class="navbar-text text-white">
                    Olá, <?php echo $_SESSION['email']; ?> | <a href="logout.php" class="text-white text-decoration-none fw-bold">Sair</a>
                </span>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4">
        
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card card-kpi">
                    <div class="kpi-title">Total de Alunos</div>
                    <div class="kpi-value"><?php echo $total_alunos; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi">
                    <div class="kpi-title">Dev. Sistemas</div>
                    <div class="kpi-value"><?php echo $ds_total; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi">
                    <div class="kpi-title">Enfermagem</div>
                    <div class="kpi-value"><?php echo $enf_total; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-kpi">
                    <div class="kpi-title">Administração</div>
                    <div class="kpi-value"><?php echo $adm_total; ?></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        Alunos por Curso
                    </div>
                    <div class="chart-body">
                        <canvas id="barChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        Distribuição por Tipo de Responsável
                    </div>
                    <div class="chart-body d-flex justify-content-center align-items-center">
                        <canvas id="doughnutChart" style="max-height: 350px; max-width: 450px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">

            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        Distribuição por Faixa Etária
                    </div>
                    <div class="chart-body">
                        <canvas id="barAgeChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        Top 5 Bairros com Maior Número de Alunos
                    </div>
                    <div class="chart-body">
                        <canvas id="horizontalBarChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cores padrão para os gráficos
        const defaultColors = [
            '#0d6efd', // Azul primário
            '#198754', // Verde sucesso
            '#ffc107', // Amarelo aviso
            '#dc3545', // Vermelho perigo
            '#6f42c1', // Roxo
            '#20c997', // Verde água
            '#fd7e14'  // Laranja
        ];
        
        // --- GRÁFICO 1: Alunos por Curso (Barra Vertical) ---
        const ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($cursos_labels); ?>,
                datasets: [{
                    label: 'Quantidade de Alunos',
                    data: <?php echo json_encode($cursos_data); ?>,
                    backgroundColor: defaultColors[0],
                    borderColor: defaultColors[0],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // --- GRÁFICO 2: Responsável (Doughnut Chart) ---
        const ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($resp_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($resp_data); ?>,
                    backgroundColor: defaultColors.slice(0, <?php echo count($resp_data); ?>),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'right' },
                    title: { display: false }
                }
            }
        });

        // --- GRÁFICO 3: Faixa Etária (Barra Vertical) ---
        const ctxBarAge = document.getElementById('barAgeChart').getContext('2d');
        new Chart(ctxBarAge, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($idades_labels); ?>,
                datasets: [{
                    label: 'Quantidade de Alunos',
                    data: <?php echo json_encode($idades_data); ?>,
                    backgroundColor: defaultColors[1],
                    borderColor: defaultColors[1],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true,
                        title: { display: true, text: 'Nº de Alunos' }
                    }
                }
            }
        });

        // --- GRÁFICO 4: Top 5 Bairros (Barra Horizontal) ---
        const ctxHorizontalBar = document.getElementById('horizontalBarChart').getContext('2d');
        new Chart(ctxHorizontalBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($bairros_labels); ?>,
                datasets: [{
                    label: 'Quantidade de Alunos',
                    data: <?php echo json_encode($bairros_data); ?>,
                    backgroundColor: defaultColors[4],
                    borderColor: defaultColors[4],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Torna o gráfico horizontal
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: { display: true, text: 'Nº de Alunos' }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>