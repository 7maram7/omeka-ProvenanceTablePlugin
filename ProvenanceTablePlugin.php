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
    public $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'admin_head',
        'public_head',
        'public_items_show',
    );

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Set default options (empty - user must configure)
        set_option('provenance_table_mappings', serialize(array()));
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Delete plugin options
        delete_option('provenance_table_mappings');
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

        // Save the mappings
        if (isset($post['provenance_mappings']) && is_array($post['provenance_mappings'])) {
            set_option('provenance_table_mappings', serialize($post['provenance_mappings']));
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

        // Only load on items add/edit pages
        if ($module == 'default' && $controller == 'items' && ($action == 'add' || $action == 'edit')) {
            queue_css_file('provenance-table');
            queue_js_file('provenance-table');

            // Get the mappings
            $mappings = unserialize(get_option('provenance_table_mappings'));
            if (!is_array($mappings)) {
                $mappings = array();
            }

            // Pass configuration to JavaScript
            echo '<script type="text/javascript">';
            echo 'var ProvenanceTableConfig = ' . json_encode($mappings) . ';';
            echo '</script>';
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

        // Try to get provenance data
        $provenanceText = metadata($item, array('Item Type Metadata', 'Provenance'), array('no_escape' => true));

        if (empty($provenanceText)) {
            return;
        }

        // Try to parse as JSON (structured data from our table)
        $provenanceData = json_decode($provenanceText, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($provenanceData) && !empty($provenanceData)) {
            // Display as table
            $this->_displayProvenanceTable($provenanceData);
        } else {
            // Display as regular text (old format)
            echo '<div id="provenance-section" class="element">';
            echo '<h3>Provenance</h3>';
            echo '<div class="element-text">' . html_escape($provenanceText) . '</div>';
            echo '</div>';
        }
    }

    /**
     * Display provenance data as a formatted table.
     *
     * @param array $data
     */
    protected function _displayProvenanceTable($data)
    {
        echo '<div id="provenance-section" class="element">';
        echo '<h3>Provenance</h3>';
        echo '<div class="element-text">';
        echo '<table class="provenance-display-table">';
        echo '<thead><tr>';
        echo '<th>No.</th>';
        echo '<th>Auction or Collection</th>';
        echo '<th>Date</th>';
        echo '<th>Characteristics</th>';
        echo '</tr></thead>';
        echo '<tbody>';

        foreach ($data as $index => $row) {
            echo '<tr>';
            echo '<td>' . html_escape($index + 1) . '</td>';
            echo '<td>' . html_escape(isset($row['auction']) ? $row['auction'] : '') . '</td>';
            echo '<td>' . html_escape(isset($row['date']) ? $row['date'] : '') . '</td>';
            echo '<td>' . html_escape(isset($row['characteristics']) ? $row['characteristics'] : '') . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
}
