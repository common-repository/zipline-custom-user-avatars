jQuery(function ($) {
    // Add enctype to form with JavaScript as backup
    $('#your-profile').attr('enctype', 'multipart/form-data');
    // Store Zipline Custom User Avatars ID
    var zlcuaID = $('#zl-custom-user-avatars').val();
    // Store Zipline Custom User Avatars src
    var zlcuaSrc = $('#zlcua-preview').find('img').attr('src');
    $('#zlcua-undo-button-existing').hide();
    // Remove Zipline Custom User Avatars
    $('body').on('click', '#zlcua-remove', function (e) {
        e.preventDefault();
        $('#zlcua-original').remove();
        $('#zlcua-remove-button, #zlcua-thumbnail').hide();
        $('#zlcua-preview').find('img:first').hide();
        $('#zlcua-preview').prepend('<img id="zlcua-original" />');
        $('#zlcua-original').attr('src', zlcua_custom.avatar_thumb);
        $('#zl-custom-user-avatars').val("");
        $('#zlcua-original, #zlcua-undo-button').show();
        $('#zl_custom_user_avatars_radio').trigger('click');
    });
    // Undo Zipline Custom User Avatars
    $('body').on('click', '#zlcua-undo', function (e) {
        e.preventDefault();
        $('#zlcua-original').remove();
        $('#zlcua-images').removeAttr('style');
        $('#zlcua-undo-button').hide();
        $('#zlcua-remove-button, #zlcua-thumbnail').show();
        $('#zlcua-preview').find('img:first').attr('src', zlcuaSrc).show();
        $('#zl-custom-user-avatars').val(zlcuaID);
        $('#zl_custom_user_avatars_radio').trigger('click');
    });

    // Store WP Existing User Avatar ID
    var zlcuaEID = $('#zl-custom-user-avatars-existing').val();
    // Store WP Existing User Avatar src
    var zlcuaESrc = $('#zlcua-preview-existing').find('img').attr('src');
    // Remove WP Existing User Avatar
    $('body').on('click', '#zlcua-remove-existing', function (e) {
        e.preventDefault();
        $('#zlcua-original-existing').remove();
        $('#zlcua-remove-button-existing, #zlcua-thumbnail-existing').hide();
        $('#zlcua-preview-existing').find('img:first').hide();
        $('#zlcua-preview-existing').prepend('<img id="zlcua-original-existing" />');
        $('#zlcua-original-existing').attr('src', zlcua_custom.avatar_thumb);
        $('#zl-custom-user-avatars-existing').val("");
        $('#zlcua-original-existing, #zlcua-undo-button-existing').show();
        $('#zl_custom_user_avatars_radio-existing').trigger('click');
    });
    // Undo WP Existing User Avatar
    $('body').on('click', '#zlcua-undo-existing', function (e) {
        e.preventDefault();
        $('#zlcua-original-existing').remove();
        $('#zlcua-images-existing').removeAttr('style');
        $('#zlcua-undo-button-existing').hide();
        $('#zlcua-remove-button-existing, #zlcua-thumbnail-existing').show();
        $('#zlcua-preview-existing').find('img:first').attr('src', zlcuaSrc).show();
        $('#zl-custom-user-avatars-existing').val(zlcuaID);
        $('#zl_custom_user_avatars_radio-existing').trigger('click');
    });
});
