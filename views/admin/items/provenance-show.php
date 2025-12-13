<?php
/**
 * Admin show page display of provenance data - Multiple Tables Support
 * This displays multiple provenance tables on the admin items show page
 */
?>

<div id="provenance-metadata" class="section">
    <h2><?php echo html_escape($tabName); ?></h2>

    <?php foreach ($provenanceData as $tableIndex => $tableData): ?>
        <?php
        // Check if table has any content
        $tableHasContent = false;
        if (!empty($tableData['rows'])) {
            foreach ($tableData['rows'] as $row) {
                foreach ($enabledColumns as $i) {
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
                <h3>Variety Notes:</h3>
                <div class="provenance-variety-notes" style="margin-bottom: 10px; padding: 10px; background-color: #f9f9f9; border-left: 3px solid #ddd; white-space: pre-wrap;">
                    <?php echo nl2br(html_escape($tableData['notes'])); ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <?php foreach ($enabledColumns as $i): ?>
                            <th style="width: <?php echo $columnWidths[$i]; ?>%;"><?php echo html_escape($columnNames[$i]); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tableData['rows'])): ?>
                        <?php foreach ($tableData['rows'] as $row): ?>
                            <?php
                            // Skip completely empty rows
                            $hasContent = false;
                            foreach ($enabledColumns as $i) {
                                if (!empty(trim($row['col' . $i]))) {
                                    $hasContent = true;
                                    break;
                                }
                            }
                            if (!$hasContent) continue;
                            ?>
                            <tr>
                                <?php foreach ($enabledColumns as $i): ?>
                                    <td><?php echo html_escape($row['col' . $i]); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</div>
