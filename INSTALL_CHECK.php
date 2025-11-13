<?php
/**
 * Installation Check for Provenance Table Plugin
 *
 * Upload this file to your Omeka plugins folder and access it via:
 * http://yoursite.com/plugins/FOLDERNAME/INSTALL_CHECK.php
 *
 * This will tell you exactly what's wrong with your installation.
 */

echo "<h1>Provenance Table Plugin - Installation Check</h1>";

// Check 1: Folder name
$currentFolder = basename(dirname(__FILE__));
echo "<h2>1. Folder Name Check</h2>";
echo "<p>Current folder name: <strong>$currentFolder</strong></p>";

if ($currentFolder === 'ProvenanceTable') {
    echo "<p style='color: green;'>✓ CORRECT: Folder name is 'ProvenanceTable'</p>";
} else {
    echo "<p style='color: red;'>✗ ERROR: Folder name MUST be exactly 'ProvenanceTable' (capital P, capital T, no hyphens)</p>";
    echo "<p style='color: red;'>Your folder is named: '$currentFolder'</p>";
    echo "<p style='color: red;'><strong>ACTION REQUIRED: Rename this folder to 'ProvenanceTable'</strong></p>";
}

// Check 2: Required files
echo "<h2>2. Required Files Check</h2>";
$requiredFiles = array(
    'ProvenanceTablePlugin.php',
    'plugin.ini',
    'config_form.php'
);

$allFilesExist = true;
foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ Found: $file</p>";
    } else {
        echo "<p style='color: red;'>✗ MISSING: $file</p>";
        $allFilesExist = false;
    }
}

// Check 3: Files that should NOT exist
echo "<h2>3. Conflicting Files Check</h2>";
$forbiddenFiles = array('plugin.php', 'test-config.php');

$noConflicts = true;
foreach ($forbiddenFiles as $file) {
    if (file_exists($file)) {
        echo "<p style='color: red;'>✗ PROBLEM: $file exists and should be deleted!</p>";
        $noConflicts = false;
    } else {
        echo "<p style='color: green;'>✓ Good: $file does not exist</p>";
    }
}

// Check 4: PHP syntax
echo "<h2>4. PHP Syntax Check</h2>";
if (file_exists('ProvenanceTablePlugin.php')) {
    $output = array();
    $return_var = 0;
    exec('php -l ProvenanceTablePlugin.php 2>&1', $output, $return_var);

    if ($return_var === 0) {
        echo "<p style='color: green;'>✓ No PHP syntax errors in ProvenanceTablePlugin.php</p>";
    } else {
        echo "<p style='color: red;'>✗ PHP syntax errors found:</p>";
        echo "<pre>" . implode("\n", $output) . "</pre>";
    }
}

// Check 5: Class definition
echo "<h2>5. Class Definition Check</h2>";
if (file_exists('ProvenanceTablePlugin.php')) {
    $content = file_get_contents('ProvenanceTablePlugin.php');

    if (strpos($content, 'class ProvenanceTablePlugin extends Omeka_Plugin_AbstractPlugin') !== false) {
        echo "<p style='color: green;'>✓ Class definition is correct</p>";
    } else {
        echo "<p style='color: red;'>✗ Class definition not found or incorrect</p>";
    }

    if (strpos($content, 'config_form') !== false && strpos($content, 'config') !== false) {
        echo "<p style='color: green;'>✓ config_form and config hooks are registered</p>";
    } else {
        echo "<p style='color: red;'>✗ config_form or config hooks missing</p>";
    }
}

// Summary
echo "<h2>Summary</h2>";
if ($currentFolder === 'ProvenanceTable' && $allFilesExist && $noConflicts) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ Installation looks correct!</p>";
    echo "<p>If you still don't see the plugin:</p>";
    echo "<ol>";
    echo "<li>Make sure you're in the /plugins/ folder (not /themes/ or anywhere else)</li>";
    echo "<li>Try refreshing the Plugins page in Omeka admin</li>";
    echo "<li>Check your PHP error log for any errors</li>";
    echo "<li>Make sure your Omeka version is 2.0 or higher</li>";
    echo "</ol>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>✗ Installation has problems - fix the errors above!</p>";
}

echo "<hr>";
echo "<p><small>After you've checked everything, delete this INSTALL_CHECK.php file.</small></p>";
