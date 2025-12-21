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
                    <div class="provenance-variety-notes pt-multiline">
                        <?php
                            // Normalize any injected <br> tags and collapse accidental blank lines
                            $cleanedNotes = preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $tableData['notes']);
                            $cleanedNotes = str_replace(array("\r\n", "\r"), "\n", $cleanedNotes);
                            $cleanedNotes = preg_replace("/\n[ \t]*\n+/", "\n", $cleanedNotes);
                            echo html_escape($cleanedNotes);
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
                                        <td class="pt-multiline"><?php
                                            // Normalize any injected <br> tags and collapse accidental blank lines
                                            $cleanedText = preg_replace('/<br\s*\/?\s*>/i', "\n", (string) $row['col' . $i]);
                                            $cleanedText = str_replace(array("\r\n", "\r"), "\n", $cleanedText);
                                            $cleanedText = preg_replace("/\n[ \t]*\n+/", "\n", $cleanedText);
                                            echo html_escape($cleanedText);
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
