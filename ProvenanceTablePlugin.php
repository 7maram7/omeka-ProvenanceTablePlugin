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
        'config_form',
        'config',
        'admin_items_show',
        'public_items_show',
        'admin_head',
        'public_head',
    );

    /**
     * @var array Plugin filters
     */
    protected $_filters = array(
        'admin_items_form_tabs',
        'item_citation',
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        $db = $this->_db;

        // Create provenance entries table
        $sql = "
        CREATE TABLE IF NOT EXISTS `{$db->prefix}provenance_entries` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `item_id` int(10) unsigned NOT NULL,
            `entry_order` int(10) unsigned NOT NULL DEFAULT '0',
            `owner_name` text COLLATE utf8_unicode_ci,
            `date_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `date_to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `location` text COLLATE utf8_unicode_ci,
            `source` text COLLATE utf8_unicode_ci,
            `transaction_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `price` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `notes` text COLLATE utf8_unicode_ci,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `item_id` (`item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ";

        $db->query($sql);

        // Set default configuration
        set_option('provenance_table_enable_public', '1');
        set_option('provenance_table_show_empty', '0');
        set_option('provenance_table_default_fields', 'owner_name,date_from,date_to,location,source,notes');
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        $db = $this->_db;

        // Drop the provenance entries table
        $sql = "DROP TABLE IF EXISTS `{$db->prefix}provenance_entries`";
        $db->query($sql);

        // Delete plugin options
        delete_option('provenance_table_enable_public');
        delete_option('provenance_table_show_empty');
        delete_option('provenance_table_default_fields');
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

        set_option('provenance_table_enable_public', (int)(boolean)$post['provenance_table_enable_public']);
        set_option('provenance_table_show_empty', (int)(boolean)$post['provenance_table_show_empty']);
        set_option('provenance_table_default_fields', $post['provenance_table_default_fields']);
    }

    /**
     * Add provenance table to admin items show page.
     */
    public function hookAdminItemsShow($args)
    {
        $item = $args['item'];
        $this->_displayProvenanceTable($item, true);
    }

    /**
     * Add provenance table to public items show page.
     */
    public function hookPublicItemsShow($args)
    {
        if (get_option('provenance_table_enable_public')) {
            $item = $args['item'];
            $this->_displayProvenanceTable($item, false);
        }
    }

    /**
     * Add CSS and JavaScript to admin head.
     */
    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // Only load on items pages
        if ($module == 'default' && $controller == 'items') {
            queue_css_file('provenance-table');
            queue_js_file('provenance-table');
        }
    }

    /**
     * Add CSS to public head.
     */
    public function hookPublicHead($args)
    {
        if (get_option('provenance_table_enable_public')) {
            queue_css_file('provenance-display', 'all', false, 'css');
        }
    }

    /**
     * Add a tab to the admin items form.
     */
    public function filterAdminItemsFormTabs($tabs, $args)
    {
        $item = $args['item'];
        $tabs['Provenance'] = $this->_getProvenanceFormContent($item);
        return $tabs;
    }

    /**
     * Display the provenance table for an item.
     *
     * @param Item $item
     * @param bool $isAdmin
     */
    protected function _displayProvenanceTable($item, $isAdmin = false)
    {
        $entries = $this->_getProvenanceEntries($item->id);

        if (empty($entries) && !get_option('provenance_table_show_empty') && !$isAdmin) {
            return;
        }

        echo '<div id="provenance-section" class="provenance-section">';
        echo '<h2>Provenance History</h2>';

        if (!empty($entries)) {
            echo '<table class="provenance-display-table">';
            echo '<thead><tr>';
            echo '<th>Owner/Holder</th>';
            echo '<th>Date From</th>';
            echo '<th>Date To</th>';
            echo '<th>Location</th>';
            echo '<th>Source</th>';
            echo '<th>Transaction Type</th>';
            echo '<th>Price</th>';
            echo '<th>Notes</th>';
            echo '</tr></thead>';
            echo '<tbody>';

            foreach ($entries as $entry) {
                echo '<tr>';
                echo '<td>' . html_escape($entry['owner_name']) . '</td>';
                echo '<td>' . html_escape($entry['date_from']) . '</td>';
                echo '<td>' . html_escape($entry['date_to']) . '</td>';
                echo '<td>' . html_escape($entry['location']) . '</td>';
                echo '<td>' . html_escape($entry['source']) . '</td>';
                echo '<td>' . html_escape($entry['transaction_type']) . '</td>';
                echo '<td>' . html_escape($entry['price']) . '</td>';
                echo '<td>' . html_escape($entry['notes']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No provenance information available for this item.</p>';
        }

        echo '</div>';
    }

    /**
     * Get provenance form content for admin.
     *
     * @param Item $item
     * @return string
     */
    protected function _getProvenanceFormContent($item)
    {
        $entries = array();
        if ($item->id) {
            $entries = $this->_getProvenanceEntries($item->id);
        }

        ob_start();
        ?>
        <div id="provenance-table-container">
            <p class="provenance-instructions">
                Add provenance entries to track the ownership history of this item.
                Click "Add Entry" to create a new row. Entries will be saved when you save the item.
            </p>

            <table id="provenance-table" class="provenance-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Owner/Holder *</th>
                        <th>Date From</th>
                        <th>Date To</th>
                        <th>Location</th>
                        <th>Source</th>
                        <th>Transaction Type</th>
                        <th>Price</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="provenance-table-body">
                    <?php if (!empty($entries)): ?>
                        <?php foreach ($entries as $index => $entry): ?>
                            <tr class="provenance-entry" data-entry-id="<?php echo $entry['id']; ?>">
                                <td class="drag-handle">
                                    <span class="order-number"><?php echo $index + 1; ?></span>
                                    <input type="hidden" name="provenance[<?php echo $index; ?>][id]" value="<?php echo $entry['id']; ?>" />
                                    <input type="hidden" name="provenance[<?php echo $index; ?>][entry_order]" value="<?php echo $index; ?>" class="entry-order" />
                                </td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][owner_name]" value="<?php echo html_escape($entry['owner_name']); ?>" class="textinput" required /></td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][date_from]" value="<?php echo html_escape($entry['date_from']); ?>" class="textinput" placeholder="YYYY or YYYY-MM-DD" /></td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][date_to]" value="<?php echo html_escape($entry['date_to']); ?>" class="textinput" placeholder="YYYY or YYYY-MM-DD" /></td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][location]" value="<?php echo html_escape($entry['location']); ?>" class="textinput" /></td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][source]" value="<?php echo html_escape($entry['source']); ?>" class="textinput" /></td>
                                <td>
                                    <select name="provenance[<?php echo $index; ?>][transaction_type]" class="textinput">
                                        <option value="">Select...</option>
                                        <option value="Purchase" <?php echo $entry['transaction_type'] == 'Purchase' ? 'selected' : ''; ?>>Purchase</option>
                                        <option value="Gift" <?php echo $entry['transaction_type'] == 'Gift' ? 'selected' : ''; ?>>Gift</option>
                                        <option value="Inheritance" <?php echo $entry['transaction_type'] == 'Inheritance' ? 'selected' : ''; ?>>Inheritance</option>
                                        <option value="Loan" <?php echo $entry['transaction_type'] == 'Loan' ? 'selected' : ''; ?>>Loan</option>
                                        <option value="Auction" <?php echo $entry['transaction_type'] == 'Auction' ? 'selected' : ''; ?>>Auction</option>
                                        <option value="Unknown" <?php echo $entry['transaction_type'] == 'Unknown' ? 'selected' : ''; ?>>Unknown</option>
                                    </select>
                                </td>
                                <td><input type="text" name="provenance[<?php echo $index; ?>][price]" value="<?php echo html_escape($entry['price']); ?>" class="textinput" placeholder="e.g., $100 USD" /></td>
                                <td><textarea name="provenance[<?php echo $index; ?>][notes]" class="textinput" rows="2"><?php echo html_escape($entry['notes']); ?></textarea></td>
                                <td><button type="button" class="delete-entry button">Delete</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <button type="button" id="add-provenance-entry" class="add-provenance-entry button">Add Entry</button>
            <input type="hidden" id="provenance-entry-index" value="<?php echo count($entries); ?>" />
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get provenance entries for an item.
     *
     * @param int $itemId
     * @return array
     */
    protected function _getProvenanceEntries($itemId)
    {
        $db = $this->_db;
        $sql = "
            SELECT * FROM `{$db->prefix}provenance_entries`
            WHERE `item_id` = ?
            ORDER BY `entry_order` ASC
        ";

        return $db->fetchAll($sql, array($itemId));
    }

    /**
     * Save provenance entries for an item.
     *
     * @param Item $item
     * @param array $provenanceData
     */
    public static function saveProvenanceEntries($item, $provenanceData)
    {
        if (!$item->id) {
            return;
        }

        $db = get_db();

        // Get existing entry IDs
        $existingIds = array();
        foreach ($provenanceData as $data) {
            if (!empty($data['id'])) {
                $existingIds[] = (int)$data['id'];
            }
        }

        // Delete entries not in the submitted data
        if (!empty($existingIds)) {
            $sql = "DELETE FROM `{$db->prefix}provenance_entries`
                    WHERE `item_id` = ? AND `id` NOT IN (" . implode(',', $existingIds) . ")";
            $db->query($sql, array($item->id));
        } else {
            $sql = "DELETE FROM `{$db->prefix}provenance_entries` WHERE `item_id` = ?";
            $db->query($sql, array($item->id));
        }

        // Insert or update each entry
        foreach ($provenanceData as $index => $data) {
            // Skip empty entries
            if (empty($data['owner_name'])) {
                continue;
            }

            $entryData = array(
                'item_id' => $item->id,
                'entry_order' => $index,
                'owner_name' => $data['owner_name'],
                'date_from' => !empty($data['date_from']) ? $data['date_from'] : null,
                'date_to' => !empty($data['date_to']) ? $data['date_to'] : null,
                'location' => !empty($data['location']) ? $data['location'] : null,
                'source' => !empty($data['source']) ? $data['source'] : null,
                'transaction_type' => !empty($data['transaction_type']) ? $data['transaction_type'] : null,
                'price' => !empty($data['price']) ? $data['price'] : null,
                'notes' => !empty($data['notes']) ? $data['notes'] : null,
            );

            if (!empty($data['id'])) {
                // Update existing entry
                $db->update($db->prefix . 'provenance_entries', $entryData, array('id = ?' => $data['id']));
            } else {
                // Insert new entry
                $db->insert($db->prefix . 'provenance_entries', $entryData);
            }
        }
    }
}
