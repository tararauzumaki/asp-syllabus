/**
 * ASP Syllabus - Admin JavaScript
 * Handles repeater field functionality and media uploader
 */

(function($) {
    'use strict';
    
    let rowIndex = 0;
    
    $(document).ready(function() {
        
        // Initialize row index based on existing rows
        updateRowIndexes();
        
        // Initialize sortable
        initSortable();
        initHeadersSortable();
        
        // Update header buttons visibility on load
        updateHeaderButtons();
        
        // Add Row
        $(document).on('click', '.asp-add-row', function(e) {
            e.preventDefault();
            addNewRow();
        });
        
        // Remove Row
        $(document).on('click', '.asp-remove-row', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this row?')) {
                $(this).closest('.asp-row').fadeOut(300, function() {
                    $(this).remove();
                    updateRowNumbers();
                });
            }
        });
        
        // Add Header
        $(document).on('click', '.asp-add-header', function(e) {
            e.preventDefault();
            addNewHeader();
        });
        
        // Remove Header
        $(document).on('click', '.asp-remove-header', function(e) {
            e.preventDefault();
            
            const headerCount = $('.asp-header-item').length;
            
            if (headerCount <= 1) {
                alert('You must have at least 1 column.');
                return;
            }
            
            if (confirm('Are you sure you want to remove this column? This will affect all rows.')) {
                const $item = $(this).closest('.asp-header-item');
                $item.fadeOut(300, function() {
                    $item.remove();
                    updateHeaderButtons();
                });
            }
        });
        
        // Media Uploader
        $(document).on('click', '.asp-upload-pdf', function(e) {
            e.preventDefault();
            
            const button = $(this);
            const inputField = button.siblings('.asp-pdf-url');
            
            // Create media uploader
            const mediaUploader = wp.media({
                title: 'Select PDF File',
                button: {
                    text: 'Select PDF'
                },
                library: {
                    type: 'application/pdf'
                },
                multiple: false
            });
            
            // When a file is selected
            mediaUploader.on('select', function() {
                const attachment = mediaUploader.state().get('selection').first().toJSON();
                
                // Check if selected file is PDF
                if (attachment.mime === 'application/pdf' || attachment.url.toLowerCase().endsWith('.pdf')) {
                    inputField.val(attachment.url);
                    inputField.css('background', '#e8f5e9');
                    
                    // Reset background after animation
                    setTimeout(function() {
                        inputField.css('background', '#f8f9fa');
                    }, 1000);
                } else {
                    alert('Please select a PDF file.');
                }
            });
            
            // Open media uploader
            mediaUploader.open();
        });
        
    });
    
    /**
     * Initialize jQuery UI Sortable for Rows
     */
    function initSortable() {
        $('#asp-rows-container').sortable({
            handle: '.asp-row-handle',
            placeholder: 'ui-sortable-placeholder',
            axis: 'y',
            opacity: 0.8,
            cursor: 'move',
            tolerance: 'pointer',
            update: function(event, ui) {
                updateRowNumbers();
            },
            start: function(event, ui) {
                ui.placeholder.height(ui.item.height());
            }
        });
    }
    
    /**
     * Initialize jQuery UI Sortable for Headers
     */
    function initHeadersSortable() {
        if ($('#asp-headers-container').length) {
            $('#asp-headers-container').sortable({
                handle: '.asp-header-handle',
                axis: 'y',
                opacity: 0.8,
                cursor: 'move',
                tolerance: 'pointer'
            });
        }
    }
    
    /**
     * Add New Header
     */
    function addNewHeader() {
        const headerCount = $('.asp-header-item').length;
        const newHeader = $('<div class="asp-header-item">' +
            '<span class="asp-header-handle dashicons dashicons-move"></span>' +
            '<input type="text" name="asp_headers[]" value="" placeholder="Column Name" required>' +
            '<button type="button" class="button asp-remove-header" title="Remove Column">' +
            '<span class="dashicons dashicons-no-alt"></span>' +
            '</button>' +
            '</div>');
        
        $('#asp-headers-container').append(newHeader);
        newHeader.find('input').focus();
        
        // Update button visibility
        updateHeaderButtons();
        
        // Refresh sortable
        if ($('#asp-headers-container').hasClass('ui-sortable')) {
            $('#asp-headers-container').sortable('refresh');
        }
    }
    
    /**
     * Update Header Buttons Visibility
     */
    function updateHeaderButtons() {
        const headerCount = $('.asp-header-item').length;
        
        if (headerCount <= 1) {
            $('.asp-remove-header').hide();
        } else {
            $('.asp-remove-header').show();
        }
    }
    
    /**
     * Add New Row
     */
    function addNewRow() {
        const template = $('#asp-row-template').html();
        const newRow = template.replace(/\{\{INDEX\}\}/g, rowIndex)
                              .replace(/\{\{ROW_NUMBER\}\}/g, rowIndex + 1);
        
        const $newRow = $(newRow);
        $newRow.addClass('new-row');
        
        $('#asp-rows-container').append($newRow);
        
        // Scroll to new row
        $('html, body').animate({
            scrollTop: $newRow.offset().top - 100
        }, 300);
        
        rowIndex++;
        updateRowNumbers();
        
        // Remove animation class after animation completes
        setTimeout(function() {
            $newRow.removeClass('new-row');
        }, 300);
    }
    
    /**
     * Update Row Indexes
     */
    function updateRowIndexes() {
        const $rows = $('.asp-row');
        rowIndex = $rows.length;
    }
    
    /**
     * Update Row Numbers and Input Names
     */
    function updateRowNumbers() {
        $('.asp-row').each(function(index) {
            const $row = $(this);
            
            // Update row number display
            $row.find('.row-number').text(index + 1);
            
            // Update data-index attribute
            $row.attr('data-index', index);
            
            // Update input names
            $row.find('input[type="text"]').each(function() {
                const $input = $(this);
                const name = $input.attr('name');
                
                if (name) {
                    // Replace the index in the name attribute
                    const newName = name.replace(/asp_rows\[\d+\]/, 'asp_rows[' + index + ']');
                    $input.attr('name', newName);
                }
            });
        });
    }
    
})(jQuery);
