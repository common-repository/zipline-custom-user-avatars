jQuery(function ($) {
    // Show size info only if allow uploads is checked
    $('#zl_custom_user_avatars_allow_upload').change(function () {
        $('#zlcua-contributors-subscribers').slideToggle($('#zl_custom_user_avatars_allow_upload').is(':checked'));
    });
    // Show resize info only if resize uploads is checked
    $('#zl_custom_user_avatars_resize_upload').change(function () {
        $('#zlcua-resize-sizes').slideToggle($('#zl_custom_user_avatars_resize_upload').is(':checked'));
    });
    // Hide Gravatars if disable Gravatars is checked
    $('#zl_custom_user_avatars_disable_gravatar').change(function () {
        if ($('#wp-avatars').length) {
            $('#wp-avatars, #avatar-rating').slideToggle(!$('#zl_custom_user_avatars_disable_gravatar').is(':checked'));
            $('#zl_custom_user_avatars_radio').trigger('click');
        }
    });
    // Add size slider
    $('#zlcua-slider').slider({
        value: parseInt(zlcua_admin.upload_size_limit),
        min: 0,
        max: parseInt(zlcua_admin.max_upload_size),
        step: 1,
        slide: function (event, ui) {
            $('#zl_custom_user_avatars_upload_size_limit').val(ui.value);
            $('#zlcua-readable-size').html(Math.floor(ui.value / 1024) + 'KB');
            $('#zlcua-readable-size-error').hide();
            $('#zlcua-readable-size').removeClass('zlcua-error');
        }
    });
    // Update readable size on keyup
    $('#zl_custom_user_avatars_upload_size_limit').keyup(function () {
        var zlcuaUploadSizeLimit = $(this).val();
        zlcuaUploadSizeLimit = zlcuaUploadSizeLimit.replace(/\D/g, "");
        $(this).val(zlcuaUploadSizeLimit);
        $('#zlcua-readable-size').html(Math.floor(zlcuaUploadSizeLimit / 1024) + 'KB');
        $('#zlcua-readable-size-error').toggle(zlcuaUploadSizeLimit > parseInt(zlcua_admin.max_upload_size));
        $('#zlcua-readable-size').toggleClass('zlcua-error', zlcuaUploadSizeLimit > parseInt(zlcua_admin.max_upload_size));
    });
    $('#zl_custom_user_avatars_upload_size_limit').val($('#zlcua-slider').slider('value'));
});
