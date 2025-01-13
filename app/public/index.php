<?php
session_start(); // Inizializza la sessione
session_unset();  // Cancella tutte le variabili di sessione
const WP_USE_THEMES = true;

define('OUTPUT_PATH', dirname(__DIR__) . '/public/output');

require_once(dirname(__DIR__) . '/src/classes/ProspettoPDFCommissione.php');
require_once(dirname(__DIR__) . '/src/classes/GestioneInvioEmail.php');

$output_dir = __DIR__ . '/output';
if (!file_exists(OUTPUT_PATH)) {
    mkdir(OUTPUT_PATH, 0777, true); // mode 0777 viene ignorata su Windows
}

$form_values = [
    'matricole' => '',
    'cdl' => '',
    'date' => ''
];

// Controlla se il form è stato inviato
if (isset($_POST['create']) || isset($_POST['send'])) {
    $form_values = [
        'matricole' => $_POST['matricole'],
        'cdl' => $_POST['cdl'],
        'date' => $_POST['date']
    ];

    $matricole_string = $_POST['matricole'];
    $matricole_array = array_map('trim', explode(',', $matricole_string));
    $cdl = $_POST['cdl'];
    $data_laurea = $_POST['date'];
    // Controlla che i campi siano stati compilati
    if (!empty($matricole_array) && $cdl != "Seleziona un CdL" && !empty($data_laurea)) {
        $pdf = new FPDF();
        $prospetto = new ProspettoPDFCommissione($pdf, $matricole_array, $cdl, $data_laurea);
        
        if (isset($_POST['create'])) {
            try {
                $prospetto->generaProspetto();
                $success = true;
                $safe_cdl = str_replace(' ', '_', $cdl);
                $safe_date = str_replace('-', '_', $data_laurea);
                $_SESSION['prospetti_generati'] = $success;
                $anno_accademico = $prospetto->calcolaAnnoAccademico();
                $_SESSION['output_path'] =  '/output/' . $safe_cdl . '/' . $anno_accademico . '/' . $safe_date;
                $_SESSION['create_success'] = true;
            } catch (Exception $e) {
                $_SESSION['create_success'] = false;
            }
        } elseif (isset($_POST['send'])) {
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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>Gestione Prospetti di Laurea</h1>
    <form class="form" method="post">
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
            <button id="openProspetti" class="btn-link" onclick="<?php echo !isset($_SESSION['prospetti_generati']) ? 'disabled' : ''; ?>">
                apri prospetti
            </button>
            <button class="btn" type="submit" name="send">Invia Prospetti</button>
            <?php if (isset($_SESSION['create_success'])): ?>
                <div class="alert <?php echo $_SESSION['create_success'] ? 'success' : 'error'; ?>">
                    <?php echo $_SESSION['create_success'] ? 'Prospetti creati con successo!' : 'Errore nella creazione dei prospetti'; ?>
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
    // Gestiamo l'apertura dei prospetti
    const openBtn = document.getElementById('openProspetti');

    <?php if (isset($_SESSION['prospetti_generati']) && $_SESSION['prospetti_generati']): ?>
        const outputPath = '<?php echo $_SESSION['output_path']; ?>';

        openBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // Apri il prospetto della commissione in una nuova scheda
            window.open(outputPath + '/prospetto_commissione.pdf', '_blank');
            // Apri la cartella con i tutti i prospetti generati
            window.open(outputPath, '_blank');
        });
    <?php endif; ?>

    const clearAlert = () => {
        const alertDiv = document.querySelector('.alert');
        if (alertDiv) alertDiv.style.display = 'none';
    };

    // Aggiungi event listener to clear message when CdL changes
    document.getElementById('cdl').addEventListener('change', clearAlert);
    // Aggiungi event listener to clear message when date changes
    document.getElementById('date').addEventListener('change', clearAlert);
    // Aggiungi event listener to clear message when matricole changes
    document.getElementById('matricole').addEventListener('input', clearAlert);

    // Pre-fill form values
    document.getElementById('matricole').value = '<?php echo htmlspecialchars($form_values['matricole']); ?>';
    document.getElementById('cdl').value = '<?php echo htmlspecialchars($form_values['cdl']); ?>';
    document.getElementById('date').value = '<?php echo htmlspecialchars($form_values['date']); ?>';
});
</script>

</body>
</html>
<!-- 
    Nota che per far funzionare window.open('/output/', '_blank');
    è stato aggiunto:

    # Allow directory listing for output folder
        location ^~ /output/ {
            autoindex on;
            autoindex_exact_size off;
            autoindex_localtime on;
        }

    in site.conf.hbs di nginx del local site.
    Prima di:

     #
     # PHP-FPM
     #
-->