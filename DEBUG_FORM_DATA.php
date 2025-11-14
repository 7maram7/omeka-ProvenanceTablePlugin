<?php
/**
 * DEBUG: Form Data Inspector
 *
 * Add this to ProvenanceTablePlugin.php temporarily in the hookAfterSaveItem method
 * to see what data is being submitted from the form:
 *
 * In hookAfterSaveItem, add at the top:
 *
 * $saveData = Zend_Registry::get('provenance_data_to_save');
 * file_put_contents(
 *     dirname(__FILE__) . '/DEBUG_OUTPUT.txt',
 *     "=== DEBUG OUTPUT ===\n" .
 *     "Time: " . date('Y-m-d H:i:s') . "\n\n" .
 *     "POST Data:\n" . print_r($_POST, true) . "\n\n" .
 *     "Provenance Data to Save:\n" . print_r($saveData, true) . "\n\n",
 *     FILE_APPEND
 * );
 *
 * Then after you save an item, check the DEBUG_OUTPUT.txt file to see
 * exactly what data is being submitted.
 */

echo "This is a debug instruction file. See contents for instructions.";
