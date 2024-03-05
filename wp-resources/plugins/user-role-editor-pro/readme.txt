=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.4
Tested up to: 6.4.3
Stable tag: 4.64.1
Requires PHP: 7.3
License URI: https://www.role-editor.com/end-user-license-agreement/

User Role Editor Pro WordPress plugin makes user roles and capabilities changing easy. Edit/add/delete WordPress user roles and capabilities.

== Description ==

User Role Editor Pro WordPress plugin allows you to change user roles and capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. Changes made to the roles will stay unchanged.
You will have to configure lost part of the settings at the User Role Editor Pro Settings page again after that.
Right decision in this case is to delete free version folder (user-role-editor) after deactivation via FTP, not via WordPress.

== Changelog ==

= [4.64.1] 30.10.2023 =
* Core version: 4.64.1
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property URE_Export_Single_Role::$editor is deprecated in wp-content/plugins/user-role-editor-pro/pro/includes/classes/export-single-role.php:23
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property PluginInfo_1_3::$requires_php is deprecated in /wp-content/plugins/user-role-editor-pro/pro/includes/plugin-update-checker.php on line 801
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property PluginInfo_1_3::$license_state is deprecated in /wp-content/plugins/user-role-editor-pro/pro/includes/plugin-update-checker.php on line 801
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property PluginInfo_1_3::$request_time_elapsed is deprecated in /wp-content/plugins/user-role-editor-pro/pro/includes/plugin-update-checker.php on line 801
* Fix: Content view restrictions add-on: Undefined array key 0 in user-role-editor-pro/pro/includes/classes/post-types-own-caps.php on line 93
* Update: filter 'ure_check_updates' was added. It's return true by default. Return false from it to switch off automatic checking if new version of URE is available. It would be useful if you use URE behind corporate firewall and it does not have access to the Internet.
* Core version was updated to 4.64.1
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property URE_Editor::$hide_pro_banner is deprecated in /wp-content/plugins/user-role-editor/includes/classes/editor.php on line 166
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Creation of dynamic property URE_Role_View::$caps_to_remove is deprecated in /wp-content/plugins/user-role-editor/includes/classes/role-view.php on line 23
* Fix: Notice shown by PHP 8.3 is removed: PHP Deprecated: Function utf8_decode() is deprecated in /wp-content/plugins/user-role-editor-pro/includes/classes/editor.php on line 984

= [4.64] 08.08.2023 =
* Core version: 4.64
* Fix: PHP Warning: Trying to access array offset on value of type bool in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/admin-menu-access.php on line 356
* Fix: PHP Warning:  Undefined array key "message" in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/ajax-processor.php on line 228
* Update: Admin menu access add-on: Block "Sales Reports" menu automatically, if WooCommerce->Reports menu item is blocked
* Core version was updated to 4.64
* Fix: Missed 'message' parameter was added to response for AJAX query. It fixed the potential PHP Warning:  Undefined array key "message" in expressions like "strpos( $data['message'], ...
* Update: "Show capabilities in human readable form" checkbox switches between capability 2 text forms without full page reloading using JavaScript.


Full list of changes is available in changelog.txt file.
