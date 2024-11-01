(function ($) {
    var id;
    wp.media.wpUserAvatar = {

        get: function () {
            return wp.media.view.settings.post.wpUserAvatarId
        },
        set: function (a) {
            var b = wp.media.view.settings;
            b.post.wpUserAvatarId = a;
            b.post.wpUserAvatarSrc = $('div.attachment-info').find('img').attr('src');
            if (b.post.wpUserAvatarId && b.post.wpUserAvatarSrc) {
                $('#zl-custom-user-avatars' + id).val(b.post.wpUserAvatarId);
                $('#zlcua-images' + id + ', #zlcua-undo-button' + id).show();
                $('#zlcua-preview' + id).find('img').attr('src', b.post.wpUserAvatarSrc).removeAttr('height', "");
                $('#zlcua-remove-button' + id + ', #zlcua-thumbnail' + id).hide();
                $('#zl_custom_user_avatars_radio').trigger('click')
            }
            wp.media.wpUserAvatar.frame().close()
        },
        frame: function () {
            if (this._frame) {
                return this._frame
            }
            this._frame = wp.media({
                library: {
                    type: 'image'
                },
                multiple: false,
                title: $('#zlcua-add' + id).data('title')
            });
            this._frame.on('open', function () {
                var a = $('#zl-custom-user-avatars' + id).val();
                if (a == "") {
                    $('div.media-router').find('a:first').trigger('click')
                } else {
                    var b = this.state().get('selection');
                    attachment = wp.media.attachment(a);
                    attachment.fetch();
                    b.add(attachment ? [attachment] : [])
                }
            }, this._frame);
            this._frame.state('library').on('select', this.select);
            return this._frame
        },
        select: function (a) {
            selection = this.get('selection').single();
            wp.media.wpUserAvatar.set(selection ? selection.id : -1)
        },
        init: function () {
            $('body').on('click', '#zlcua-add', function (e) {
                e.preventDefault();
                e.stopPropagation();
                id = '';
                wp.media.wpUserAvatar.frame().open()
            })
            $('body').on('click', '#zlcua-add-existing', function (e) {
                e.preventDefault();
                e.stopPropagation();
                id = '-existing';
                wp.media.wpUserAvatar.frame().open()
            })
        }
    }
})(jQuery);
jQuery(function ($) {
    if (typeof (wp) != 'undefined') {
        wp.media.wpUserAvatar.init()
    }
    $('#your-profile').attr('enctype', 'multipart/form-data');
    var a = $('#zl-custom-user-avatars').val();
    var b = $('#zlcua-preview').find('img').attr('src');
    $('body').on('click', '#zlcua-remove', function (e) {
        e.preventDefault();
        $('#zlcua-original').remove();
        $('#zlcua-remove-button, #zlcua-thumbnail').hide();
        $('#zlcua-preview').find('img:first').hide();
        $('#zlcua-preview').prepend('<img id="zlcua-original" />');
        $('#zlcua-original').attr('src', zlcua_custom.avatar_thumb);
        $('#zl-custom-user-avatars').val("");
        $('#zlcua-original, #zlcua-undo-button').show();
        $('#zl_custom_user_avatars_radio').trigger('click')
    });
    $('body').on('click', '#zlcua-undo', function (e) {
        e.preventDefault();
        $('#zlcua-original').remove();
        $('#zlcua-images').removeAttr('style');
        $('#zlcua-undo-button').hide();
        $('#zlcua-remove-button, #zlcua-thumbnail').show();
        $('#zlcua-preview').find('img:first').attr('src', b).show();
        $('#zl-custom-user-avatars').val(a);
        $('#zl_custom_user_avatars_radio').trigger('click')
    })
});
jQuery(function ($) {
    if (typeof (wp) != 'undefined') {
        wp.media.wpUserAvatar.init()
    }
    $('#your-profile').attr('enctype', 'multipart/form-data');
    var a = $('#zl-custom-user-avatars-existing').val();
    var b = $('#zlcua-preview-existing').find('img').attr('src');
    $('#zlcua-undo-button-existing').hide();
    $('body').on('click', '#zlcua-remove-existing', function (e) {
        e.preventDefault();
        $('#zlcua-original-existing').remove();
        $('#zlcua-remove-button-existing, #zlcua-thumbnail-existing').hide();
        $('#zlcua-preview-existing').find('img:first').hide();
        $('#zlcua-preview-existing').prepend('<img id="zlcua-original-existing" />');
        $('#zlcua-original-existing').attr('src', zlcua_custom.avatar_thumb);
        $('#zl-custom-user-avatars-existing').val("");
        $('#zlcua-original-existing, #zlcua-undo-button-existing').show();
        $('#zl_custom_user_avatars_radio').trigger('click')
    });
    $('body').on('click', '#zlcua-undo-existing', function (e) {
        e.preventDefault();
        $('#zlcua-original-existing').remove();
        $('#zlcua-images-existing').removeAttr('style');
        $('#zlcua-undo-button-existing').hide();
        $('#zlcua-remove-button-existing, #zlcua-thumbnail-existing').show();
        $('#zlcua-preview-existing').find('img:first').attr('src', b).show();
        $('#zl-custom-user-avatars-existing').val(a);
        $('#zl_custom_user_avatars_radio').trigger('click')
    })
});
