<?php
/**
 * Public display of provenance data
 * This displays the provenance table on the public item show page
 */
?>

<div id="provenance-section" class="element">
    <h3><?php echo html_escape($tabName); ?></h3>
    <div class="element-text">
        <table class="provenance-display-table">
            <thead>
                <tr>
                    <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                        <th><?php echo html_escape($columnNames[$i]); ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($provenanceData as $row): ?>
                    <?php
                    // Skip completely empty rows
                    $hasContent = false;
                    for ($i = 1; $i <= $numColumns; $i++) {
                        if (!empty(trim($row['col' . $i]))) {
                            $hasContent = true;
                            break;
                        }
                    }
                    if (!$hasContent) continue;
                    ?>
                    <tr>
                        <?php for ($i = 1; $i <= $numColumns; $i++): ?>
                            <td><?php echo html_escape($row['col' . $i]); ?></td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
