<?php
/**
 * Admin panel for Provenance tab - Multiple Tables Support
 * Simple, clean layout matching Omeka's default styling
 */
?>

<div id="provenance-tables-container">
    <?php if (!empty($provenanceData)): ?>
        <?php foreach ($provenanceData as $tableIndex => $tableData): ?>
            <div class="provenance-table-wrapper" data-table-index="<?php echo $tableIndex; ?>">
                <div class="provenance-table-header">
                    <label>Variety Notes:</label>
                    <textarea name="provenance_tables[<?php echo $tableIndex; ?>][notes]"
                              class="provenance-notes textinput"
                              rows="3"
                              style="width: 100%;"><?php
                        // Strip all variations of <br> tags and convert to newlines
                        $cleanedNotes = preg_replace('/<br\s*\/?\s*>/i', "\n", $tableData['notes']);
                        echo html_escape($cleanedNotes);
                    ?></textarea>
                </div>

                <table class="provenance-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;"></th>
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <th style="width: <?php echo $columnWidths[$i]; ?>%;"><?php echo html_escape($columnNames[$i]); ?></th>
                            <?php endfor; ?>
                            <th style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="provenance-table-body">
                        <?php if (!empty($tableData['rows'])): ?>
                            <?php foreach ($tableData['rows'] as $rowIndex => $row): ?>
                                <tr>
                                    <td class="drag-handle" style="text-align: center; cursor: move;">
                                        <span class="drag-icon">⋮⋮</span>
                                    </td>
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                        <td>
                                            <textarea class="textinput provenance-col"
                                                      name="provenance_tables[<?php echo $tableIndex; ?>][rows][<?php echo $rowIndex; ?>][col<?php echo $i; ?>]"
                                                      rows="2"><?php
                                                // Strip all variations of <br> tags and convert to newlines
                                                $cleanedText = preg_replace('/<br\s*\/?\s*>/i', "\n", $row['col' . $i]);
                                                echo html_escape($cleanedText);
                                            ?></textarea>
                                        </td>
                                    <?php endfor; ?>
                                    <td style="text-align: center;">
                                        <button type="button" class="button delete-provenance-row">Delete Row</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td class="drag-handle" style="text-align: center; cursor: move;">
                                    <span class="drag-icon">⋮⋮</span>
                                </td>
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <td>
                                        <textarea class="textinput provenance-col"
                                                  name="provenance_tables[<?php echo $tableIndex; ?>][rows][0][col<?php echo $i; ?>]"
                                                  rows="2"></textarea>
                                    </td>
                                <?php endfor; ?>
                                <td style="text-align: center;">
                                    <button type="button" class="button delete-provenance-row">Delete Row</button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <p>
                    <button type="button" class="button add-provenance-row">Add Row</button>
                    <?php if ($tableIndex > 0 || count($provenanceData) > 1): ?>
                        <button type="button" class="button delete-provenance-table" style="margin-left: 10px;">Delete Table</button>
                    <?php endif; ?>
                </p>
                <hr style="margin: 20px 0; border: 1px solid #ccc;">
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="provenance-table-wrapper" data-table-index="0">
            <div class="provenance-table-header">
                <label>Variety Notes:</label>
                <textarea name="provenance_tables[0][notes]"
                          class="provenance-notes textinput"
                          rows="3"
                          style="width: 100%;"></textarea>
            </div>

            <table class="provenance-table">
                <thead>
                    <tr>
                        <th style="width: 30px;"></th>
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <th style="width: <?php echo $columnWidths[$i]; ?>%;"><?php echo html_escape($columnNames[$i]); ?></th>
                        <?php endfor; ?>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="provenance-table-body">
                    <tr>
                        <td class="drag-handle" style="text-align: center; cursor: move;">
                            <span class="drag-icon">⋮⋮</span>
                        </td>
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <td>
                                <textarea class="textinput provenance-col"
                                          name="provenance_tables[0][rows][0][col<?php echo $i; ?>]"
                                          rows="2"></textarea>
                            </td>
                        <?php endfor; ?>
                        <td style="text-align: center;">
                            <button type="button" class="button delete-provenance-row">Delete Row</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p>
                <button type="button" class="button add-provenance-row">Add Row</button>
            </p>
            <hr style="margin: 20px 0; border: 1px solid #ccc;">
        </div>
    <?php endif; ?>
</div>

<p>
    <button type="button" class="button add-provenance-table" id="add-provenance-table">Add Table</button>
</p>

<p class="explanation">
    Add multiple provenance tables to track different varieties of this item. Use "Variety Notes" to describe each variety.
</p>

<script type="text/javascript">
// Store configuration for JavaScript
var ProvenanceTableConfig = {
    numColumns: <?php echo $numColumns; ?>,
    columnNames: <?php echo json_encode($columnNames); ?>,
    columnWidths: <?php echo json_encode($columnWidths); ?>
};
</script>
