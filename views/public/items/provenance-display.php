<?php
/**
 * Public display of provenance data - Multiple Tables Support
 * This displays multiple provenance tables on the public item show page
 */
?>

<div id="provenance-section" class="element">
    <h3><?php echo html_escape($tabName); ?></h3>
    <div class="element-text">
        <?php foreach ($provenanceData as $tableIndex => $tableData): ?>
            <?php
            // Check if table has any content
            $tableHasContent = false;
            if (!empty($tableData['rows'])) {
                foreach ($tableData['rows'] as $row) {
                    for ($i = 1; $i <= 3; $i++) {
                        if (!empty(trim($row['col' . $i]))) {
                            $tableHasContent = true;
                            break 2;
                        }
                    }
                }
            }
            if (!$tableHasContent) continue;
            ?>

            <div class="provenance-table-section" style="margin-bottom: 30px;">
                <?php if (!empty(trim($tableData['notes']))): ?>
                    <div class="provenance-variety-notes">
                        <?php
                            // Strip br tags, split into lines, filter blanks, render as divs
                            $text = preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $tableData['notes']);
                            $text = str_replace(array("\r\n", "\r"), "\n", $text);
                            $lines = explode("\n", $text);
                            $lines = array_map('trim', $lines);
                            $lines = array_filter($lines, function($line) { return $line !== ''; });
                            foreach ($lines as $line) {
                                echo '<div style="margin: 0; padding: 0; line-height: 1.4;">' . html_escape($line) . '</div>';
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <th style="width: <?php echo $columnWidths[$i]; ?>%;"><?php echo html_escape($columnNames[$i]); ?></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tableData['rows'])): ?>
                            <?php foreach ($tableData['rows'] as $row): ?>
                                <?php
                                // Skip completely empty rows
                                $hasContent = false;
                                for ($i = 1; $i <= 3; $i++) {
                                    if (!empty(trim($row['col' . $i]))) {
                                        $hasContent = true;
                                        break;
                                    }
                                }
                                if (!$hasContent) continue;
                                ?>
                                <tr>
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                        <td><?php
                                            // Strip br tags, split into lines, filter blanks, render as divs
                                            $text = preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $row['col' . $i]);
                                            $text = str_replace(array("\r\n", "\r"), "\n", $text);
                                            $lines = explode("\n", $text);
                                            $lines = array_map('trim', $lines);
                                            $lines = array_filter($lines, function($line) { return $line !== ''; });
                                            foreach ($lines as $line) {
                                                echo '<div style="margin: 0; padding: 0; line-height: 1.4;">' . html_escape($line) . '</div>';
                                            }
                                        ?></td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>
