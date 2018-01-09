jQuery(function($) {

    if (!$('#publish').length || !$('#publish').closest('#major-publishing-actions').length || !$('#cf_invalidate_container').length) {
        return;
    }

    // move invalidation container below publish button
    $('#publish').closest('#major-publishing-actions').append($('#cf_invalidate_container'));
    $('#cf_invalidate_container').show();

    // watch action link
    $('#cf_invalidate_container a.action').on('click', function() {
        $(this).hide();
        $('#cfpc-select').show();
        $('#cfpc-select select').focus();
    });

    // watch action link
    $('#cf_invalidate_container select[name="cfpc_purge"]').on('change', function() {
        $('#cfpc-save-default').show();
    });
});