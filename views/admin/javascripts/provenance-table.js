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
        // Check if configuration is available
        if (typeof ProvenanceTableConfig === 'undefined' || !ProvenanceTableConfig) {
            console.log('ProvenanceTableConfig not found - plugin may not be configured');
            return;
        }

        // Get current item type ID from the page
        var itemTypeId = getCurrentItemTypeId();

        if (!itemTypeId) {
            console.log('Item type ID not found - may be adding new item');
            // Try to find field anyway using fallback method
            itemTypeId = 'fallback';
        }

        console.log('Item Type ID:', itemTypeId);
        console.log('Configuration:', ProvenanceTableConfig);

        // Get the element ID for this item type
        var elementId = null;
        if (itemTypeId !== 'fallback' && ProvenanceTableConfig[itemTypeId]) {
            elementId = ProvenanceTableConfig[itemTypeId];
        }

        if (!elementId) {
            console.log('No element configured for this item type');
            return;
        }

        console.log('Element ID to transform:', elementId);

        // Find the field by element ID
        var $provenanceField = findProvenanceFieldByElementId(elementId);

        if (!$provenanceField || $provenanceField.length === 0) {
            console.log('Provenance field not found for element ID:', elementId);
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
     * Get current item type ID from the page
     */
    function getCurrentItemTypeId() {
        // Try to get from the item type select dropdown
        var $itemTypeSelect = $('#item_type_id, select[name="item_type_id"]');
        if ($itemTypeSelect.length > 0 && $itemTypeSelect.val()) {
            return $itemTypeSelect.val();
        }

        // Try to get from a hidden field (if editing)
        var $hiddenItemType = $('input[name="item_type_id"]');
        if ($hiddenItemType.length > 0 && $hiddenItemType.val()) {
            return $hiddenItemType.val();
        }

        // Try to get from body class (Omeka sometimes adds this)
        var bodyClass = $('body').attr('class');
        if (bodyClass) {
            var match = bodyClass.match(/item-type-(\d+)/);
            if (match) {
                return match[1];
            }
        }

        return null;
    }

    /**
     * Find the provenance field textarea by element ID
     */
    function findProvenanceFieldByElementId(elementId) {
        // In Omeka, element fields have IDs like: Elements-{element_id}-0-text
        // Try multiple patterns
        var selectors = [
            '#Elements-' + elementId + '-0-text',
            'textarea[id^="Elements-' + elementId + '-"]',
            'textarea[name^="Elements[' + elementId + ']"]',
            'textarea[id*="Elements-' + elementId + '"]'
        ];

        for (var i = 0; i < selectors.length; i++) {
            var $field = $(selectors[i]);
            if ($field.length > 0) {
                console.log('Found field using selector:', selectors[i]);
                return $field.first();
            }
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
