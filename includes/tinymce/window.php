<?php
/**
 * TinyMCE modal window.
 *
 * @package Zipline Custom User Avatars
 */
/**
 * @uses  get_users()
 */
if ( ! defined( 'ABSPATH' ) ) {
    die( 'You are not allowed to call this page directly.' );
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php
        _e( 'Zipline Custom User Avatars', 'zl-custom-user-avatars' ); ?></title>
    <meta http-equiv="Content-Type" content="<?php
    bloginfo( 'html_type' ); ?>; charset=<?php
    echo get_option( 'blog_charset' ); ?>"/>
    <base target="_self"/>
    <script type="text/javascript" src="<?php
    echo site_url(); ?>/wp-includes/js/jquery/jquery.js"></script>
    <script type="text/javascript" src="<?php
    echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <script type="text/javascript" src="<?php
    echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
    <script type="text/javascript">
        function zlcuaInsertAvatar() {
            // Custom shortcode values
            var shortcode,
                closing_tag,
                user = document.getElementById('zl_custom_user_avatars_user').value,
                size = document.getElementById('zl_custom_user_avatars_size').value,
                size_number = document.getElementById('zl_custom_user_avatars_size_number').value,
                align = document.getElementById('zl_custom_user_avatars_align').value,
                link = document.getElementById('zl_custom_user_avatars_link').value,
                link_external = document.getElementById('zl_custom_user_avatars_link_external').value,
                target = document.getElementById('zl_custom_user_avatars_target').value,
                caption = document.getElementById('zl_custom_user_avatars_caption').value;
            // Add tag to shortcode only if not blank
            var user_tag = (user != "") ? ' user="' + user + '"' : "";
            var size_tag = (size != "" && size_number == "") ? ' size="' + size + '"' : "";
            size_tag = (size_number != "") ? ' size="' + size_number + '"' : size_tag;
            var align_tag = (align != "") ? ' align="' + align + '"' : "";
            var link_tag = (link != "" && link != 'custom-url' && link_external == "") ? ' link="' + link + '"' : "";
            link_tag = (link_external != "") ? ' link="' + link_external + '"' : link_tag;
            var target_tag = document.getElementById('zl_custom_user_avatars_target').checked && (link_tag != "") ? ' target="' + target + '"' : "";
            // Assemble the shortcode
            closing_tag = (caption != "") ? "]" + caption + "[/avatar]" : " /]";
            shortcode = "<p>[avatar" + user_tag + size_tag + align_tag + link_tag + target_tag + closing_tag + "</p>";
            // Insert into Visual Editor
            if (window.tinyMCE) {
                var tmce_ver = window.tinyMCE.majorVersion;
                if (tmce_ver >= "4") {
                    window.tinyMCE.execCommand('mceInsertContent', false, shortcode);
                } else {
                    window.tinyMCE.execInstanceCommand(window.tinyMCE.activeEditor.id, 'mceInsertContent', false, shortcode);
                }
                tinyMCEPopup.editor.execCommand('mceRepaint');
                tinyMCEPopup.close();
            }
            return;
        }

        function zlcuaInsertAvatarUpload() {
            // Upload shortcode
            var shortcode = "<p>[avatar_upload /]</p>";
            // Insert into Visual Editor
            if (window.tinyMCE) {
                var tmce_ver = window.tinyMCE.majorVersion;
                if (tmce_ver >= "4") {
                    window.tinyMCE.execCommand('mceInsertContent', false, shortcode);
                } else {
                    window.tinyMCE.execInstanceCommand(window.tinyMCE.activeEditor.id, 'mceInsertContent', false, shortcode);
                }
                tinyMCEPopup.editor.execCommand('mceRepaint');
                tinyMCEPopup.close();
            }
            return;
        }

        jQuery(function ($) {
            // Show link input
            $('#zl_custom_user_avatars_link').change(function () {
                $('#zl_custom_user_avatars_link_external_section').toggle($('#zl_custom_user_avatars_link').val() == 'custom-url');
            });
            // Show size input
            $('#zl_custom_user_avatars_size').change(function () {
                $('#zl_custom_user_avatars_size_number_section').toggle($('#zl_custom_user_avatars_size').val() == 'custom');
            });

            $("#zlcua-tabs li a").click(function () {
                tab_id = $(this).attr('href');
                if (tab_id == '#zlcua')
                    $("#zlcua-upload").hide();
                else
                    $('#zlcua').hide();
                $(tab_id).show();
            })
            // Tabs
        });
    </script>
    <style type="text/css">
        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        ul li {
            float: left;
        }

        ul li a {
            float: left;
            padding: 2px 5px;
            background: #ddd;
            border: 1px solid #eee;
            border-bottom: 0;
            display: block;
            font-weight: 700;
            outline: none;
            text-decoration: none;
        }

        ul li.ui-tabs-active a {
            background: #fff;
        }

        form {
            clear: both;
            background: #fff;
            border: 1px solid #eee;
        }

        p, h4 {
            margin: 0;
            padding: 12px 0 0;
        }

        h4.center {
            text-align: center;
        }

        label {
            width: 150px;
            margin-right: 3px;
            display: inline-block;
            text-align: right;
            vertical-align: top;
        }

        .mceActionPanel {
            padding: 7px 0 12px;
            text-align: center;
        }

        .mceActionPanel #insert {
            float: none;
            width: 180px;
            margin: 0 auto;
        }

        #zl_custom_user_avatars_size_number_section, #zl_custom_user_avatars_link_external_section {
            display: none;
        }
    </style>
</head>
<body id="link" class="wp-core-ui" onload="document.body.style.display='';" style="display:none;">
<div id="zlcua-tabs">
    <ul>
        <li><a href="#zlcua"><?php
                _e( 'Profile Picture', 'zl-custom-user-avatars' ); ?></a></li>
        <li><a href="#zlcua-upload"><?php
                _e( 'Upload', 'zl-custom-user-avatars' ); ?></a></li>
    </ul>
    <form name="wpUserAvatar" action="#">
        <div id="zlcua">
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_user' ); ?>"><strong><?php
                        _e( 'User Name', 'zl-custom-user-avatars' ); ?>:</strong></label>
                <select id="<?php
                esc_attr_e( 'zl_custom_user_avatars_user' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_user' ); ?>">
                    <option value=""></option>
                    <?php
                    $users = get_users();
                    foreach ( $users as $user ) : ?>
                        <option value="<?php
                        echo $user->user_login; ?>"><?php
                            echo $user->display_name; ?></option>
                    <?php
                    endforeach; ?>
                </select>
            </p>
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_size' ); ?>"><strong><?php
                        _e( 'Size' ); ?>:</strong></label>
                <select id="<?php
                esc_attr_e( 'zl_custom_user_avatars_size' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_size' ); ?>">
                    <option value=""></option>
                    <option value="original"><?php
                        _e( 'Original Size', 'zl-custom-user-avatars' ); ?></option>
                    <option value="large"><?php
                        _e( 'Large', 'zl-custom-user-avatars' ); ?></option>
                    <option value="medium"><?php
                        _e( 'Medium', 'zl-custom-user-avatars' ); ?></option>
                    <option value="thumbnail"><?php
                        _e( 'Thumbnail', 'zl-custom-user-avatars' ); ?></option>
                    <option value="custom"><?php
                        _e( 'Custom', 'zl-custom-user-avatars' ); ?></option>
                </select>
            </p>
            <p id="<?php
            esc_attr_e( 'zl_custom_user_avatars_size_number_section' ); ?>">
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_size_number' ); ?>"><?php
                    _e( 'Size', 'zl-custom-user-avatars' ); ?>:</label>
                <input type="text" size="8" id="<?php
                esc_attr_e( 'zl_custom_user_avatars_size_number' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_size' ); ?>" value=""/>
            </p>
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_align' ); ?>"><strong><?php
                        _e( 'Alignment', 'zl-custom-user-avatars' ); ?>:</strong></label>
                <select id="<?php
                esc_attr_e( 'zl_custom_user_avatars_align' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_align' ); ?>">
                    <option value=""></option>
                    <option value="center"><?php
                        _e( 'Center', 'zl-custom-user-avatars' ); ?></option>
                    <option value="left"><?php
                        _e( 'Left', 'zl-custom-user-avatars' ); ?></option>
                    <option value="right"><?php
                        _e( 'Right', 'zl-custom-user-avatars' ); ?></option>
                </select>
            </p>
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_link' ); ?>"><strong><?php
                        _e( 'Link To' ); ?>:</strong></label>
                <select id="<?php
                esc_attr_e( 'zl_custom_user_avatars_link' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_link' ); ?>">
                    <option value=""></option>
                    <option value="file"><?php
                        _e( 'Image File', 'zl-custom-user-avatars' ); ?></option>
                    <option value="attachment"><?php
                        _e( 'Attachment Page', 'zl-custom-user-avatars' ); ?></option>
                    <option value="custom-url"><?php
                        _e( 'Custom URL', 'zl-custom-user-avatars' ); ?></option>
                </select>
            </p>
            <p id="<?php
            esc_attr_e( 'zl_custom_user_avatars_link_external_section' ); ?>">
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_link_external' ); ?>"><?php
                    _e( 'URL', 'zl-custom-user-avatars' ); ?>:</label>
                <input type="text" size="36" id="<?php
                esc_attr_e( 'zl_custom_user_avatars_link_external' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_link_external' ); ?>" value=""/>
            </p>
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_target' ); ?>"></label>
                <input type="checkbox" id="<?php
                esc_attr_e( 'zl_custom_user_avatars_target' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_target' ); ?>" value="_blank"/> <strong><?php
                    _e( 'Open link in a new window', 'zl-custom-user-avatars' ); ?></strong>
            </p>
            <p>
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_caption' ); ?>"><strong><?php
                        _e( 'Caption', 'zl-custom-user-avatars' ); ?>:</strong></label>
                <textarea cols="36" rows="2" id="<?php
                esc_attr_e( 'zl_custom_user_avatars_caption' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_size' ); ?>"></textarea>
            </p>
            <div class="mceActionPanel">
                <input type="submit" id="insert" class="button-primary" name="insert" value="<?php
                _e( 'Insert into Post' ); ?>" onclick="zlcuaInsertAvatar();"/>
            </div>
        </div>
        <div id="zlcua-upload" style="display:none;">
            <p id="<?php
            esc_attr_e( 'zl_custom_user_avatars_upload' ); ?>">
                <label for="<?php
                esc_attr_e( 'zl_custom_user_avatars_upload' ); ?>"><strong><?php
                        _e( 'Upload', 'zl-custom-user-avatars' ); ?>:</strong></label>
                <input type="text" size="36" id="<?php
                esc_attr_e( 'zl_custom_user_avatars_upload' ); ?>" name="<?php
                esc_attr_e( 'zl_custom_user_avatars_upload' ); ?>" value="<?php
                esc_attr_e( '[avatar_upload]' ); ?>" readonly="readonly"/>
            </p>
            <div class="mceActionPanel">
                <input type="submit" id="insert" class="button-primary" name="insert" value="<?php
                _e( 'Insert into Post', 'zl-custom-user-avatars' ); ?>" onclick="zlcuaInsertAvatarUpload();"/>
            </div>
        </div>
    </form>
</div>
</body>
</html>
