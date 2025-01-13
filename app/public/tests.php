<?php
require_once(__DIR__ . '/../vendor/autoload.php');

class TestRunner {
    private array $results;
    
    public function __construct() {
        date_default_timezone_set('Europe/Rome');
        $this->results = [
            'startTime' => microtime(true),
            'dateTime' => date('d/m/Y H:i:s'),
            'totalTests' => 0,
            'passedTests' => 0,
            'failedTests' => 0,
            'classResults' => [],
            'systemInfo' => $this->getSystemInfo()
        ];
    }
    
    private function getSystemInfo(): array {
        return [
            'phpVersion' => PHP_VERSION,
            'phpunitVersion' => PHPUnit\Runner\Version::id(),
            'os' => PHP_OS,
            'timezone' => date_default_timezone_get()
        ];
    }
    
    public function runTests(string $directory): array {
        foreach (glob($directory . "/*Test.php") as $testFile) {
            require_once $testFile;
            $className = basename($testFile, '.php');
            
            $class = new ReflectionClass($className);
            $this->processTestClass($class, $className);
        }
        
        $this->results['executionTime'] = round(microtime(true) - $this->results['startTime'], 2);
        $this->results['successRate'] = $this->calculateSuccessRate();
        
        return $this->results;
    }
    
    private function processTestClass(ReflectionClass $class, string $className): void {
        $this->results['classResults'][$className] = [
            'name' => $className,
            'tests' => [],
            'total' => 0,
            'passed' => 0,
            'failed' => 0
        ];
        
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (strpos($method->name, 'test') === 0) {
                $this->processTestMethod($class, $method, $className);
            }
        }
    }
    
    private function processTestMethod(ReflectionClass $class, ReflectionMethod $method, string $className): void {
        $methodName = str_replace('test', '', $method->name);
        $methodName = ucfirst(preg_replace('/(?<!^)[A-Z]/', ' $0', $methodName));
        
        $testResult = [
            'name' => $methodName,
            'passed' => false,
            'error' => null
        ];
        
        try {
            $test = $class->newInstance();
            
            if ($class->hasMethod('setUp')) {
                $setUpMethod = $class->getMethod('setUp');
                $setUpMethod->setAccessible(true);
                $setUpMethod->invoke($test);
            }
            
            $method->invoke($test);
            $testResult['passed'] = true;
            $this->results['passedTests']++;
            $this->results['classResults'][$className]['passed']++;
            
        } catch (Exception | Error $e) {
            $testResult['error'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            $this->results['failedTests']++;
            $this->results['classResults'][$className]['failed']++;
        }
        
        $this->results['totalTests']++;
        $this->results['classResults'][$className]['total']++;
        $this->results['classResults'][$className]['tests'][] = $testResult;
    }
    
    private function calculateSuccessRate(): float {
        return $this->results['totalTests'] > 0 
            ? round(($this->results['passedTests'] / $this->results['totalTests']) * 100, 2)
            : 0;
    }
}

$runner = new TestRunner();
$results = $runner->runTests(__DIR__ . '/../tests');
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Risultati Test PHPUnit</title>
    <style>
        <?php include 'tests_styles.css'; ?>
    </style>
</head>
<body>
    <h1>Risultati dei Test</h1>

    <!-- System Info -->
    <div class="system-info">
        <h2>Informazioni di Sistema</h2>
        <div class="info-grid">
            <div class="info-item">
                <strong>Versione PHP:</strong><br><?= $results['systemInfo']['phpVersion'] ?>
            </div>
            <div class="info-item">
                <strong>Versione PHPUnit:</strong><br><?= $results['systemInfo']['phpunitVersion'] ?>
            </div>
            <div class="info-item">
                <strong>Sistema Operativo:</strong><br><?= $results['systemInfo']['os'] ?>
            </div>
            <div class="info-item">
                <strong>Timezone:</strong><br><?= $results['systemInfo']['timezone'] ?>
            </div>
            <div class="info-item">
                <strong>Data e Ora Esecuzione:</strong><br><?= $results['dateTime'] ?>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary">
        <h3>Riepilogo dei Test</h3>
        <div class="stats">
            <div class="stat-box">
                <strong>Test Totali</strong><br><?= $results['totalTests'] ?>
            </div>
            <div class="stat-box">
                <strong>Test Superati</strong><br><?= $results['passedTests'] ?>
            </div>
            <div class="stat-box">
                <strong>Test Falliti</strong><br><?= $results['failedTests'] ?>
            </div>
            <div class="stat-box">
                <strong>Percentuale Successo</strong><br><?= $results['successRate'] ?>%
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <?php foreach ($results['classResults'] as $className => $classResult): ?>
        <div class="test-class">
            <h2>Classe di Test: <?= $className ?></h2>
            
            <?php foreach ($classResult['tests'] as $test): ?>
                <div class="test-method">
                    <div class="method-content <?= $test['passed'] ? 'success' : 'failure' ?>">
                        <span class="test-status"><?= $test['passed'] ? '✓' : '✗' ?></span>
                        <strong><?= $test['name'] ?></strong>
                        
                        <div class="test-details">
                            <?= $test['passed'] ? 'Test completato con successo' : 'Test fallito' ?>
                        </div>
                        
                        <?php if (!$test['passed']): ?>
                            <div class="error-details">
                                Errore: <?= $test['error']['message'] ?><br>
                                File: <?= $test['error']['file'] ?><br>
                                Linea: <?= $test['error']['line'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="test-details">
                Totale test della classe: <?= $classResult['total'] ?> |
                Test superati: <?= $classResult['passed'] ?> |
                Percentuale successo: <?= ($classResult['total'] > 0 ? round(($classResult['passed']/$classResult['total']) * 100, 2) : 0) ?>%
            </div>
        </div>
    <?php endforeach; ?>

    <div class="execution-time">
        Tempo di esecuzione: <?= $results['executionTime'] ?> secondi
    </div>
</body>
</html>