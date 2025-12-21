/**
 * Provenance Table Plugin - JavaScript
 * Handles add/delete row and table functionality for multiple provenance tables
 */

(function($) {
    'use strict';
    var ptFixCount = 0;

    $(document).ready(function() {
        ptInstallValInterceptor();
        initProvenanceTables();
    });

    /**
     * Aggressively normalize textarea values.
     * Your DB + POST are clean; this means something in the admin UI is injecting <br />
     * after the page renders. So we normalize on load, on focus/typing, on tab clicks,
     * and periodically for a few seconds to catch delayed scripts.
     */
    function ptNormalizeValue(v) {
        if (!v) return v;

        // Literal <br> variants
        v = v.replace(/<br\b[^>]*>/gi, "\n");

        // Encoded variants
        v = v.replace(/&lt;br\b[^&]*&gt;/gi, "\n");
        v = v.replace(/&amp;lt;br\b[^&]*&amp;gt;/gi, "\n");

        // Normalize newlines
        v = v.replace(/\r\n/g, "\n").replace(/\r/g, "\n");

        return v;
    }

    /**
     * Intercept BOTH jQuery .val(...) and native textarea.value setters
     * for textareas inside #provenance-tables-container.
     */
    function ptInstallValInterceptor() {
        if ($.fn.__ptValInterceptorInstalled) return;
        $.fn.__ptValInterceptorInstalled = true;

        // --- jQuery .val(...) interceptor (only catches scripts using jQuery) ---
        var _val = $.fn.val;
        $.fn.val = function(value) {
            // Getter
            if (arguments.length === 0) {
                return _val.call(this);
            }

            // Support val(function)
            if (typeof value === 'function') {
                return _val.call(this, value);
            }

            // Only normalize when setting a string into a textarea inside our container
            if (typeof value === 'string') {
                var shouldNormalize = false;

                this.each(function() {
                    if (!this || !this.tagName) return;
                    if (this.tagName.toLowerCase() !== 'textarea') return;
                    if ($(this).closest('#provenance-tables-container').length) {
                        shouldNormalize = true;
                    }
                });

                if (shouldNormalize) {
                    value = ptNormalizeValue(value);
                }
            }

            return _val.call(this, value);
        };

        // --- Native textarea.value interceptor (catches direct assignments) ---
        try {
            if (!HTMLTextAreaElement.prototype.__ptValuePatched) {
                HTMLTextAreaElement.prototype.__ptValuePatched = true;

                var desc = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value');
                if (desc && typeof desc.get === 'function' && typeof desc.set === 'function') {
                    Object.defineProperty(HTMLTextAreaElement.prototype, 'value', {
                        configurable: true,
                        enumerable: desc.enumerable,
                        get: function() {
                            return desc.get.call(this);
                        },
                        set: function(v) {
                            if (typeof v === 'string' && $(this).closest('#provenance-tables-container').length) {
                                v = ptNormalizeValue(v);
                            }
                            return desc.set.call(this, v);
                        }
                    });
                }
            }
        } catch (e) {
            // If this fails in some browsers, timer-based fix still helps.
        }
    }

    function ptFixOneTextarea(el) {
        var $t = $(el);
        var v = $t.val();
        if (!v) return;

        var nv = ptNormalizeValue(v);
        if (nv !== v) {
            ptFixCount++;
            $t.val(nv);
        }
    }

    function ptFixAllTextareas(scope) {
        var $root = scope ? $(scope) : $('#provenance-tables-container');
        $root.find('textarea.provenance-col, textarea.provenance-notes').each(function() {
            ptFixOneTextarea(this);
        });
    }

    function ptBindAntiBrGuards() {
        // Fix immediately
        ptFixAllTextareas('#provenance-tables-container');

        // Fix on focus/typing/paste/change (catches scripts that rewrite on focus)
        $(document).on('focusin input change keyup paste', 'textarea.provenance-col, textarea.provenance-notes', function() {
            ptFixOneTextarea(this);
        });

        // Fix right before submitting the item form
        var $form = $('#provenance-tables-container').closest('form');
        if ($form.length) {
            $form.on('submit', function() {
                ptFixAllTextareas('#provenance-tables-container');
            });
        }

        // Fix when switching tabs / clicking anchors (Omeka uses various tab UIs)
        $(document).on('click', '.tabs a, .ui-tabs-nav a, #section-nav a, a[href^="#"]', function() {
            setTimeout(function() {
                ptFixAllTextareas('#provenance-tables-container');
            }, 50);
            setTimeout(function() {
                ptFixAllTextareas('#provenance-tables-container');
            }, 250);
        });

        // Last resort: keep fixing while the container exists (catches delayed editors)
        var timer = setInterval(function() {
            if (!$('#provenance-tables-container').length) {
                clearInterval(timer);
                return;
            }
            ptFixAllTextareas('#provenance-tables-container');
        }, 500);
    }

    /**
     * Initialize provenance tables
     */
    function initProvenanceTables() {
        // Check if we're on the provenance tab
        if ($('#provenance-tables-container').length === 0) {
            return;
        }

        // Aggressive guards against <br /> being injected into textarea values by other admin scripts
        ptBindAntiBrGuards();

        // Initialize drag and drop for existing tables
        initializeSortable();

        // Bind add table button
        $(document).on('click', '.add-provenance-table', function(e) {
            e.preventDefault();
            addTable();
            ptFixAllTextareas('#provenance-tables-container');
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
            ptFixAllTextareas($wrapper);
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
        var numColumns = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.numColumns : 3;
        var columnNames = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.columnNames : {};
        var columnWidths = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.columnWidths : {};

        // Create new table wrapper
        var $newWrapper = $('<div class="provenance-table-wrapper" data-table-index="' + tableCount + '"></div>');

        // Add notes textarea (NO textinput)
        var $header = $('<div class="provenance-table-header"></div>');
        $header.append('<label>Variety Notes:</label>');
        $header.append('<textarea name="provenance_tables[' + tableCount + '][notes]" class="provenance-notes" rows="3" style="width: 100%;"></textarea>');
        $newWrapper.append($header);

        // Create table
        var $table = $('<table class="provenance-table"></table>');

        // Add header
        var $thead = $('<thead><tr></tr></thead>');
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
        $row.append('<td class="drag-handle" style="text-align: center; cursor: move;"><span class="drag-icon">⋮⋮</span></td>');
        for (var j = 1; j <= numColumns; j++) {
            var $td = $('<td></td>');
            // NO textinput
            var $textarea = $('<textarea class="provenance-col" name="provenance_tables[' + tableCount + '][rows][0][col' + j + ']" rows="2"></textarea>');
            $td.append($textarea);
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

        $newWrapper.append('<hr style="margin: 20px 0; border: 1px solid #ccc;">');

        $container.append($newWrapper);

        reindexTables();
        initializeSortable();

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
            reindexTables();
        }
    }

    /**
     * Add a new row to a specific table
     */
    function addRow($wrapper) {
        var $tbody = $wrapper.find('.provenance-table-body');
        var tableIndex = $wrapper.attr('data-table-index');
        var numColumns = (typeof ProvenanceTableConfig !== 'undefined') ? ProvenanceTableConfig.numColumns : 3;

        var $newRow = $('<tr></tr>');

        var $dragTd = $('<td class="drag-handle" style="text-align: center; cursor: move;"></td>');
        $dragTd.append('<span class="drag-icon">⋮⋮</span>');
        $newRow.append($dragTd);

        for (var i = 1; i <= numColumns; i++) {
            var $td = $('<td></td>');
            // NO textinput
            var $textarea = $('<textarea class="provenance-col" name="provenance_tables[' + tableIndex + '][rows][0][col' + i + ']" rows="2"></textarea>');
            $td.append($textarea);
            $newRow.append($td);
        }

        var $actionTd = $('<td style="text-align: center;"></td>');
        var $deleteBtn = $('<button type="button" class="button delete-provenance-row">Delete Row</button>');
        $actionTd.append($deleteBtn);
        $newRow.append($actionTd);

        $tbody.prepend($newRow);

        reindexRowsInTable($wrapper);

        $newRow.find('textarea').first().focus();
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

            $wrapper.find('.provenance-notes').attr('name', 'provenance_tables[' + tableIndex + '][notes]');

            var tableCount = $('#provenance-tables-container .provenance-table-wrapper').length;
            var $deleteBtn = $wrapper.find('.delete-provenance-table');
            if (tableCount <= 1) {
                $deleteBtn.remove();
            } else if ($deleteBtn.length === 0) {
                $wrapper.find('.add-provenance-row').after('<button type="button" class="button delete-provenance-table" style="margin-left: 10px;">Delete Table</button>');
            }

            reindexRowsInTable($wrapper);
        });
    }

    /**
     * Re-index row numbers in form field names for a specific table
     */
    function reindexRowsInTable($wrapper) {
        var tableIndex = $wrapper.attr('data-table-index');
        $wrapper.find('.provenance-table-body tr').each(function(rowIndex) {
            $(this).find('textarea.provenance-col').each(function() {
                var name = $(this).attr('name');
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