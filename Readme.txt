=== Accordion Category Display ===
Contributors: Stephen Lee Hernandez
Tags: accordion, categories, posts, display, documents
Requires at least: 6.0
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accordion Category Display is a WordPress plugin to elegantly display posts within categories in an accordion-style layout, resembling a file directory.

== Description ==

Accordion Category Display plugin allows you to showcase your posts or custom post types within categories, in an accordion-style layout that mimics a file directory structure. It's designed to work seamlessly with ACF Extended (ACFE) to create a 'document' post type and 'document categories' taxonomy, but it can be adapted for regular posts as well.

Features:
- Display categories and posts in an accordion layout.
- Works with ACF Extended for creating a custom 'document' post type and taxonomy.
- Easy to adapt for use with standard WordPress posts.
- Fully responsive and accessible.

== Installation ==

1. Upload `accordion-category-display` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the shortcode `[document_category_list]` in your posts or pages to display the accordion.
4. In ACF create a post type called Document with value document, a taxonomy called document category with value document_category and user field called user field with value user_field

== Frequently Asked Questions ==

= Can I use this plugin with standard WordPress posts? =

Yes. While this plugin is configured for use with custom post types created by ACF Extended, it can be adapted for regular posts. To do this, edit the `functions.php` file in the `includes` directory and modify the `post_type` and `taxonomy` parameters to suit standard posts and categories.

= The plugin is creating a custom folder when files are getting added, can I disable that? =

Yes. You can go to the functions in the includes folder and modify the file_upload_folder function

== Screenshots ==

1. Accordion display of categories and posts.
2. Example of a document category expanded.
3. Settings in the WordPress admin panel.

== Changelog ==

= 1.0 =
- Initial release.

== Upgrade Notice ==

= 1.0 =
Initial release.

== Arbitrary Section ==

If you have additional information such as customizing the plugin, you can include it in this section.
