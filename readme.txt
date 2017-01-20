=== Inline Image Upload for BBPress ===
Contributors: hearken, alfhen
Original creator link: https://potentplugins.com/
Tags: bbpress, image, images, inline, media
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 1.1.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upload inline images to BBPress forum topics and replies.

== Description ==

This plugin enables the TinyMCE WYSIWYG editor for BBPress forum topics and replies and adds a button to the editor's "Insert/edit image" dialog that allows forum users to upload images from their computer and insert them inline into their posts.
This fork tweaks the plugin so images are stored as attachments and are thus visible in media library.
Images are not removed from the media library when you delete them from the posts.

== Installation ==

1. Click "Plugins" > "Add New" in the WordPress admin menu.
1. Search for "Image Upload for BBPress".
1. Click "Install Now".
1. Click "Activate Plugin".

Alternatively, you can manually upload the plugin to your wp-content/plugins directory.

== Frequently Asked Questions ==

== Screenshots ==

1. The Image toolbar icon in the TinyMCE editor for forum topics and replies.
2. The Browse button in the Image dialog, which allows the user to select and upload an image from their computer for inline insertion into their forum topic or reply.

== Changelog ==

= 1.1.13 =
* Added composer support

= 1.1.12 =
* Changed plugin to upload files as attachments on posts

= 1.1.11 =
* Fixed unnecessary creation of revision when saving forum post

= 1.1.10 =
* Improved compatibility with other plugins and themes that activate the visual editor in bbPress

= 1.1.7 =
* Added image button to full TinyMCE editor
* Fixed tabbing issue in image dialog

= 1.1.1 =
* Fixed problem with reply threading in IE

= 1.1.0 =
* Added support for rotations based on EXIF orientation data in JPEG images
* Added cleanup feature to remove unnecessary files
* Added plain text conversion when pasting formatted text into the WYSIWYG editor
* Added attempt to increase PHP memory limit before image processing

= 1.0.8 =
* Fixed bug affecting multi-domain sites

= 1.0.7 =
* Fixed bug with non-root-URL WordPress installations

= 1.0.5 =
* Hide image caption field

= 1.0.4 =
* Fixed bug with uploads by anonymous users

= 1.0 =
* Initial release

== Upgrade Notice ==
