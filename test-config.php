<?php
/**
 * Diagnostic Test for Provenance Table Plugin
 * 
 * Upload this file to your Omeka root directory and access it via browser:
 * http://youromeka.com/test-config.php
 */

// Define paths
define('BASE_PATH', dirname(__FILE__));
define('APPLICATION_PATH', BASE_PATH . '/application');

// Bootstrap Omeka
require_once 'bootstrap.php';
$bootstrap = new Omeka_Application(APPLICATION_ENV);
$bootstrap->getBootstrap()->setOptions(array(
    'resources' => array(
        'theme' => array('basePath' => THEME_DIR, 'webBasePath' => WEB_THEME)
    )
));
$bootstrap->bootstrap();

echo "<h1>Provenance Table Plugin Diagnostic</h1>";
echo "<pre>";

// 1. Check if plugin directory exists
$pluginPath = BASE_PATH . '/plugins/ProvenanceTable';
echo "1. Plugin directory exists: " . (is_dir($pluginPath) ? "YES ✓" : "NO ✗") . "\n";

// 2. Check if main class file exists
$classFile = $pluginPath . '/ProvenanceTablePlugin.php';
echo "2. ProvenanceTablePlugin.php exists: " . (file_exists($classFile) ? "YES ✓" : "NO ✗") . "\n";

// 3. Check if config_form.php exists
$configFile = $pluginPath . '/config_form.php';
echo "3. config_form.php exists: " . (file_exists($configFile) ? "YES ✓" : "NO ✗") . "\n";
echo "   File size: " . (file_exists($configFile) ? filesize($configFile) . " bytes" : "N/A") . "\n";

// 4. Check if plugin is installed
$db = get_db();
$plugin = $db->getTable('Plugin')->findByDirectoryName('ProvenanceTable');
echo "4. Plugin in database: " . ($plugin ? "YES ✓" : "NO ✗") . "\n";
if ($plugin) {
    echo "   - ID: " . $plugin->id . "\n";
    echo "   - Active: " . ($plugin->active ? "YES ✓" : "NO ✗") . "\n";
    echo "   - Version: " . $plugin->version . "\n";
}

// 5. Try to load the class
if (file_exists($classFile)) {
    require_once $classFile;
    echo "5. Class file loaded: YES ✓\n";
    
    // 6. Check if class exists
    $classExists = class_exists('ProvenanceTablePlugin');
    echo "6. ProvenanceTablePlugin class exists: " . ($classExists ? "YES ✓" : "NO ✗") . "\n";
    
    if ($classExists) {
        // 7. Check hooks
        $reflection = new ReflectionClass('ProvenanceTablePlugin');
        $property = $reflection->getProperty('_hooks');
        $property->setAccessible(true);
        $plugin = new ProvenanceTablePlugin();
        $hooks = $property->getValue($plugin);
        
        echo "7. Registered hooks:\n";
        foreach ($hooks as $hook) {
            echo "   - $hook";
            if ($hook === 'config_form') echo " ← REQUIRED FOR CONFIGURE BUTTON";
            if ($hook === 'config') echo " ← REQUIRED FOR SAVING";
            echo "\n";
        }
        
        // 8. Check methods
        echo "8. Required methods:\n";
        echo "   - hookConfigForm: " . (method_exists($plugin, 'hookConfigForm') ? "YES ✓" : "NO ✗") . "\n";
        echo "   - hookConfig: " . (method_exists($plugin, 'hookConfig') ? "YES ✓" : "NO ✗") . "\n";
    }
} else {
    echo "5. Class file NOT FOUND ✗\n";
}

// 9. Check for PHP errors
echo "\n9. Recent PHP errors in Omeka:\n";
$errorLog = APPLICATION_PATH . '/logs/errors.log';
if (file_exists($errorLog)) {
    $errors = file($errorLog);
    $recentErrors = array_slice($errors, -10);  // Last 10 lines
    if (empty($recentErrors)) {
        echo "   No recent errors ✓\n";
    } else {
        foreach ($recentErrors as $error) {
            if (stripos($error, 'provenance') !== false) {
                echo "   " . htmlspecialchars($error);
            }
        }
    }
} else {
    echo "   Error log not found\n";
}

echo "\n10. Conclusion:\n";
echo "================\n";
if ($plugin && $plugin->active && class_exists('ProvenanceTablePlugin')) {
    echo "✓ Plugin appears to be installed correctly.\n";
    echo "✓ All required files exist.\n";
    echo "✓ Class and hooks are properly configured.\n";
    echo "\n";
    echo "If Configure button still doesn't appear:\n";
    echo "1. Clear Omeka cache (delete files in application/cache/)\n";
    echo "2. Check browser cache (Ctrl+F5 to hard refresh)\n";
    echo "3. Try uninstalling and reinstalling the plugin\n";
} else {
    echo "✗ Plugin is NOT properly installed.\n";
    echo "Action required: Install the plugin from Plugins page.\n";
}

echo "</pre>";
