=== Zipline Custom User Avatars | User Profile Pictures ===
Contributors:
Tags: user profile, avatar, gravatar, author image, author photo, author avatar, bbPress, profile avatar, profile image, user avatar, user image, user photo, widget, zipline
Requires at least: 4.0
Tested up to: 5.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Use any image from your WordPress Media Library as a custom user avatar or user profile picture. Add your own Default Avatar.
== Description ==
WordPress currently only allows you to use custom avatars that are uploaded through [Gravatar](http://gravatar.com/). **Zipline Custom User Avatars** enables you to use any photo uploaded into your Media Library as an avatar. This means you use the same uploader and library as your posts. No extra folders or image editing functions are necessary.
**ZL Custom User Avatars** also lets you:
* Upload your own Default Avatar in your Zipline Custom User Avatars settings.
* Show the user's [Gravatar](http://gravatar.com/) avatar or Default Avatar if the user doesn't have a Zipline Custom User Avatars image.
* Disable [Gravatar](http://gravatar.com/) avatars and use only local avatars.
* Use the <code>[avatar_upload]</code> shortcode to add a standalone uploader to a front page or widget. This uploader is only visible to logged-in users.
* Use the <code>[avatar]</code> shortcode in your posts. These shortcodes will work with any theme, whether it has avatar support or not.
* Allow Contributors and Subscribers to upload their own avatars.
* Limit upload file size and image dimensions for Contributors and Subscribers.
== Installation ==
1. Download, install, and activate the Zipline Custom User Avatars plugin.
2. On your profile edit page, click "Edit Image".
3. Choose an image, then click "Select Image".
4. Click "Update Profile".
5. Upload your own Default Avatar in your Zipline Custom User Avatars settings (optional). You can also allow Contributors & Subscribers to upload avatars and disable Gravatar.
6. Choose a theme that has avatar support. In your theme, manually replace <code>get_avatar</code> with <code>get_zl_custom_user_avatars</code> or leave <code>get_avatar</code> as-is. [Read about the differences here](http://wordpress.org/extend/plugins/zl-custom-user-avatars/faq/).
7. You can also use the <code>[avatar_upload]</code> and <code>[avatar]</code> shortcodes in your posts. These shortcodes will work with any theme, whether it has avatar support or not.
**Example Usage**
= Posts =
Within [The Loop](http://codex.wordpress.org/The_Loop), you may be using:
`<?php echo get_avatar(get_the_author_meta('ID'), 96); ?>`
Replace this function with:
`<?php echo get_zl_custom_user_avatars(get_the_author_meta('ID'), 96); ?>`
You can also use the values "original", "large", "medium", or "thumbnail" for your avatar size:
`<?php echo get_zl_custom_user_avatars(get_the_author_meta('ID'), 'medium'); ?>`
You can also add an alignment of "left", "right", or "center":
`<?php echo get_zl_custom_user_avatars(get_the_author_meta('ID'), 96, 'left'); ?>`
= Author Page =
On an author page outside of [The Loop](http://codex.wordpress.org/The_Loop), you may be using:
`<?php
  $user = get_user_by('slug', $author_name);
  echo get_avatar($user->ID, 96);
?>`
Replace this function with:
`<?php
  $user = get_user_by('slug', $author_name);
  echo get_zl_custom_user_avatars($user->ID, 96);
?>`
If you leave the options blank, Zipline Custom User Avatars will detect whether you're inside [The Loop](http://codex.wordpress.org/The_Loop) or on an author page and return the correct avatar in the default 96x96 size:
`<?php echo get_zl_custom_user_avatars(); ?>`
The function <code>get_zl_custom_user_avatars</code> can also fall back to <code>get_avatar</code> if there is no Zipline Custom User Avatars image. For this to work, "Show Avatars" must be checked in your Zipline Custom User Avatars settings. When this setting is enabled, you will see the user's [Gravatar](http://gravatar.com/) avatar or Default Avatar.
= Comments =
For comments, you might have in your template:
`<?php echo get_avatar($comment, 32); ?>`
Replace this function with:
`<?php echo get_zl_custom_user_avatars($comment, 32); ?>`
For comments, you must specify the $comment variable.
**Other Available Functions**
= [avatar_upload] shortcode =
You can use the <code>[avatar_upload]</code> shortcode to add a standalone uploader to a front page or widget. This uploader is only visible to logged-in users. If you want to integrate the uploader into a profile edit page, see [Other Notes](http://wordpress.org/plugins/zl-custom-user-avatars/other_notes/).
You can specify a user with the shortcode, but you must have <code>edit_user</code> capability for that particular user.
`[avatar_upload user="admin"]`
= [avatar] shortcode =
You can use the <code>[avatar]</code> shortcode in your posts. It will detect the author of the post or you can specify an author by username. You can specify a size, alignment, and link, but they are optional. For links, you can link to the original image file, attachment page, or a custom URL.
`[avatar user="admin" size="medium" align="left" link="file" /]`
You can also add a caption to the shortcode:
`[avatar user="admin" size="medium" align="left" link="file"]Photo Credit: Your Name[/avatar]`
**Note:** If you are using one shortcode without a caption and another shortcode with a caption on the same page, you must close the caption-less shortcode with a forward slash before the closing bracket: <code>[avatar /]</code> instead of <code>[avatar]</code>
= get_zl_custom_user_avatars_src =
Works just like <code>get_zl_custom_user_avatars</code> but returns just the image src. This is useful if you would like to link a thumbnail-sized avatar to a larger version of the image:
`<a href="<?php echo get_zl_custom_user_avatars_src($user_id, 'large'); ?>">
  <?php echo get_zl_custom_user_avatars($user_id, 'thumbnail'); ?>
</a>`
= has_zl_custom_user_avatars =
Returns true if the user has a Zipline Custom User Avatars image. You must specify the user ID:
`<?php
  if ( has_zl_custom_user_avatars($user_id) ) {
    echo get_zl_custom_user_avatars($user_id, 96);
  } else {
    echo '<img src="my-alternate-image.jpg" />';
  }
?>`
== Frequently Asked Questions ==
== Screenshots ==
== Changelog ==
= 1.0 =
* Initial release
== Upgrade Notice ==
