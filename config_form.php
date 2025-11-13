<?php
/**
 * Configuration form for Provenance Table Plugin
 */

$tabName = get_option('provenance_tab_name') ?: 'Provenance';
$numColumns = (int)get_option('provenance_num_columns') ?: 4;
$col1Name = get_option('provenance_col1_name') ?: 'No.';
$col2Name = get_option('provenance_col2_name') ?: 'Auction/Collection';
$col3Name = get_option('provenance_col3_name') ?: 'Date';
$col4Name = get_option('provenance_col4_name') ?: 'Characteristics';
$enabledItemTypes = get_option('provenance_enabled_item_types') ?: 'all';

// Get all item types
$itemTypes = get_db()->getTable('ItemType')->findAll();
?>

<div class="field">
    <div class="two columns alpha">
        <label for="provenance_tab_name">Tab Display Name</label>
    </div>
    <div class="inputs five columns omega">
        <input type="text" name="provenance_tab_name" id="provenance_tab_name"
               value="<?php echo html_escape($tabName); ?>" class="textinput" />
        <p class="explanation">The name that will appear in the tab (e.g., "Provenance", "History", "Ownership")</p>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="provenance_num_columns">Number of Columns</label>
    </div>
    <div class="inputs five columns omega">
        <select name="provenance_num_columns" id="provenance_num_columns" class="textinput">
            <option value="2" <?php echo ($numColumns == 2) ? 'selected' : ''; ?>>2 Columns</option>
            <option value="3" <?php echo ($numColumns == 3) ? 'selected' : ''; ?>>3 Columns</option>
            <option value="4" <?php echo ($numColumns == 4) ? 'selected' : ''; ?>>4 Columns</option>
        </select>
        <p class="explanation">How many columns should the provenance table have?</p>
    </div>
</div>

<fieldset>
    <legend>Column Names</legend>
    <p class="explanation">Configure the names for each column in your provenance table.</p>

    <div class="field">
        <div class="two columns alpha">
            <label for="provenance_col1_name">Column 1 Name</label>
        </div>
        <div class="inputs five columns omega">
            <input type="text" name="provenance_col1_name" id="provenance_col1_name"
                   value="<?php echo html_escape($col1Name); ?>" class="textinput" />
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <label for="provenance_col2_name">Column 2 Name</label>
        </div>
        <div class="inputs five columns omega">
            <input type="text" name="provenance_col2_name" id="provenance_col2_name"
                   value="<?php echo html_escape($col2Name); ?>" class="textinput" />
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <label for="provenance_col3_name">Column 3 Name</label>
        </div>
        <div class="inputs five columns omega">
            <input type="text" name="provenance_col3_name" id="provenance_col3_name"
                   value="<?php echo html_escape($col3Name); ?>" class="textinput" />
        </div>
    </div>

    <div class="field">
        <div class="two columns alpha">
            <label for="provenance_col4_name">Column 4 Name</label>
        </div>
        <div class="inputs five columns omega">
            <input type="text" name="provenance_col4_name" id="provenance_col4_name"
                   value="<?php echo html_escape($col4Name); ?>" class="textinput" />
            <p class="explanation">Column 4 only displays if "4 Columns" is selected above.</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Enable for Item Types</legend>
    <p class="explanation">Choose which item types should have the provenance tab. Leave all unchecked to enable for ALL item types.</p>

    <div class="field">
        <div class="two columns alpha">
            <label>Item Types</label>
        </div>
        <div class="inputs five columns omega">
            <label>
                <input type="radio" name="provenance_enable_mode" value="all"
                       <?php echo ($enabledItemTypes === 'all') ? 'checked' : ''; ?>
                       onclick="document.getElementById('specific-types-container').style.display='none';" />
                Enable for ALL item types
            </label>
            <br/><br/>
            <label>
                <input type="radio" name="provenance_enable_mode" value="specific"
                       <?php echo ($enabledItemTypes !== 'all') ? 'checked' : ''; ?>
                       onclick="document.getElementById('specific-types-container').style.display='block';" />
                Enable only for specific item types:
            </label>

            <div id="specific-types-container" style="margin-left: 20px; margin-top: 10px; <?php echo ($enabledItemTypes === 'all') ? 'display:none;' : ''; ?>">
                <?php
                $enabledTypesArray = ($enabledItemTypes !== 'all') ? @unserialize($enabledItemTypes) : array();
                if (!is_array($enabledTypesArray)) {
                    $enabledTypesArray = array();
                }

                if (count($itemTypes) > 0):
                    foreach ($itemTypes as $itemType):
                        $checked = in_array($itemType->id, $enabledTypesArray) ? 'checked' : '';
                ?>
                    <label>
                        <input type="checkbox" name="provenance_enabled_item_types[]"
                               value="<?php echo $itemType->id; ?>" <?php echo $checked; ?> />
                        <?php echo html_escape($itemType->name); ?>
                    </label><br/>
                <?php
                    endforeach;
                else:
                ?>
                    <p><em>No item types found. The provenance tab will be available for all items.</em></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</fieldset>

