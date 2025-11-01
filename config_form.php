<?php
/**
 * Configuration form for Provenance Table Plugin
 */
$enablePublic = get_option('provenance_table_enable_public');
$showEmpty = get_option('provenance_table_show_empty');
$defaultFields = get_option('provenance_table_default_fields');
?>

<div class="field">
    <div class="two columns alpha">
        <label for="provenance_table_enable_public">Display on Public Pages</label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            Check this box to display provenance tables on public item pages.
        </p>
        <?php echo get_view()->formCheckbox('provenance_table_enable_public', true,
            array('checked' => (boolean)$enablePublic)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="provenance_table_show_empty">Show Empty Tables</label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            Check this box to show the provenance section even when no provenance data exists for an item.
        </p>
        <?php echo get_view()->formCheckbox('provenance_table_show_empty', true,
            array('checked' => (boolean)$showEmpty)); ?>
    </div>
</div>

<div class="field">
    <div class="two columns alpha">
        <label for="provenance_table_default_fields">Default Fields</label>
    </div>
    <div class="inputs five columns omega">
        <p class="explanation">
            Comma-separated list of default fields to display (e.g., owner_name,date_from,date_to,location,source,notes).
            Available fields: owner_name, date_from, date_to, location, source, transaction_type, price, notes.
        </p>
        <?php echo get_view()->formText('provenance_table_default_fields', $defaultFields,
            array('class' => 'textinput')); ?>
    </div>
</div>
