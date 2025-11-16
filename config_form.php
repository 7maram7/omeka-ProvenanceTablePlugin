<?php
/**
 * Configuration form for Provenance Table Plugin
 * Simple layout matching Omeka's default config forms
 */

$tabName = get_option('provenance_tab_name') ?: 'Provenance';
$numColumns = (int)get_option('provenance_num_columns') ?: 4;
$col1Name = get_option('provenance_col1_name') ?: 'No.';
$col2Name = get_option('provenance_col2_name') ?: 'Auction/Collection';
$col3Name = get_option('provenance_col3_name') ?: 'Date';
$col4Name = get_option('provenance_col4_name') ?: 'Characteristics';
$col1Width = get_option('provenance_col1_width') ?: '5';
$col2Width = get_option('provenance_col2_width') ?: '30';
$col3Width = get_option('provenance_col3_width') ?: '15';
$col4Width = get_option('provenance_col4_width') ?: '30';
$enabledItemTypes = get_option('provenance_enabled_item_types') ?: 'all';

// Get all item types
$itemTypes = get_db()->getTable('ItemType')->findAll();
?>

<div class="field">
    <label for="provenance_tab_name">Tab Display Name</label>
    <div class="inputs">
        <input type="text" name="provenance_tab_name" id="provenance_tab_name"
               value="<?php echo html_escape($tabName); ?>" class="textinput" size="30" />
        <p class="explanation">The name that will appear in the tab</p>
    </div>
</div>

<div class="field">
    <label for="provenance_num_columns">Number of Columns</label>
    <div class="inputs">
        <select name="provenance_num_columns" id="provenance_num_columns" class="textinput">
            <option value="2" <?php echo ($numColumns == 2) ? 'selected' : ''; ?>>2 Columns</option>
            <option value="3" <?php echo ($numColumns == 3) ? 'selected' : ''; ?>>3 Columns</option>
            <option value="4" <?php echo ($numColumns == 4) ? 'selected' : ''; ?>>4 Columns</option>
        </select>
        <p class="explanation">How many columns should the table have?</p>
    </div>
</div>

<fieldset>
    <legend>Column 1 Settings</legend>
    <div class="field">
        <label for="provenance_col1_name">Name</label>
        <div class="inputs">
            <input type="text" name="provenance_col1_name" id="provenance_col1_name"
                   value="<?php echo html_escape($col1Name); ?>" class="textinput" size="30" />
        </div>
    </div>
    <div class="field">
        <label for="provenance_col1_width">Width (%)</label>
        <div class="inputs">
            <input type="number" name="provenance_col1_width" id="provenance_col1_width"
                   value="<?php echo html_escape($col1Width); ?>" class="textinput" size="5" min="1" max="100" />
            <p class="explanation">Width as percentage (e.g., 5 for a narrow column, 30 for wider)</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Column 2 Settings</legend>
    <div class="field">
        <label for="provenance_col2_name">Name</label>
        <div class="inputs">
            <input type="text" name="provenance_col2_name" id="provenance_col2_name"
                   value="<?php echo html_escape($col2Name); ?>" class="textinput" size="30" />
        </div>
    </div>
    <div class="field">
        <label for="provenance_col2_width">Width (%)</label>
        <div class="inputs">
            <input type="number" name="provenance_col2_width" id="provenance_col2_width"
                   value="<?php echo html_escape($col2Width); ?>" class="textinput" size="5" min="1" max="100" />
            <p class="explanation">Width as percentage</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Column 3 Settings</legend>
    <div class="field">
        <label for="provenance_col3_name">Name</label>
        <div class="inputs">
            <input type="text" name="provenance_col3_name" id="provenance_col3_name"
                   value="<?php echo html_escape($col3Name); ?>" class="textinput" size="30" />
        </div>
    </div>
    <div class="field">
        <label for="provenance_col3_width">Width (%)</label>
        <div class="inputs">
            <input type="number" name="provenance_col3_width" id="provenance_col3_width"
                   value="<?php echo html_escape($col3Width); ?>" class="textinput" size="5" min="1" max="100" />
            <p class="explanation">Width as percentage</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Column 4 Settings</legend>
    <div class="field">
        <label for="provenance_col4_name">Name</label>
        <div class="inputs">
            <input type="text" name="provenance_col4_name" id="provenance_col4_name"
                   value="<?php echo html_escape($col4Name); ?>" class="textinput" size="30" />
            <p class="explanation">Only shown if "4 Columns" is selected above</p>
        </div>
    </div>
    <div class="field">
        <label for="provenance_col4_width">Width (%)</label>
        <div class="inputs">
            <input type="number" name="provenance_col4_width" id="provenance_col4_width"
                   value="<?php echo html_escape($col4Width); ?>" class="textinput" size="5" min="1" max="100" />
            <p class="explanation">Width as percentage</p>
        </div>
    </div>
</fieldset>

<fieldset>
    <legend>Enable for Item Types</legend>
    <div class="field">
        <label>Which Item Types</label>
        <div class="inputs">
            <p>
                <label>
                    <input type="radio" name="provenance_enable_mode" value="all"
                           <?php echo ($enabledItemTypes === 'all') ? 'checked' : ''; ?> />
                    Enable for ALL item types
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="provenance_enable_mode" value="specific"
                           <?php echo ($enabledItemTypes !== 'all') ? 'checked' : ''; ?> />
                    Enable only for specific item types:
                </label>
            </p>

            <div id="specific-types-container" style="margin-left: 20px; <?php echo ($enabledItemTypes === 'all') ? 'display:none;' : ''; ?>">
                <?php
                $enabledTypesArray = ($enabledItemTypes !== 'all') ? @unserialize($enabledItemTypes) : array();
                if (!is_array($enabledTypesArray)) {
                    $enabledTypesArray = array();
                }

                if (count($itemTypes) > 0):
                    foreach ($itemTypes as $itemType):
                        $checked = in_array($itemType->id, $enabledTypesArray) ? 'checked' : '';
                ?>
                    <p>
                        <label>
                            <input type="checkbox" name="provenance_enabled_item_types[]"
                                   value="<?php echo $itemType->id; ?>" <?php echo $checked; ?> />
                            <?php echo html_escape($itemType->name); ?>
                        </label>
                    </p>
                <?php
                    endforeach;
                else:
                ?>
                    <p><em>No item types found</em></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</fieldset>

<script type="text/javascript">
(function() {
    var allRadio = document.querySelector('input[name="provenance_enable_mode"][value="all"]');
    var specificRadio = document.querySelector('input[name="provenance_enable_mode"][value="specific"]');
    var container = document.getElementById('specific-types-container');

    if (allRadio) {
        allRadio.addEventListener('change', function() {
            if (this.checked) container.style.display = 'none';
        });
    }

    if (specificRadio) {
        specificRadio.addEventListener('change', function() {
            if (this.checked) container.style.display = 'block';
        });
    }
})();
</script>
