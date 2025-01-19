<?php
// test_result.php

// Carica il file XML dei risultati
$junitPath = 'junit.xml';
if (!file_exists($junitPath)) {
    die("File dei risultati non trovato");
}

$junit = simplexml_load_file($junitPath);

// Estrai le statistiche principali
$stats = [
    'tests' => (int)$junit->testsuite['tests'],
    'assertions' => (int)$junit->testsuite['assertions'],
    'failures' => (int)$junit->testsuite['failures'],
    'errors' => (int)$junit->testsuite['errors'],
    'time' => (float)$junit->testsuite['time']
];

// Calcola la percentuale di successo
$successRate = ($stats['tests'] > 0) ? 
    (($stats['tests'] - $stats['failures'] - $stats['errors']) / $stats['tests']) * 100 : 
    0;
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Risultati dei Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .success-rate {
            font-size: 28px;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: <?php echo $successRate >= 90 ? '#d4edda' : ($successRate >= 70 ? '#fff3cd' : '#f8d7da'); ?>;
            border-radius: 6px;
            color: <?php echo $successRate >= 90 ? '#155724' : ($successRate >= 70 ? '#856404' : '#721c24'); ?>;
        }

        .test-details {
            margin-top: 20px;
        }

        h3 {
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            color: #2c3e50;
        }

        .test-case {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .test-case .test-details {
            margin-top: 5px;
            font-size: 1em;
            color: #666;
        }

        .test-case.failed {
            border-left: 4px solid #dc3545;
        }

        .test-case.success {
            border-left: 4px solid #28a745;
        }

        .time {
            color: #666;
            font-size: 1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Risultati dei Test PHPUnit</h1>

        <div class="success-rate">
            Tasso di Successo: <?php echo number_format($successRate, 1); ?>%
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div>Test Totali</div>
                <div class="stat-value"><?php echo $stats['tests']; ?></div>
            </div>
            <div class="stat-card">
                <div>Asserzioni</div>
                <div class="stat-value"><?php echo $stats['assertions']; ?></div>
            </div>
            <div class="stat-card">
                <div>Fallimenti</div>
                <div class="stat-value"><?php echo $stats['failures']; ?></div>
            </div>
            <div class="stat-card">
                <div>Errori</div>
                <div class="stat-value"><?php echo $stats['errors']; ?></div>
            </div>
            <div class="stat-card">
                <div>Tempo Totale</div>
                <div class="stat-value"><?php echo number_format($stats['time'], 2); ?>s</div>
            </div>
        </div>

        <div class="test-details">
            <h2>Dettagli dei Test</h2>
            <?php foreach ($junit->testsuite->testsuite->testsuite as $testSuite): ?>
                <h3><?php echo htmlspecialchars((string)$testSuite['name']); ?></h3>
                <?php foreach ($testSuite->testcase as $testcase): ?>
                    <?php
                    $hasFailure = isset($testcase->failure);
                    $testName = (string)$testcase['name'];
                    $time = (float)$testcase['time'];
                    $assertions = (int)$testcase['assertions'];
                    ?>
                    <div class="test-case <?php echo $hasFailure ? 'failed' : 'success'; ?>">
                        <div>
                            <strong><?php echo htmlspecialchars($testName); ?></strong>
                        </div>
                        <div class="test-details">
                            <span class="time">Tempo: <?php echo number_format($time, 3); ?>s</span> | 
                            <span>Asserzioni: <?php echo $assertions; ?></span>
                        </div>
                        <?php if ($hasFailure): ?>
                            <div style="color: #dc3545; margin-top: 10px;">
                                <?php echo htmlspecialchars((string)$testcase->failure); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>