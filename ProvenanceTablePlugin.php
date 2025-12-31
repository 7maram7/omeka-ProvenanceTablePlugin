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
        'admin_items_show',
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

        // Create provenance tables metadata table
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}provenance_tables` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `table_order` int(10) unsigned NOT NULL DEFAULT '0',
            `notes` text COLLATE utf8_unicode_ci,
            PRIMARY KEY (`id`),
            KEY `item_id` (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $db->query($sql);

        // Create provenance data table
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}provenance_data` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `table_id` int(10) unsigned NOT NULL,
            `item_id` int(10) unsigned NOT NULL,
            `row_order` int(10) unsigned NOT NULL DEFAULT '0',
            `col1` text COLLATE utf8_unicode_ci,
            `col2` text COLLATE utf8_unicode_ci,
            `col3` text COLLATE utf8_unicode_ci,
            PRIMARY KEY (`id`),
            KEY `item_id` (`item_id`),
            KEY `table_id` (`table_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";
        $db->query($sql);

        // Set default configuration options
        set_option('provenance_tab_name', 'Specimens');
        set_option('provenance_num_columns', '3');
        set_option('provenance_col1_name', 'No.');
        set_option('provenance_col2_name', 'Auction/Collection');
        set_option('provenance_col3_name', 'Characteristics');
        set_option('provenance_col1_width', '5');
        set_option('provenance_col2_width', '30');
        set_option('provenance_col3_width', '65');
        set_option('provenance_enabled_item_types', 'all'); // 'all' or serialized array of IDs
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drop provenance tables
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}provenance_data`";
        $db->query($sql);
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}provenance_tables`";
        $db->query($sql);

        // Delete plugin options
        delete_option('provenance_tab_name');
        delete_option('provenance_num_columns');
        delete_option('provenance_col1_name');
        delete_option('provenance_col2_name');
        delete_option('provenance_col3_name');
        delete_option('provenance_col1_width');
        delete_option('provenance_col2_width');
        delete_option('provenance_col3_width');
        delete_option('provenance_enabled_item_types');
    }

    /**
     * Upgrade the plugin.
     */
    public function hookUpgrade($args)
    {
        $oldVersion = $args['old_version'];
        $newVersion = $args['new_version'];
        $db = $this->_db;

        // Handle upgrades from old version (field transformation) to new version (tabs)
        if (version_compare($oldVersion, '3.0.0', '<')) {
            // Remove old options
            delete_option('provenance_table_mappings');
        }

        // Upgrade to version 4.0.0 - Add support for multiple tables
        if (version_compare($oldVersion, '4.0.0', '<')) {
            // Create provenance_tables table
            $sql = "
            CREATE TABLE IF NOT EXISTS `{$db->prefix}provenance_tables` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `item_id` int(10) unsigned NOT NULL,
                `table_order` int(10) unsigned NOT NULL DEFAULT '0',
                `notes` text COLLATE utf8_unicode_ci,
                PRIMARY KEY (`id`),
                KEY `item_id` (`item_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ";
            $db->query($sql);

            // Check if table_id column already exists
            $columns = $db->fetchAll("SHOW COLUMNS FROM `{$db->prefix}provenance_data` LIKE 'table_id'");

            if (empty($columns)) {
                // Add table_id column to provenance_data
                $sql = "ALTER TABLE `{$db->prefix}provenance_data`
                        ADD `table_id` int(10) unsigned NOT NULL DEFAULT 0 AFTER `id`,
                        ADD KEY `table_id` (`table_id`)";
                $db->query($sql);

                // Migrate existing data: create a default table for each item with provenance data
                $sql = "SELECT DISTINCT item_id FROM `{$db->prefix}provenance_data`";
                $items = $db->fetchAll($sql);

                foreach ($items as $item) {
                    $itemId = $item['item_id'];

                    // Create a default table for this item
                    $sql = "INSERT INTO `{$db->prefix}provenance_tables` (item_id, table_order, notes)
                            VALUES (?, 0, '')";
                    $db->query($sql, array($itemId));
                    $tableId = $db->lastInsertId();

                    // Link all existing rows to this table
                    $sql = "UPDATE `{$db->prefix}provenance_data`
                            SET table_id = ?
                            WHERE item_id = ?";
                    $db->query($sql, array($tableId, $itemId));
                }
            }
        }

        // Upgrade to version 5.0.0 - Convert from 4 columns to 3 columns
        if (version_compare($oldVersion, '5.0.0', '<')) {
            // Check if col4 exists
            $columns = $db->fetchAll("SHOW COLUMNS FROM `{$db->prefix}provenance_data` LIKE 'col4'");

            if (!empty($columns)) {
                // Migrate col4 data to col3
                $sql = "UPDATE `{$db->prefix}provenance_data` SET col3 = col4";
                $db->query($sql);

                // Drop col4 column
                $sql = "ALTER TABLE `{$db->prefix}provenance_data` DROP COLUMN `col4`";
                $db->query($sql);
            }

            // Update configuration options
            set_option('provenance_num_columns', '3');
            set_option('provenance_col3_name', 'Characteristics');
            set_option('provenance_col3_width', '65');

            // Remove old options
            delete_option('provenance_col4_name');
            delete_option('provenance_col4_width');
            delete_option('provenance_col1_enabled');
            delete_option('provenance_col2_enabled');
            delete_option('provenance_col3_enabled');
            delete_option('provenance_col4_enabled');
        }
    }

    /**
     * Display the plugin configuration form.
     */
    public function hookConfigForm()
    {
        include dirname(__FILE__) . '/config_form.php';
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

        // Save column names and widths
        for ($i = 1; $i <= 3; $i++) {
            if (isset($post['provenance_col' . $i . '_name'])) {
                set_option('provenance_col' . $i . '_name', trim($post['provenance_col' . $i . '_name']));
            }
            if (isset($post['provenance_col' . $i . '_width'])) {
                $width = (int)$post['provenance_col' . $i . '_width'];
                if ($width < 1) $width = 5;
                if ($width > 100) $width = 100;
                set_option('provenance_col' . $i . '_width', $width);
            }
        }

        // Save enabled item types
        if (isset($post['provenance_enable_mode']) && $post['provenance_enable_mode'] === 'specific') {
            if (isset($post['provenance_enabled_item_types']) && is_array($post['provenance_enabled_item_types'])) {
                set_option('provenance_enabled_item_types', serialize($post['provenance_enabled_item_types']));
            } else {
                set_option('provenance_enabled_item_types', 'all');
            }
        } else {
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
        $numColumns = 3;
        $columnNames = array();
        $columnWidths = array();
        for ($i = 1; $i <= 3; $i++) {
            $columnNames[$i] = get_option('provenance_col' . $i . '_name') ?: 'Column ' . $i;
            $columnWidths[$i] = (int)get_option('provenance_col' . $i . '_width') ?: (($i == 1) ? 5 : 30);
        }

        // Generate the HTML content for the tab
        ob_start();
        include dirname(__FILE__) . '/views/admin/items/provenance-panel.php';
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
        if (!isset($post['provenance_tables'])) {
            return;
        }

        // Create cleaned structure (for debug + to save)
        $cleanTables = $post['provenance_tables'];

        // Store in request registry to save after item is saved
        Zend_Registry::set('provenance_data_to_save', array(
            'item_id' => $item->id,
            'data' => $cleanTables
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
     * Display provenance table on admin items show page.
     */
    public function hookAdminItemsShow($args)
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
        $numColumns = 3;
        $columnNames = array();
        $columnWidths = array();
        for ($i = 1; $i <= 3; $i++) {
            $columnNames[$i] = get_option('provenance_col' . $i . '_name') ?: 'Column ' . $i;
            $columnWidths[$i] = (int)get_option('provenance_col' . $i . '_width') ?: (($i == 1) ? 5 : 30);
        }

        $tabName = get_option('provenance_tab_name') ?: 'Provenance';

        // Display the table
        include dirname(__FILE__) . '/views/admin/items/provenance-show.php';
    }

    /**
     * Add CSS and JavaScript to public head.
     */
    public function hookPublicHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // Only load CSS on all pages, but JS only on item show pages
        queue_css_file('provenance-display');

        if ($controller == 'items' && $action == 'show') {
            queue_js_file('provenance-reposition');
        }
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
        $numColumns = 3;
        $columnNames = array();
        $columnWidths = array();
        for ($i = 1; $i <= 3; $i++) {
            $columnNames[$i] = get_option('provenance_col' . $i . '_name') ?: 'Column ' . $i;
            $columnWidths[$i] = (int)get_option('provenance_col' . $i . '_width') ?: (($i == 1) ? 5 : 30);
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
     * Get provenance data for an item (all tables with their rows).
     * Adds debug to show what is read from DB on Edit.
     */
    protected function _getProvenanceData($item)
    {
        if (!$item || !$item->id) {
            return array();
        }

        $db = $this->_db;

        // Get all tables for this item
        $sql = "SELECT * FROM {$db->prefix}provenance_tables
                WHERE item_id = ?
                ORDER BY table_order ASC";
        $tables = $db->fetchAll($sql, array($item->id));

        // Get rows for each table
        foreach ($tables as &$table) {
            $sql = "SELECT * FROM {$db->prefix}provenance_data
                    WHERE table_id = ?
                    ORDER BY row_order ASC";
            $rows = $db->fetchAll($sql, array($table['id']));


            $table['rows'] = $rows;
        }

        return $tables;
    }

    /**
     * Save provenance data for an item (multiple tables).
     * Adds debug to show what goes in + what is stored in DB after save.
     */
    public static function saveProvenanceData($itemId, $data)
    {
        $db = get_db();


        // Delete existing tables and data for this item
        $db->query("DELETE FROM {$db->prefix}provenance_data WHERE item_id = ?", array($itemId));
        $db->query("DELETE FROM {$db->prefix}provenance_tables WHERE item_id = ?", array($itemId));

        // Insert new data
        if (is_array($data) && !empty($data)) {
            $tableOrder = 0;

            foreach ($data as $tableData) {
                // Notes - strip br and collapse newlines
                $notes = isset($tableData['notes']) ? $tableData['notes'] : '';
                $notes = preg_replace('/<br\s*\/?\s*>/i', "\n", $notes);
                $notes = str_replace(array("\r\n", "\r"), "\n", $notes);
                $notes = preg_replace("/\n+/", "\n", $notes);
                $notes = trim($notes);

                $sql = "INSERT INTO {$db->prefix}provenance_tables
                        (item_id, table_order, notes)
                        VALUES (?, ?, ?)";
                $db->query($sql, array($itemId, $tableOrder++, $notes));
                $tableId = $db->lastInsertId();

                // Rows
                if (isset($tableData['rows']) && is_array($tableData['rows'])) {
                    $rowOrder = 0;

                    foreach ($tableData['rows'] as $row) {
                        // Normalize cols - strip br and collapse newlines
                        $col1 = isset($row['col1']) ? $row['col1'] : '';
                        $col2 = isset($row['col2']) ? $row['col2'] : '';
                        $col3 = isset($row['col3']) ? $row['col3'] : '';

                        // Strip br tags and collapse newlines
                        $col1 = preg_replace('/<br\s*\/?\s*>/i', "\n", $col1);
                        $col2 = preg_replace('/<br\s*\/?\s*>/i', "\n", $col2);
                        $col3 = preg_replace('/<br\s*\/?\s*>/i', "\n", $col3);
                        $col1 = str_replace(array("\r\n", "\r"), "\n", $col1);
                        $col2 = str_replace(array("\r\n", "\r"), "\n", $col2);
                        $col3 = str_replace(array("\r\n", "\r"), "\n", $col3);
                        $col1 = preg_replace("/\n+/", "\n", $col1);
                        $col2 = preg_replace("/\n+/", "\n", $col2);
                        $col3 = preg_replace("/\n+/", "\n", $col3);

                        // Only save rows that have data
                        if (trim($col1) === '' && trim($col2) === '' && trim($col3) === '') {
                            continue;
                        }

                        $sql = "INSERT INTO {$db->prefix}provenance_data
                                (table_id, item_id, row_order, col1, col2, col3)
                                VALUES (?, ?, ?, ?, ?, ?)";
                        $db->query($sql, array(
                            $tableId,
                            $itemId,
                            $rowOrder++,
                            $col1,
                            $col2,
                            $col3
                        ));
                    }
                }
            }
        }
    }
}