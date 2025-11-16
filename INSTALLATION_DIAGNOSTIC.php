<?php
/**
 * INSTALLATION DIAGNOSTIC FOR PROVENANCE TABLE PLUGIN
 *
 * Upload this file to: /plugins/ProvenanceTable/INSTALLATION_DIAGNOSTIC.php
 * Access via: http://yoursite.com/plugins/ProvenanceTable/INSTALLATION_DIAGNOSTIC.php
 */

$pluginDir = dirname(__FILE__);
$errors = array();
$warnings = array();
$success = array();

echo "<h1>Provenance Table Plugin - Installation Diagnostic</h1>";
echo "<p><strong>Plugin Directory:</strong> $pluginDir</p><hr>";

// Check 1: Core files exist
echo "<h2>1. Core Files</h2>";
$coreFiles = array(
    'ProvenanceTablePlugin.php',
    'plugin.ini',
    'config_form.php',
    'views/admin/items/provenance-panel.php',
    'views/admin/items/provenance-show.php',
    'views/admin/css/provenance-table.css',
    'views/admin/javascripts/provenance-table.js',
    'views/public/css/provenance-display.css',
    'views/public/items/provenance-display.php'
);

foreach ($coreFiles as $file) {
    $path = $pluginDir . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p style='color:green'>✓ <strong>$file</strong> - exists ($size bytes)</p>";
        $success[] = $file;
    } else {
        echo "<p style='color:red'>✗ <strong>$file</strong> - MISSING!</p>";
        $errors[] = "$file is missing";
    }
}

// Check 2: Files that should NOT exist
echo "<h2>2. Conflicting Files (should NOT exist)</h2>";
$badFiles = array('plugin.php', 'test-config.php');
$hasConflicts = false;
foreach ($badFiles as $file) {
    if (file_exists($pluginDir . '/' . $file)) {
        echo "<p style='color:red'>✗ <strong>$file</strong> - EXISTS (should be deleted!)</p>";
        $errors[] = "$file exists but should be deleted";
        $hasConflicts = true;
    }
}
if (!$hasConflicts) {
    echo "<p style='color:green'>✓ No conflicting files found</p>";
}

// Check 3: File contents - check for correct form field naming
echo "<h2>3. Form Field Naming Check</h2>";
$panelFile = $pluginDir . '/views/admin/items/provenance-panel.php';
if (file_exists($panelFile)) {
    $content = file_get_contents($panelFile);

    // Check for NEW correct naming
    if (strpos($content, 'provenance_data[<?php echo $rowIndex; ?>][col') !== false) {
        echo "<p style='color:green'>✓ provenance-panel.php has CORRECT form field naming (with rowIndex)</p>";
        $success[] = "Correct form naming";
    }
    // Check for OLD incorrect naming
    else if (strpos($content, 'provenance_data[][col') !== false) {
        echo "<p style='color:red'>✗ provenance-panel.php has OLD INCORRECT form field naming!</p>";
        echo "<p style='color:red'><strong>This is why your data splits across rows!</strong></p>";
        echo "<p style='color:red'><strong>ACTION: Download and upload the latest provenance-panel.php file</strong></p>";
        $errors[] = "Old form naming in provenance-panel.php";
    } else {
        echo "<p style='color:orange'>⚠ Cannot detect form naming pattern</p>";
        $warnings[] = "Unknown form naming pattern";
    }
} else {
    echo "<p style='color:red'>✗ Cannot check - file missing</p>";
}

// Check 4: PHP syntax
echo "<h2>4. PHP Syntax Check</h2>";
foreach (array('ProvenanceTablePlugin.php', 'config_form.php') as $file) {
    $path = $pluginDir . '/' . $file;
    if (file_exists($path)) {
        $output = array();
        $return_var = 0;
        exec("php -l \"$path\" 2>&1", $output, $return_var);
        if ($return_var === 0) {
            echo "<p style='color:green'>✓ $file - no syntax errors</p>";
        } else {
            echo "<p style='color:red'>✗ $file - syntax errors found:</p>";
            echo "<pre>" . implode("\n", $output) . "</pre>";
            $errors[] = "Syntax error in $file";
        }
    }
}

// Check 5: File permissions
echo "<h2>5. File Permissions</h2>";
$checkPerm = array(
    'ProvenanceTablePlugin.php',
    'views/admin/items/provenance-panel.php',
    'views/admin/javascripts/provenance-table.js'
);
foreach ($checkPerm as $file) {
    $path = $pluginDir . '/' . $file;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        if (is_readable($path)) {
            echo "<p style='color:green'>✓ $file - readable (permissions: $perms)</p>";
        } else {
            echo "<p style='color:red'>✗ $file - NOT readable (permissions: $perms)</p>";
            $errors[] = "$file is not readable";
        }
    }
}

// Summary
echo "<hr><h2>Summary</h2>";
echo "<p><strong>Successes:</strong> " . count($success) . "</p>";
echo "<p><strong>Warnings:</strong> " . count($warnings) . "</p>";
echo "<p><strong>Errors:</strong> " . count($errors) . "</p>";

if (count($errors) > 0) {
    echo "<div style='background:#ffe6e6; padding:15px; border-left:4px solid #cc0000;'>";
    echo "<h3 style='color:#cc0000'>Action Required:</h3>";
    echo "<ol>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ol>";
    echo "</div>";
}

if (count($errors) === 0 && count($warnings) === 0) {
    echo "<div style='background:#e6ffe6; padding:15px; border-left:4px solid #00cc00;'>";
    echo "<h3 style='color:#00cc00'>✓ Installation looks good!</h3>";
    echo "<p>If you're still having issues, check your PHP error log or browser console.</p>";
    echo "</div>";
}

echo "<hr><p><small>After resolving any issues, you can delete this diagnostic file.</small></p>";
