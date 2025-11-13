<?php
/**
 * Admin panel for Provenance tab
 * This displays the provenance table interface when editing/adding items
 */
?>

<div class="field" id="provenance-table-container">
    <div class="two columns alpha">
        <label>Provenance Data</label>
    </div>
    <div class="inputs five columns omega">
        <div class="provenance-table-wrapper">
            <table class="provenance-table" id="provenance-table">
                <thead>
                    <tr>
                        <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                            <th><?php echo html_escape($columnNames[$i]); ?></th>
                        <?php endfor; ?>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="provenance-table-body">
                    <?php if (!empty($provenanceData)): ?>
                        <?php foreach ($provenanceData as $row): ?>
                            <tr>
                                <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                                    <td>
                                        <input type="text"
                                               class="textinput provenance-col"
                                               name="provenance_data[][col<?php echo $i; ?>]"
                                               value="<?php echo html_escape($row['col' . $i]); ?>" />
                                    </td>
                                <?php endfor; ?>
                                <td>
                                    <button type="button" class="button delete-provenance-row">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php // Start with one empty row ?>
                        <tr>
                            <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                                <td>
                                    <input type="text"
                                           class="textinput provenance-col"
                                           name="provenance_data[][col<?php echo $i; ?>]"
                                           value="" />
                                </td>
                            <?php endfor; ?>
                            <td>
                                <button type="button" class="button delete-provenance-row">Delete</button>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="provenance-table-actions" style="margin-top: 10px;">
                <button type="button" class="button add-provenance-row" id="add-provenance-row">Add Row</button>
            </div>

            <p class="explanation" style="margin-top: 10px;">
                Add rows to track the provenance history of this item. Click "Add Row" to insert additional entries.
            </p>
        </div>
    </div>
</div>

<script type="text/javascript">
// Store configuration for JavaScript
var ProvenanceTableConfig = {
    numColumns: <?php echo $numColumns; ?>,
    columnNames: <?php echo json_encode($columnNames); ?>
};
</script>
