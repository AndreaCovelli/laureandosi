<?php
session_start();
session_unset();  // Clear all session variables on page load
const WP_USE_THEMES = true;
require_once('src/Classes/ProspettoPDFCommissione.php');
require_once('src/Classes/GestioneInvioEmail.php');

$output_dir = __DIR__ . '/output';
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0777, true); // mode is ignored on Windows
}

$form_values = [
    'matricole' => '',
    'cdl' => '',
    'date' => ''
];

if (isset($_GET['create']) || isset($_GET['send'])) {
    $form_values = [
        'matricole' => $_GET['matricole'],
        'cdl' => $_GET['cdl'],
        'date' => $_GET['date']
    ];

    $matricole_string = $_GET['matricole'];
    $matricole_array = array_map('trim', explode(',', $matricole_string));
    $cdl = $_GET['cdl'];
    $data_laurea = $_GET['date'];

    if (!empty($matricole_array) && $cdl != "Seleziona un CdL" && !empty($data_laurea)) {
        $pdf = new FPDF();
        $prospetto = new ProspettoPDFCommissione($pdf, $matricole_array, $cdl, $data_laurea);
        
        if (isset($_GET['create'])) {
            $prospetto->generaProspetto();
            $safe_cdl = str_replace(' ', '_', $cdl);
            $safe_date = str_replace('-', '_', $data_laurea);
            $_SESSION['prospetti_generati'] = true;
            $_SESSION['output_path'] = '/output/' . $safe_cdl . '/' . $safe_date;
            $_SESSION['create_success'] = true; // Aggiungi questa linea
        } elseif (isset($_GET['send'])) {
            $success = $prospetto->inviaProspettiLaureandi();
            $_SESSION['email_sent'] = $success;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Prospetti di Laurea</title>
    <link rel="stylesheet" href="src/styles.css">
</head>
<body>
<div class="container">
    <h1>Gestione Prospetti di Laurea</h1>
    <form class="form" method="get">
        <div class="left-column">
            <label for="cdl">CdL:</label>
            <select id="cdl" name="cdl">
                <option value="">Seleziona un CdL</option>
                <option value="T. Ing. Informatica">T. Ing. Informatica</option>
                <option value="M. Ing. Elettronica">M. Ing. Elettronica</option>
                <option value="M. Ing. delle Telecomunicazioni">M. Ing. delle Telecomunicazioni</option>
                <option value="M. Cybersecurity">M. Cybersecurity</option>
            </select>
            <label for="date">Data Laurea:</label>
            <input type="date" id="date" name="date">
        </div>
        <div class="middle-column">
            <label for="matricole">Matricole:</label>
            <textarea id="matricole" name="matricole"></textarea>
        </div>
        <div class="right-column">
            <label></label>
            <button class="btn" type="submit" name="create">Crea Prospetti</button>
            <button class="btn-link" type="submit" name="open" <?php echo !isset($_SESSION['prospetti_generati']) ? 'disabled' : ''; ?>>
                apri prospetti
            </button>
            <button class="btn" type="submit" name="send">Invia Prospetti</button>
            <?php if (isset($_SESSION['create_success'])): ?>
                <div class="alert success">
                    Prospetti creati con successo!
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['email_sent'])): ?>
                <div class="alert <?php echo $_SESSION['email_sent'] ? 'success' : 'error'; ?>">
                    <?php echo $_SESSION['email_sent'] ? 'Email inviate con successo!' : 'Errore nell\'invio delle email'; ?>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['prospetti_generati']) && $_SESSION['prospetti_generati']): ?>
    const openBtn = document.querySelector('button[name="open"]');
    const outputPath = '<?php echo $_SESSION['output_path']; ?>';
    openBtn.addEventListener('click', function(e) {
        e.preventDefault();
        // Apri il file PDF nel browser
        window.open(outputPath + '/prospetto_commissione.pdf', '_blank');
        // Apri la cartella output nel browser
        window.open(outputPath, '_blank');
    });
    <?php endif; ?>

    // Add event listeners to clear message when selection changes
    document.getElementById('cdl').addEventListener('change', function() {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) alertDiv.style.display = 'none';
    });

    document.getElementById('date').addEventListener('change', function() {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) alertDiv.style.display = 'none';
    });

    document.getElementById('matricole').addEventListener('input', function() {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) alertDiv.style.display = 'none';
    });

    // Pre-fill form values
    document.getElementById('matricole').value = '<?php echo htmlspecialchars($form_values['matricole']); ?>';
    document.getElementById('cdl').value = '<?php echo htmlspecialchars($form_values['cdl']); ?>';
    document.getElementById('date').value = '<?php echo htmlspecialchars($form_values['date']); ?>';
});
</script>

</body>
</html>
<!-- 
    nota che per far funzionare window.open('/output/', '_blank');
    è stato aggiunto
    # Allow directory listing for output folder
        location ^~ /output/ {
            autoindex on;
            autoindex_exact_size off;
            autoindex_localtime on;
        }
    in site.conf.hbs di nginx del local site
    prima di
     #
     # PHP-FPM
     #
-->