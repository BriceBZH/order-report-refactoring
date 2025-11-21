<?php

// $outputLegacy = '';
// $outputRefact = '';

function runLegacy() {
    exec('php ../legacy/orderReportLegacy.php', $lines);
    $outputLegacy = implode("\n", $lines);
    file_put_contents('../legacy/expected/report.txt', $outputLegacy);

    return $outputLegacy;
}

function runRefact() {
    exec('php ../src/orderReportLegacy.php', $lines);
    $outputRefact = implode("\n", $lines);

    return $outputRefact;
}

function comparedOutputs($outputLegacy, $outputRefact) {
    if($outputLegacy === $outputRefact) {
        echo "Test Golden Master Ok, outputs are sames";
    } else {
        echo "Outputs aren't sames";
    }
}

// Point d'entrée
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $outputLegacy = runLegacy();
    $outputRefact = runRefact();
    comparedOutputs($outputLegacy, $outputRefact);
}
