<?php
/**
 * Admin show page display of provenance data
 * This displays the provenance table on the admin items show page
 */
?>

<div id="provenance-metadata" class="section">
    <h2><?php echo html_escape($tabName); ?></h2>

    <table>
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
