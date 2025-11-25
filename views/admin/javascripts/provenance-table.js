/**
 * Provenance Table Plugin - JavaScript
 * Handles add/delete row and table functionality for multiple provenance tables
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initProvenanceTables();
    });

    /**
     * Initialize provenance tables
     */
    function initProvenanceTables() {
        // Check if we're on the provenance tab
        if ($('#provenance-tables-container').length === 0) {
            return;
        }

        // Initialize drag and drop for existing tables
        initializeSortable();

        // Bind add table button
        $(document).on('click', '.add-provenance-table', function(e) {
            e.preventDefault();
            addTable();
        });

        // Bind delete table buttons
        $(document).on('click', '.delete-provenance-table', function(e) {
            e.preventDefault();
            deleteTable($(this));
        });

        // Bind add row button
        $(document).on('click', '.add-provenance-row', function(e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.provenance-table-wrapper');
            addRow($wrapper);
        });

        // Bind delete row buttons
        $(document).on('click', '.delete-provenance-row', function(e) {
            e.preventDefault();
            deleteRow($(this));
        });
    }

    /**
     * Initialize jQuery UI Sortable on all table bodies
     */
    function initializeSortable() {
        $('.provenance-table-body').each(function() {
            var $tbody = $(this);

            // Destroy existing sortable if it exists
            if ($tbody.hasClass('ui-sortable')) {
                $tbody.sortable('destroy');
            }

            // Initialize sortable
            $tbody.sortable({
                handle: '.drag-handle',
                axis: 'y',
                cursor: 'move',
                opacity: 0.8,
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    // Re-index rows after sorting
                    var $wrapper = $(this).closest('.provenance-table-wrapper');
                    reindexRowsInTable($wrapper);
                }
            });
        });
    }

    /**
     * Add a new table
     */
    function addTable() {
        var $container = $('#provenance-tables-container');
        var tableCount = $container.find('.provenance-table-wrapper').length;
        var numColumns = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.numColumns : 4;
        var columnNames = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.columnNames : {};
        var columnWidths = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.columnWidths : {};

        // Create new table wrapper
        var $newWrapper = $('<div class="provenance-table-wrapper" data-table-index="' + tableCount + '"></div>');

        // Add notes textarea
        var $header = $('<div class="provenance-table-header"></div>');
        $header.append('<label>Variety Notes:</label>');
        $header.append('<textarea name="provenance_tables[' + tableCount + '][notes]" class="provenance-notes textinput" rows="3" style="width: 100%;"></textarea>');
        $newWrapper.append($header);

        // Create table
        var $table = $('<table class="provenance-table"></table>');

        // Add header
        var $thead = $('<thead><tr></tr></thead>');
        // Add empty header for drag handle column
        $thead.find('tr').append('<th style="width: 30px;"></th>');
        for (var i = 1; i <= numColumns; i++) {
            var colName = columnNames[i] || ('Column ' + i);
            var colWidth = columnWidths[i] || 25;
            $thead.find('tr').append('<th style="width: ' + colWidth + '%;">' + colName + '</th>');
        }
        $thead.find('tr').append('<th style="width: 80px;">Actions</th>');
        $table.append($thead);

        // Add body with one empty row
        var $tbody = $('<tbody class="provenance-table-body"></tbody>');
        var $row = $('<tr></tr>');
        // Add drag handle
        $row.append('<td class="drag-handle" style="text-align: center; cursor: move;"><span class="drag-icon">⋮⋮</span></td>');
        for (var i = 1; i <= numColumns; i++) {
            var $td = $('<td></td>');
            var $input = $('<input type="text" class="textinput provenance-col" name="provenance_tables[' + tableCount + '][rows][0][col' + i + ']" value="" />');
            $td.append($input);
            $row.append($td);
        }
        $row.append('<td style="text-align: center;"><button type="button" class="button delete-provenance-row">Delete Row</button></td>');
        $tbody.append($row);
        $table.append($tbody);

        $newWrapper.append($table);

        // Add buttons
        var $buttons = $('<p></p>');
        $buttons.append('<button type="button" class="button add-provenance-row">Add Row</button>');
        $buttons.append('<button type="button" class="button delete-provenance-table" style="margin-left: 10px;">Delete Table</button>');
        $newWrapper.append($buttons);

        // Add separator
        $newWrapper.append('<hr style="margin: 20px 0; border: 1px solid #ccc;">');

        // Append to container
        $container.append($newWrapper);

        // Re-index all tables
        reindexTables();

        // Initialize sortable on the new table
        initializeSortable();

        // Focus on notes field
        $newWrapper.find('textarea').first().focus();
    }

    /**
     * Delete a table
     */
    function deleteTable($button) {
        var $container = $('#provenance-tables-container');
        var tableCount = $container.find('.provenance-table-wrapper').length;

        if (tableCount <= 1) {
            alert('You must have at least one table.');
            return;
        }

        if (confirm('Are you sure you want to delete this entire table?')) {
            $button.closest('.provenance-table-wrapper').remove();
            // Re-index all tables after deletion
            reindexTables();
        }
    }

    /**
     * Add a new row to a specific table
     */
    function addRow($wrapper) {
        var $tbody = $wrapper.find('.provenance-table-body');
        var tableIndex = $wrapper.attr('data-table-index');
        var numColumns = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.numColumns : 4;

        // Create new row
        var $newRow = $('<tr></tr>');

        // Add drag handle
        var $dragTd = $('<td class="drag-handle" style="text-align: center; cursor: move;"></td>');
        $dragTd.append('<span class="drag-icon">⋮⋮</span>');
        $newRow.append($dragTd);

        // Add input fields for each column (index will be set by reindexRows)
        for (var i = 1; i <= numColumns; i++) {
            var $td = $('<td></td>');
            var $input = $('<input type="text" class="textinput provenance-col" name="provenance_tables[' + tableIndex + '][rows][0][col' + i + ']" value="" />');
            $td.append($input);
            $newRow.append($td);
        }

        // Add delete button
        var $actionTd = $('<td style="text-align: center;"></td>');
        var $deleteBtn = $('<button type="button" class="button delete-provenance-row">Delete Row</button>');
        $actionTd.append($deleteBtn);
        $newRow.append($actionTd);

        // Prepend to table (add at top)
        $tbody.prepend($newRow);

        // Re-index rows in this table
        reindexRowsInTable($wrapper);

        // Focus on first input of new row
        $newRow.find('input').first().focus();
    }

    /**
     * Delete a row from a table
     */
    function deleteRow($button) {
        var $wrapper = $button.closest('.provenance-table-wrapper');
        var $tbody = $wrapper.find('.provenance-table-body');
        var rowCount = $tbody.find('tr').length;

        if (rowCount <= 1) {
            alert('You must have at least one row in each table.');
            return;
        }

        if (confirm('Are you sure you want to delete this row?')) {
            $button.closest('tr').remove();
            // Re-index rows in this table after deletion
            reindexRowsInTable($wrapper);
        }
    }

    /**
     * Re-index all tables
     */
    function reindexTables() {
        $('#provenance-tables-container .provenance-table-wrapper').each(function(tableIndex) {
            var $wrapper = $(this);
            $wrapper.attr('data-table-index', tableIndex);

            // Update notes textarea name
            $wrapper.find('.provenance-notes').attr('name', 'provenance_tables[' + tableIndex + '][notes]');

            // Update delete table button visibility
            var tableCount = $('#provenance-tables-container .provenance-table-wrapper').length;
            var $deleteBtn = $wrapper.find('.delete-provenance-table');
            if (tableCount <= 1) {
                $deleteBtn.remove();
            } else if ($deleteBtn.length === 0) {
                $wrapper.find('.add-provenance-row').after('<button type="button" class="button delete-provenance-table" style="margin-left: 10px;">Delete Table</button>');
            }

            // Re-index rows
            reindexRowsInTable($wrapper);
        });
    }

    /**
     * Re-index row numbers in form field names for a specific table
     */
    function reindexRowsInTable($wrapper) {
        var tableIndex = $wrapper.attr('data-table-index');
        $wrapper.find('.provenance-table-body tr').each(function(rowIndex) {
            $(this).find('input.provenance-col').each(function() {
                var name = $(this).attr('name');
                // Replace with correct table and row index
                var match = name.match(/col(\d+)/);
                if (match) {
                    var colNum = match[1];
                    var newName = 'provenance_tables[' + tableIndex + '][rows][' + rowIndex + '][col' + colNum + ']';
                    $(this).attr('name', newName);
                }
            });
        });
    }

})(jQuery);
