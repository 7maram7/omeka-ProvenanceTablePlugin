/**
 * Provenance Table Plugin - Public JavaScript
 * Repositions the provenance section to appear after metadata but before files
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', repositionProvenance);
    } else {
        repositionProvenance();
    }

    function repositionProvenance() {
        var provenanceSection = document.getElementById('provenance-section');

        if (!provenanceSection) {
            return; // No provenance section on this page
        }

        // Try to find the item files section (multiple possible selectors)
        var filesSection = document.getElementById('item-files') ||
                          document.getElementById('itemfiles') ||
                          document.querySelector('.item-file') ||
                          document.querySelector('#item-images');

        if (filesSection) {
            // Insert provenance before the files section
            filesSection.parentNode.insertBefore(provenanceSection, filesSection);
        } else {
            // If no files section found, try to insert after metadata
            var metadataSection = document.getElementById('item-metadata') ||
                                 document.getElementById('metadata') ||
                                 document.querySelector('.element-set');

            if (metadataSection) {
                // Insert after the last metadata element-set
                var allElementSets = document.querySelectorAll('.element-set');
                if (allElementSets.length > 0) {
                    var lastElementSet = allElementSets[allElementSets.length - 1];
                    lastElementSet.parentNode.insertBefore(provenanceSection, lastElementSet.nextSibling);
                } else {
                    metadataSection.parentNode.insertBefore(provenanceSection, metadataSection.nextSibling);
                }
            }
        }
    }
})();
