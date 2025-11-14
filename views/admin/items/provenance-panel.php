<?php
/**
 * Admin panel for Provenance tab
 * Simple, clean layout matching Omeka's default styling
 */
?>

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
            <?php foreach ($provenanceData as $rowIndex => $row): ?>
                <tr>
                    <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                        <td>
                            <input type="text"
                                   class="textinput provenance-col"
                                   name="provenance_data[<?php echo $rowIndex; ?>][col<?php echo $i; ?>]"
                                   value="<?php echo html_escape($row['col' . $i]); ?>" />
                        </td>
                    <?php endfor; ?>
                    <td style="text-align: center;">
                        <button type="button" class="button delete-provenance-row">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                    <td>
                        <input type="text"
                               class="textinput provenance-col"
                               name="provenance_data[0][col<?php echo $i; ?>]"
                               value="" />
                    </td>
                <?php endfor; ?>
                <td style="text-align: center;">
                    <button type="button" class="button delete-provenance-row">Delete</button>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<p>
    <button type="button" class="button add-provenance-row" id="add-provenance-row">Add Row</button>
</p>

<p class="explanation">
    Add rows to track the provenance history of this item. Click "Add Row" to insert additional entries.
</p>

<script type="text/javascript">
// Store configuration for JavaScript
var ProvenanceTableConfig = {
    numColumns: <?php echo $numColumns; ?>,
    columnNames: <?php echo json_encode($columnNames); ?>
};
</script>
