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
        'admin_head',
        'public_items_show',
    );

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
        }
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
            echo '<div class="element-text">' . $provenanceText . '</div>';
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
            echo '<td>' . html_escape($row['auction']) . '</td>';
            echo '<td>' . html_escape($row['date']) . '</td>';
            echo '<td>' . html_escape($row['characteristics']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
}
