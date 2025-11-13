<?php
/**
 * Configuration form for Provenance Table Plugin
 */

$mappings = unserialize(get_option('provenance_table_mappings'));
if (!is_array($mappings)) {
    $mappings = array();
}

// Get all item types
$itemTypes = get_db()->getTable('ItemType')->findAll();
?>

<div class="field">
    <div class="two columns alpha">
        <label>Field Mappings</label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            For each Item Type, select which text field should be transformed into the provenance table.
            The selected field will show as a 4-column table (No., Auction/Collection, Date, Characteristics) instead of a regular text box.
        </p>

        <?php if (empty($itemTypes)): ?>
            <p><em>No item types found. Please create an Item Type first, then configure this plugin.</em></p>
        <?php else: ?>
            <table class="provenance-config-table" style="width: 100%; margin-top: 15px;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 10px; background: #f0f0f0;">Item Type</th>
                        <th style="text-align: left; padding: 10px; background: #f0f0f0;">Field to Transform</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itemTypes as $itemType): ?>
                        <tr>
                            <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                                <strong><?php echo html_escape($itemType->name); ?></strong>
                            </td>
                            <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                                <select name="provenance_mappings[<?php echo $itemType->id; ?>]" class="textinput">
                                    <option value="">-- None (Disabled) --</option>
                                    <?php
                                    // Get all elements for this item type using the correct Omeka API
                                    $elements = get_db()->getTable('Element')->findByItemType($itemType->id);
                                    foreach ($elements as $element):
                                        $selected = (isset($mappings[$itemType->id]) && $mappings[$itemType->id] == $element->id) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $element->id; ?>" <?php echo $selected; ?>>
                                            <?php echo html_escape($element->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="explanation" style="margin-top: 5px; font-size: 11px;">
                                    Select a text field from this item type to transform into a table.
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px; padding: 15px; background: #e8f4f8; border-left: 4px solid #2582a0;">
                <strong>How it works:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    <li>Select which field should become a table for each Item Type</li>
                    <li>When you add/edit an item of that type, the selected field will appear as a 4-column table</li>
                    <li>Your existing data in that field will be preserved</li>
                    <li>You can enable/disable the table for different Item Types independently</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
