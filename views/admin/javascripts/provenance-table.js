/**
 * Provenance Table Plugin - JavaScript
 * Handles dynamic table row management
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initProvenanceTable();
    });

    /**
     * Initialize provenance table functionality
     */
    function initProvenanceTable() {
        // Add new entry button
        $('#add-provenance-entry').on('click', function(e) {
            e.preventDefault();
            addProvenanceEntry();
        });

        // Delete entry buttons
        $(document).on('click', '.delete-entry', function(e) {
            e.preventDefault();
            deleteProvenanceEntry($(this));
        });

        // Make table rows sortable
        if (typeof $.fn.sortable !== 'undefined') {
            $('#provenance-table-body').sortable({
                handle: '.drag-handle',
                axis: 'y',
                cursor: 'move',
                opacity: 0.7,
                helper: function(e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function(index) {
                        $(this).width($originals.eq(index).width());
                    });
                    return $helper;
                },
                update: function(event, ui) {
                    updateEntryOrder();
                }
            });
        }

        // Update order numbers on page load
        updateEntryOrder();
    }

    /**
     * Add a new provenance entry row
     */
    function addProvenanceEntry() {
        var index = parseInt($('#provenance-entry-index').val());
        var tableBody = $('#provenance-table-body');

        var rowHtml = `
            <tr class="provenance-entry">
                <td class="drag-handle">
                    <span class="order-number">${index + 1}</span>
                    <input type="hidden" name="provenance[${index}][entry_order]" value="${index}" class="entry-order" />
                </td>
                <td><input type="text" name="provenance[${index}][owner_name]" value="" class="textinput" required /></td>
                <td><input type="text" name="provenance[${index}][date_from]" value="" class="textinput" placeholder="YYYY or YYYY-MM-DD" /></td>
                <td><input type="text" name="provenance[${index}][date_to]" value="" class="textinput" placeholder="YYYY or YYYY-MM-DD" /></td>
                <td><input type="text" name="provenance[${index}][location]" value="" class="textinput" /></td>
                <td><input type="text" name="provenance[${index}][source]" value="" class="textinput" /></td>
                <td>
                    <select name="provenance[${index}][transaction_type]" class="textinput">
                        <option value="">Select...</option>
                        <option value="Purchase">Purchase</option>
                        <option value="Gift">Gift</option>
                        <option value="Inheritance">Inheritance</option>
                        <option value="Loan">Loan</option>
                        <option value="Auction">Auction</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </td>
                <td><input type="text" name="provenance[${index}][price]" value="" class="textinput" placeholder="e.g., $100 USD" /></td>
                <td><textarea name="provenance[${index}][notes]" class="textinput" rows="2"></textarea></td>
                <td><button type="button" class="delete-entry button">Delete</button></td>
            </tr>
        `;

        tableBody.append(rowHtml);
        $('#provenance-entry-index').val(index + 1);

        // Focus on the owner name field
        tableBody.find('tr:last input[name*="owner_name"]').focus();

        updateEntryOrder();
    }

    /**
     * Delete a provenance entry row
     */
    function deleteProvenanceEntry($button) {
        if (confirm('Are you sure you want to delete this provenance entry?')) {
            $button.closest('tr').remove();
            updateEntryOrder();
        }
    }

    /**
     * Update entry order numbers and hidden fields
     */
    function updateEntryOrder() {
        $('#provenance-table-body tr.provenance-entry').each(function(index) {
            $(this).find('.order-number').text(index + 1);
            $(this).find('.entry-order').val(index);

            // Update all input names to reflect new index
            $(this).find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name && name.indexOf('provenance[') === 0) {
                    var fieldName = name.match(/\[([^\]]+)\]$/);
                    if (fieldName) {
                        $(this).attr('name', 'provenance[' + index + '][' + fieldName[1] + ']');
                    }
                }
            });
        });
    }

    /**
     * Validate provenance entries before form submission
     */
    $('form#item-form').on('submit', function(e) {
        var valid = true;
        var emptyRows = 0;

        $('#provenance-table-body tr.provenance-entry').each(function() {
            var ownerName = $(this).find('input[name*="owner_name"]').val().trim();

            if (ownerName === '') {
                emptyRows++;
            }
        });

        // Allow submission if all rows are empty or if at least one has owner name
        if (emptyRows > 0 && emptyRows < $('#provenance-table-body tr.provenance-entry').length) {
            // Some rows have data, some don't - this is okay
            return true;
        }

        return valid;
    });

})(jQuery);
