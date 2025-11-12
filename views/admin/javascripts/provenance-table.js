/**
 * Provenance Table Plugin - JavaScript
 * Transforms the Provenance textarea into a structured table
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initProvenanceTable();
    });

    /**
     * Initialize provenance table transformation
     */
    function initProvenanceTable() {
        // Find the Provenance field textarea
        // It's typically in the Item Type Metadata section with a label containing "Provenance"
        var $provenanceField = findProvenanceField();

        if (!$provenanceField || $provenanceField.length === 0) {
            console.log('Provenance field not found');
            return;
        }

        console.log('Provenance field found, transforming to table...');

        // Get the parent element (usually the .field div)
        var $fieldContainer = $provenanceField.closest('.field');

        if ($fieldContainer.length === 0) {
            $fieldContainer = $provenanceField.parent();
        }

        // Hide the original textarea
        $provenanceField.hide();

        // Parse existing data if any
        var existingData = parseProvenanceData($provenanceField.val());

        // Create the table interface
        var $tableHtml = createTableInterface(existingData);

        // Insert table after the textarea
        $provenanceField.after($tableHtml);

        // Bind events
        bindTableEvents();

        // Bind form submission to convert table to JSON
        bindFormSubmission($provenanceField);
    }

    /**
     * Find the Provenance field textarea
     */
    function findProvenanceField() {
        // Try multiple selectors to find the Provenance field
        var selectors = [
            'textarea[name*="Provenance"]',
            'textarea[id*="provenance"]',
            'textarea[id*="Provenance"]',
            '.field:has(label:contains("Provenance")) textarea',
            '#item-type-metadata textarea[name*="Provenance"]'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var $field = $(selectors[i]);
            if ($field.length > 0) {
                return $field.first();
            }
        }

        // Fallback: look for any label with "Provenance" and find its associated textarea
        var $label = $('label:contains("Provenance")');
        if ($label.length > 0) {
            var forAttr = $label.attr('for');
            if (forAttr) {
                return $('#' + forAttr);
            }
            // Try to find textarea in same container
            return $label.closest('.field').find('textarea').first();
        }

        return null;
    }

    /**
     * Parse provenance data from textarea
     */
    function parseProvenanceData(text) {
        if (!text || text.trim() === '') {
            return [];
        }

        try {
            var data = JSON.parse(text);
            if (Array.isArray(data)) {
                return data;
            }
        } catch (e) {
            // Not JSON, return empty array (old format will be preserved in hidden field)
            console.log('Existing data is not JSON format');
        }

        return [];
    }

    /**
     * Create table interface HTML
     */
    function createTableInterface(data) {
        var html = '<div class="provenance-table-container">';
        html += '<table class="provenance-table" id="provenance-table">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>No.</th>';
        html += '<th>Auction or Collection</th>';
        html += '<th>Date</th>';
        html += '<th>Characteristics</th>';
        html += '<th>Actions</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody id="provenance-table-body">';

        // Add existing rows
        if (data && data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                html += createRowHTML(i + 1, data[i]);
            }
        } else {
            // Add one empty row to start
            html += createRowHTML(1, {});
        }

        html += '</tbody>';
        html += '</table>';
        html += '<button type="button" class="button add-provenance-row">Add Row</button>';
        html += '</div>';

        return html;
    }

    /**
     * Create a single table row HTML
     */
    function createRowHTML(number, data) {
        data = data || {};
        var html = '<tr>';
        html += '<td class="row-number">' + number + '</td>';
        html += '<td><input type="text" class="textinput provenance-auction" value="' + escapeHtml(data.auction || '') + '" /></td>';
        html += '<td><input type="text" class="textinput provenance-date" value="' + escapeHtml(data.date || '') + '" /></td>';
        html += '<td><input type="text" class="textinput provenance-characteristics" value="' + escapeHtml(data.characteristics || '') + '" /></td>';
        html += '<td><button type="button" class="button delete-provenance-row">Delete</button></td>';
        html += '</tr>';
        return html;
    }

    /**
     * Bind table events
     */
    function bindTableEvents() {
        // Add row button
        $(document).on('click', '.add-provenance-row', function(e) {
            e.preventDefault();
            addRow();
        });

        // Delete row button
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
        var rowCount = $tbody.find('tr').length;
        var $newRow = $(createRowHTML(rowCount + 1, {}));
        $tbody.append($newRow);
        updateRowNumbers();
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
            updateRowNumbers();
        }
    }

    /**
     * Update row numbers after add/delete
     */
    function updateRowNumbers() {
        $('#provenance-table-body tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    /**
     * Bind form submission
     */
    function bindFormSubmission($provenanceField) {
        // Find the item form
        var $form = $provenanceField.closest('form');

        if ($form.length > 0) {
            $form.on('submit', function() {
                // Convert table data to JSON and store in hidden textarea
                var tableData = extractTableData();
                var jsonData = JSON.stringify(tableData);
                $provenanceField.val(jsonData);
            });
        }
    }

    /**
     * Extract data from table
     */
    function extractTableData() {
        var data = [];
        $('#provenance-table-body tr').each(function() {
            var $row = $(this);
            var rowData = {
                auction: $row.find('.provenance-auction').val().trim(),
                date: $row.find('.provenance-date').val().trim(),
                characteristics: $row.find('.provenance-characteristics').val().trim()
            };

            // Only add row if at least one field is filled
            if (rowData.auction || rowData.date || rowData.characteristics) {
                data.push(rowData);
            }
        });
        return data;
    }

    /**
     * Escape HTML for display
     */
    function escapeHtml(text) {
        if (!text) return '';
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

})(jQuery);
