<?php
/**
 * Visualizzatore dei risultati dei test PHPUnit
 * 
 * Questo script analizza il file XML dei risultati dei test (junit.xml) generato da PHPUnit
 * e produce una rappresentazione HTML formattata e interattiva.
 * 
 * Struttura gerarchica dei risultati PHPUnit:
 * - testsuite (livello 1): rappresenta l'intera suite di test del progetto
 * - testsuite (livello 2): rappresenta il namespace/directory dei test
 * - testsuite (livello 3): rappresenta le singole classi di test (9 nel nostro caso):
 *   1. CarrieraLaureandoInformaticaTest
 *   2. CarrieraLaureandoTest 
 *   3. EsameLaureandoTest
 *   4. GestioneCarrieraLaureandoTest
 *   5. GestioneInvioEmailTest
 *   6. GestioneParametriTest
 *   7. ProspettoPDFCommissioneTest
 *   8. ProspettoPDFLaureandoSimulazioneTest
 *   9. ProspettoPDFLaureandoTest
 * 
 * Ogni test suite di livello 3 contiene i test case individuali che testano
 * i vari metodi della classe corrispondente.
 * 
 * @author Andrea Covelli
 */

// Carica il file XML dei risultati
$junitPath = 'junit.xml';
if (!file_exists($junitPath)) {
    die("File dei risultati non trovato");
}

$junit = simplexml_load_file($junitPath);

/**
 * Estrae le statistiche principali dai risultati dei test
 * Include numero totale di test, asserzioni, fallimenti, errori e tempo di esecuzione
 * 
 * @var array $stats Array associativo contenente le metriche principali dei test
 */
$stats = [
    'tests' => (int)$junit->testsuite['tests'],
    'assertions' => (int)$junit->testsuite['assertions'],
    'failures' => (int)$junit->testsuite['failures'],
    'errors' => (int)$junit->testsuite['errors'],
    'time' => (float)$junit->testsuite['time']
];

/**
 * Calcola la percentuale di successo dei test
 * Un test è considerato "di successo" se non ha prodotto né fallimenti né errori
 */
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
        
        /* Stile per l'intestazione del report */
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Grid layout per le statistiche principali */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        /* Card per ogni metrica */
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

        /* Indicatore del tasso di successo con colori semantici */
        .success-rate {
            font-size: 28px;
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: <?php echo $successRate >= 90 ? '#d4edda' : ($successRate >= 70 ? '#fff3cd' : '#f8d7da'); ?>;
            border-radius: 6px;
            color: <?php echo $successRate >= 90 ? '#155724' : ($successRate >= 70 ? '#856404' : '#721c24'); ?>;
        }

        /* Sezione dettagli dei test */
        .test-details {
            margin-top: 20px;
        }

        h3 {
            margin-top: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            color: #2c3e50;
        }

        /* Stile per ogni caso di test */
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
        
        /* Indicatori visivi per test errati/falliti/riusciti */
        .test-case.error {
            border-left: 4px solid #fd7e14; /* o altro colore */
        }
        
        .test-case.failed {
            border-left: 4px solid #dc3545;
        }

        .test-case.success {
            border-left: 4px solid #28a745;
        }

        /* Tempo di esecuzione */
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

        <!-- Statistiche principali in formato card -->
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

        <!-- Sezione dettagli test: visualizza i risultati dettagliati per ogni test suite e test case -->
        <div class="test-details">
            <h2>Dettagli dei Test</h2>
            <?php 
            // Itera attraverso le test suite di livello 3 (classi di test)
            // La struttura $junit->testsuite->testsuite->testsuite riflette la gerarchia:
            // progetto -> namespace -> classe di test
            foreach ($junit->testsuite->testsuite->testsuite as $testSuite): 
                $testSuiteName = (string)$testSuite['name'];
            ?>  
                <!-- Intestazione della test suite con il nome della classe testata -->
                <h3><?php echo htmlspecialchars($testSuiteName); ?></h3>
                <?php
                // Itera attraverso tutti i metodi di test della classe corrente
                foreach ($testSuite->testcase as $testcase):
                    // Determina se il test case ha prodotto fallimenti o errori
                    $hasFailure = isset($testcase->failure);
                    $hasError = isset($testcase->error);
                    $testStatus = $hasError ? 'error' : ($hasFailure ? 'failed' : 'success');

                    // Estrae le informazioni del test case
                    $testName = (string)$testcase['name'];
                    $time = (float)$testcase['time'];
                    $assertions = (int)$testcase['assertions'];
                ?>  
                    <!-- Test case singolo: ogni div rappresenta un metodo di test -->
                    <div class="test-case <?php echo $testStatus; ?>">
                        <!-- Nome del metodo di test -->
                        <div>
                            <strong><?php echo htmlspecialchars($testName); ?></strong>
                        </div>
                        <!-- Metriche del test: tempo di esecuzione e numero di asserzioni -->
                        <div class="test-details">
                            <span class="time">Tempo: <?php echo number_format($time, 3); ?>s</span> | 
                            <span>Asserzioni: <?php echo $assertions; ?></span>
                        </div>
                        <?php
                        // Mostra i dettagli del fallimento o dell'errore
                        if ($hasFailure): 
                        ?>
                            <div style="color: #dc3545; margin-top: 10px;">
                                <?php echo htmlspecialchars((string)$testcase->failure); ?>
                            </div>
                        <?php elseif ($hasError): ?>
                            <div style="color: #fd7e14; margin-top: 10px;">
                                <?php echo htmlspecialchars((string)$testcase->error); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>