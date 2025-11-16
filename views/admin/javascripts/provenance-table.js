/**
 * Provenance Table Plugin - JavaScript
 * Handles add/delete row functionality for the provenance table
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initProvenanceTable();
    });

    /**
     * Initialize provenance table
     */
    function initProvenanceTable() {
        // Check if we're on the provenance tab
        if ($('#provenance-table').length === 0) {
            return;
        }

        // Bind add row button
        $(document).on('click', '.add-provenance-row', function(e) {
            e.preventDefault();
            addRow();
        });

        // Bind delete row buttons
        $(document).on('click', '.delete-provenance-row', function(e) {
            e.preventDefault();
            deleteRow($(this));
        });
    }

    /**
     * Add a new row to the table
     */
    function addRow() {
        var $tbody = $('#provenance-table-body');

        // Get number of columns from config
        var numColumns = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.numColumns : 4;

        // Create new row
        var $newRow = $('<tr></tr>');

        // Add input fields for each column (index will be set by reindexRows)
        for (var i = 1; i <= numColumns; i++) {
            var $td = $('<td></td>');
            var $input = $('<input type="text" class="textinput provenance-col" name="provenance_data[0][col' + i + ']" value="" />');
            $td.append($input);
            $newRow.append($td);
        }

        // Add delete button
        var $actionTd = $('<td style="text-align: center;"></td>');
        var $deleteBtn = $('<button type="button" class="button delete-provenance-row">Delete</button>');
        $actionTd.append($deleteBtn);
        $newRow.append($actionTd);

        // Prepend to table (add at top)
        $tbody.prepend($newRow);

        // Re-index all rows
        reindexRows();

        // Focus on first input of new row
        $newRow.find('input').first().focus();
    }

    /**
     * Delete a row from the table
     */
    function deleteRow($button) {
        var $tbody = $('#provenance-table-body');
        var rowCount = $tbody.find('tr').length;

        if (rowCount <= 1) {
            alert('You must have at least one row.');
            return;
        }

        if (confirm('Are you sure you want to delete this row?')) {
            $button.closest('tr').remove();
            // Re-index all rows after deletion
            reindexRows();
        }
    }

    /**
     * Re-index row numbers in form field names after add/delete
     */
    function reindexRows() {
        $('#provenance-table-body tr').each(function(index) {
            $(this).find('input.provenance-col').each(function() {
                var name = $(this).attr('name');
                // Replace the row index (first number in brackets)
                var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', newName);
            });
        });
    }

})(jQuery);
