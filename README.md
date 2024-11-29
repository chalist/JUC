=== User Creator by JSON ===

Contributors: chalist
Tags: users, json, bulk import, persian, arabic
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Bulk create WordPress users from a JSON file with support for Persian/Arabic text conversion.

== Description ==

JSON User Creator allows you to bulk create WordPress users from a JSON file. It supports mapping JSON fields to user attributes and handles Persian/Arabic text conversion.


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/json-user-creator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->JSON User Creator screen to configure the plugin


== Frequently Asked Questions ==
= Can I update existing users? =
No, this plugin is designed for creating new users only. Just you can add posts_id and update authors for posts.


= What format should my JSON file be in? =

Your JSON file should be an array of objects containing user data. Example:
```json
[
    {
        "username": "john-doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "post_id": [12, 121, 332, 45]  // Posts that will be assigned to this user
    }
]
```

== Changelog ==
= 1.0.0 =
* Initial release

== Upgrade Notice ==
= 1.0.0 =
Initial release of JSON User Creator

