<?php
/**
 * Admin panel for Provenance tab - Multiple Tables Support
 */

function pt_panel_debug_log($label, $value = null)
{
    // provenance-panel.php is: plugin/views/admin/items/
    $pluginRoot = dirname(dirname(dirname(dirname(__FILE__))));
    $debugFile  = $pluginRoot . '/debug_post.txt';

    $out = "=== PT PANEL DEBUG " . date('Y-m-d H:i:s') . " ===\n";
    $out .= $label . "\n";
    if (func_num_args() > 1) {
        $out .= var_export($value, true) . "\n";
    }
    $out .= "========================\n\n";
    @file_put_contents($debugFile, $out, FILE_APPEND);
}

/**
 * Decode and convert any <br> variants into real newlines for textarea display.
 */
function provenance_table_textarea_value($value)
{
    if ($value === null) {
        $value = '';
    }
    $value = (string)$value;

    // Decode entities twice (covers double-encoded cases)
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
    $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

    // Convert any <br ...> to newline
    $value = preg_replace('~<br\b[^>]*>~i', "\n", $value);

    // Normalize CRLF/CR
    $value = str_replace(array("\r\n", "\r"), "\n", $value);

    // Collapse multiple consecutive newlines into single newlines
    $value = preg_replace("/\n+/", "\n", $value);

    return $value;
}
?>

<div id="provenance-tables-container">
    <?php if (!empty($provenanceData)): ?>
        <?php foreach ($provenanceData as $tableIndex => $tableData): ?>
            <div class="provenance-table-wrapper" data-table-index="<?php echo $tableIndex; ?>">
                <div class="provenance-table-header">
                    <label>Variety Notes:</label>
                    <textarea name="provenance_tables[<?php echo $tableIndex; ?>][notes]"
                              class="provenance-notes"
                              rows="3"
                              style="width: 100%;"><?php
                        $notes = isset($tableData['notes']) ? $tableData['notes'] : '';
                        $notesNorm = provenance_table_textarea_value($notes);
                        echo html_escape($notesNorm);
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
                                            <textarea class="provenance-col"
                                                      name="provenance_tables[<?php echo $tableIndex; ?>][rows][<?php echo $rowIndex; ?>][col<?php echo $i; ?>]"
                                                      rows="1"><?php
                                                $key  = 'col' . $i;
                                                $text = isset($row[$key]) ? $row[$key] : '';

                                                // Render-time debug: only for first table, first row, col2
                                                if ($tableIndex == 0 && $rowIndex == 0 && $i == 2) {
                                                    pt_panel_debug_log("PANEL_RAW col2 (as seen by template)", $text);
                                                }

                                                $textNorm = provenance_table_textarea_value($text);

                                                if ($tableIndex == 0 && $rowIndex == 0 && $i == 2) {
                                                    pt_panel_debug_log("PANEL_NORMALIZED col2 (what we echo)", $textNorm);
                                                }

                                                echo html_escape($textNorm);
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
                                        <textarea class="provenance-col"
                                                  name="provenance_tables[<?php echo $tableIndex; ?>][rows][0][col<?php echo $i; ?>]"
                                                  rows="1"></textarea>
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
                          class="provenance-notes"
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
                                <textarea class="provenance-col"
                                          name="provenance_tables[0][rows][0][col<?php echo $i; ?>]"
                                          rows="1"></textarea>
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
var ProvenanceTableConfig = {
    numColumns: <?php echo $numColumns; ?>,
    columnNames: <?php echo json_encode($columnNames); ?>,
    columnWidths: <?php echo json_encode($columnWidths); ?>
};
</script>

<script type="text/javascript">
(function($){
    'use strict';

    function ptNormalize(v){
        if (!v) return v;
        // Replace any literal or encoded <br> variants with real newlines
        v = v.replace(/<br\b[^>]*>/gi, "\n");
        v = v.replace(/&lt;br\b[^&]*&gt;/gi, "\n");
        v = v.replace(/&amp;lt;br\b[^&]*&amp;gt;/gi, "\n");
        v = v.replace(/\r\n/g, "\n").replace(/\r/g, "\n");
        // Collapse double newlines that can be created by injected <br> plus existing newlines
        v = v.replace(/\n[ \t]*\n+/g, "\n");
        return v;
    }

    function ptFixAll(){
        var $c = $('#provenance-tables-container');
        if (!$c.length) return;

        var fixed = 0;
        $c.find('textarea').each(function(){
            var $t = $(this);
            var v = $t.val();
            if (!v) return;
            var nv = ptNormalize(v);
            if (nv !== v) {
                $t.val(nv);
                fixed++;
            }
        });
    }

    $(document).ready(function(){
        var $c = $('#provenance-tables-container');
        if (!$c.length) return;

        // Fix immediately
        ptFixAll();

        // Fix on interactions
        $(document).on('focusin input change keyup paste', '#provenance-tables-container textarea', function(){
            ptFixAll();
        });

        // Fix continuously (catches delayed/injected scripts)
        setInterval(ptFixAll, 500);
    });
})(jQuery);
</script>