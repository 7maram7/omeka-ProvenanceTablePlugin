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
                    <div class="provenance-variety-notes" style="margin-bottom: 10px; line-height: 1.0;">
                        <?php
                            // Strip <br> tags and split into lines
                            $cleanedNotes = preg_replace('/<br\s*\/?\s*>/i', "\n", $tableData['notes']);
                            $cleanedNotes = str_replace(array("\r\n", "\r"), "\n", $cleanedNotes);
                            // Split by newlines, trim each line, filter out empty lines, then rejoin
                            $lines = explode("\n", $cleanedNotes);
                            $lines = array_map('trim', $lines);
                            $lines = array_filter($lines, function($line) { return $line !== ''; });
                            // Output each line as a div for precise spacing control
                            foreach ($lines as $line) {
                                echo '<div style="margin: 0; padding: 0;">' . html_escape($line) . '</div>';
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
                                        <td style="line-height: 1.0;"><?php
                                            // Strip <br> tags and split into lines
                                            $cleanedText = preg_replace('/<br\s*\/?\s*>/i', "\n", $row['col' . $i]);
                                            $cleanedText = str_replace(array("\r\n", "\r"), "\n", $cleanedText);
                                            // Split by newlines, trim each line, filter out empty lines, then rejoin
                                            $lines = explode("\n", $cleanedText);
                                            $lines = array_map('trim', $lines);
                                            $lines = array_filter($lines, function($line) { return $line !== ''; });
                                            // Output each line as a div for precise spacing control
                                            foreach ($lines as $line) {
                                                echo '<div style="margin: 0; padding: 0;">' . html_escape($line) . '</div>';
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
