<?php
/**
 * Provenance Table Plugin Bootstrap
 *
 * @copyright Copyright 2025
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

// Define plugin directory
define('PROVENANCE_TABLE_PLUGIN_DIR', dirname(__FILE__));

// Include the plugin class
require_once PROVENANCE_TABLE_PLUGIN_DIR . '/ProvenanceTablePlugin.php';

// Note: Omeka automatically instantiates the plugin class, so we don't need to do it here

// Hook to save provenance data when an item is saved
add_filter('before_save_item', 'provenance_table_before_save_item');

/**
 * Process provenance data before saving an item.
 *
 * @param array $args
 * @return void
 */
function provenance_table_before_save_item($args)
{
    $item = $args['record'];
    $post = $args['post'];

    // Check if provenance data is present in the POST
    if (isset($post['provenance']) && is_array($post['provenance'])) {
        // Store provenance data in a temporary property to process after save
        $item->_provenanceData = $post['provenance'];
    }
}

// Hook to save provenance data after an item is saved
add_filter('after_save_item', 'provenance_table_after_save_item');

/**
 * Save provenance data after an item is saved.
 *
 * @param array $args
 * @return void
 */
function provenance_table_after_save_item($args)
{
    $item = $args['record'];

    // Check if provenance data was stored
    if (isset($item->_provenanceData)) {
        ProvenanceTablePlugin::saveProvenanceEntries($item, $item->_provenanceData);
        unset($item->_provenanceData);
    }
}
