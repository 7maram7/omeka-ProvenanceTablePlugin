<?php
/**
 * Provenance Table Plugin
 *
 * @copyright Copyright 2025
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Provenance Table plugin class.
 *
 * @package Omeka\Plugins\ProvenanceTable
 */
class ProvenanceTablePlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Plugin hooks
     */
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'config_form',
        'config',
        'before_save_item',
        'after_save_item',
        'admin_head',
        'public_head',
        'public_items_show',
    );

    /**
     * @var array Plugin filters
     */
    protected $_filters = array(
        'admin_items_form_tabs',
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $db = $this->_db;

        // Create provenance table
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}provenance_data` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `row_order` int(10) unsigned NOT NULL DEFAULT '0',
            `col1` text COLLATE utf8_unicode_ci,
            `col2` text COLLATE utf8_unicode_ci,
            `col3` text COLLATE utf8_unicode_ci,
            `col4` text COLLATE utf8_unicode_ci,
            PRIMARY KEY (`id`),
            KEY `item_id` (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $db->query($sql);

        // Set default configuration options
        set_option('provenance_tab_name', 'Provenance');
        set_option('provenance_num_columns', '4');
        set_option('provenance_col1_name', 'No.');
        set_option('provenance_col2_name', 'Auction/Collection');
        set_option('provenance_col3_name', 'Date');
        set_option('provenance_col4_name', 'Characteristics');
        set_option('provenance_enabled_item_types', 'all'); // 'all' or serialized array of IDs
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drop provenance table
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}provenance_data`";
        $db->query($sql);

        // Delete plugin options
        delete_option('provenance_tab_name');
        delete_option('provenance_num_columns');
        delete_option('provenance_col1_name');
        delete_option('provenance_col2_name');
        delete_option('provenance_col3_name');
        delete_option('provenance_col4_name');
        delete_option('provenance_enabled_item_types');
    }

    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];

        // Handle upgrades from old version (field transformation) to new version (tabs)
        if (version_compare($oldVersion, '3.0.0', '<')) {
            // Remove old options
            delete_option('provenance_table_mappings');
        }
    }

    /**
     * Display the plugin configuration form.
     */
    public function hookConfigForm()
    {
        include 'config_form.php';
    }

    /**
     * Handle the plugin configuration form submission.
     */
    public function hookConfig($args)
    {
        $post = $args['post'];

        // Save tab name
        if (isset($post['provenance_tab_name'])) {
            set_option('provenance_tab_name', trim($post['provenance_tab_name']));
        }

        // Save number of columns (2-4)
        if (isset($post['provenance_num_columns'])) {
            $numCols = (int)$post['provenance_num_columns'];
            if ($numCols < 2) $numCols = 2;
            if ($numCols > 4) $numCols = 4;
            set_option('provenance_num_columns', $numCols);
        }

        // Save column names
        for ($i = 1; $i <= 4; $i++) {
            if (isset($post['provenance_col' . $i . '_name'])) {
                set_option('provenance_col' . $i . '_name', trim($post['provenance_col' . $i . '_name']));
            }
        }

        // Save enabled item types
        if (isset($post['provenance_enable_mode']) && $post['provenance_enable_mode'] === 'specific') {
            // Save specific item types if any are selected
            if (isset($post['provenance_enabled_item_types']) && is_array($post['provenance_enabled_item_types'])) {
                set_option('provenance_enabled_item_types', serialize($post['provenance_enabled_item_types']));
            } else {
                // No types selected, default to all
                set_option('provenance_enabled_item_types', 'all');
            }
        } else {
            // Mode is 'all' or not set
            set_option('provenance_enabled_item_types', 'all');
        }
    }

    /**
     * Add the Provenance tab to the admin items form.
     * This is a FILTER, not a hook.
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];

        // Check if enabled for this item type
        if (!$this->_isEnabledForItem($item)) {
            return $tabs;
        }

        $tabName = get_option('provenance_tab_name') ?: 'Provenance';

        // Get existing provenance data for this item
        $provenanceData = $this->_getProvenanceData($item);

        // Get column configuration
        $numColumns = (int)get_option('provenance_num_columns') ?: 4;
        $columnNames = array();
        for ($i = 1; $i <= $numColumns; $i++) {
            $columnNames[$i] = get_option('provenance_col' . $i . '_name') ?: 'Column ' . $i;
        }

        // Generate the HTML content for the tab
        ob_start();
        include 'views/admin/items/provenance-panel.php';
        $tabContent = ob_get_clean();

        // Add the tab to the tabs array
        $tabs[$tabName] = $tabContent;

        return $tabs;
    }

    /**
     * Store provenance data before item is saved.
     */
    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        $post = $args['post'];

        // Check if provenance data was submitted
        if (!isset($post['provenance_data'])) {
            return;
        }

        // Store in request registry to save after item is saved
        Zend_Registry::set('provenance_data_to_save', array(
            'item_id' => $item->id,
            'data' => $post['provenance_data']
        ));
    }

    /**
     * Save provenance data after item is saved.
     * This runs after the item has an ID.
     */
    public function hookAfterSaveItem($args)
    {
        $item = $args['record'];

        // Check if we have provenance data to save
        if (Zend_Registry::isRegistered('provenance_data_to_save')) {
            $saveData = Zend_Registry::get('provenance_data_to_save');

            if ($item->id) {
                self::saveProvenanceData($item->id, $saveData['data']);
            }

            // Clear the registry
            Zend_Registry::set('provenance_data_to_save', null);
        }
    }

    /**
     * Add CSS and JavaScript to admin head.
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // Only load on items add/edit pages
        if ($controller == 'items' && ($action == 'add' || $action == 'edit' || $action == 'show')) {
            queue_css_file('provenance-table');
            queue_js_file('provenance-table');
        }
    }

    /**
     * Add CSS to public head.
     */
    public function hookPublicHead($args)
    {
        queue_css_file('provenance-display');
    }

    /**
     * Display provenance table on public items show page.
     */
    public function hookPublicItemsShow($args)
    {
        $item = $args['item'];

        // Check if enabled for this item type
        if (!$this->_isEnabledForItem($item)) {
            return;
        }

        // Get provenance data
        $provenanceData = $this->_getProvenanceData($item);

        if (empty($provenanceData)) {
            return;
        }

        // Get column configuration
        $numColumns = (int)get_option('provenance_num_columns') ?: 4;
        $columnNames = array();
        for ($i = 1; $i <= $numColumns; $i++) {
            $columnNames[$i] = get_option('provenance_col' . $i . '_name') ?: 'Column ' . $i;
        }

        $tabName = get_option('provenance_tab_name') ?: 'Provenance';

        // Display the table
        include 'views/public/items/provenance-display.php';
    }

    /**
     * Check if provenance is enabled for this item's type.
     */
    protected function _isEnabledForItem($item)
    {
        $enabledSetting = get_option('provenance_enabled_item_types');

        if ($enabledSetting === 'all') {
            return true;
        }

        // Check if item has a type and if it's in the enabled list
        if ($item && $item->item_type_id) {
            $enabledTypes = @unserialize($enabledSetting);
            if (is_array($enabledTypes)) {
                return in_array($item->item_type_id, $enabledTypes);
            }
        }

        return true; // Default to enabled
    }

    /**
     * Get provenance data for an item.
     */
    protected function _getProvenanceData($item)
    {
        if (!$item || !$item->id) {
            return array();
        }

        $db = $this->_db;
        $sql = "SELECT * FROM {$db->prefix}provenance_data
                WHERE item_id = ?
                ORDER BY row_order ASC";

        return $db->fetchAll($sql, array($item->id));
    }

    /**
     * Save provenance data for an item.
     */
    public static function saveProvenanceData($itemId, $data)
    {
        $db = get_db();

        // Delete existing data
        $db->query("DELETE FROM {$db->prefix}provenance_data WHERE item_id = ?", array($itemId));

        // Insert new data
        if (is_array($data) && !empty($data)) {
            $order = 0;
            foreach ($data as $row) {
                // Check if row has any data
                $hasData = false;
                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($row['col' . $i])) {
                        $hasData = true;
                        break;
                    }
                }

                if ($hasData) {
                    $db->insert('provenance_data', array(
                        'item_id' => $itemId,
                        'row_order' => $order++,
                        'col1' => isset($row['col1']) ? $row['col1'] : '',
                        'col2' => isset($row['col2']) ? $row['col2'] : '',
                        'col3' => isset($row['col3']) ? $row['col3'] : '',
                        'col4' => isset($row['col4']) ? $row['col4'] : '',
                    ));
                }
            }
        }
    }
}
