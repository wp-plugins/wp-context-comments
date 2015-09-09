=== Plugin Name ===
Contributors:
Tags: comments, inline, medium, context
Requires at least: 4.0
Tested up to: 4.2.2
Stable tag: 4.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Wordpress Plugin to attach comments to inline text. Medium Style.

== Description ==

WP Context Comments is a simple plugin in beta phase. It allows logged in users to highlight text on posts and pages and attach comments directly to those highlights.

== Installation ==

1. Upload `wpcc` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Your all set to go

== Frequently Asked Questions ==

Contact me at y@thgie.ch

== Screenshots ==

Comming up.

== TODO ==

== Changelog ==

= 0.4.6 =

* Major overhaul. Comment Hook injection back in backend - php. Added possibility to edit comment context in backend when editing comment.

= 0.4.5 =

* Moved content modifying back to js because of problems in how php handles regex and bytecode.

= 0.4.4 =

* Uses regular comment view from basic wordpress.

= 0.4.3 =

* Adjusted z-index of Add Comment Button

= 0.4.2 =

* Exclude comment Marker from highlighting.

= 0.4.1 =

* Moved content modifying (comment markers) to backend.

= 0.4 =

* Added popup menu.

= 0.3.5 =

* Close Button on View Comment now translatable.
* Fixed Regex when html chunks in selected text.
* Enhanced traditional comment view with context.

= 0.3.4 =

* Since it seems cool to jump versions - here is a version jump.

= 0.3.3 =

* Sentence Expansion now properly stops at punctuation of punctuation is included in selection.

= 0.3.2 =

* Added Regex Character Setting.

= 0.3.1 =

* Fixed view comment.

= 0.3 =

* Major overhaul. Skipped rangy.js. Now less obstrusive and uses official comment form mechanic.

= 0.2.2 =

* Tiny Style fixes

= 0.2.1 =

* Post ID wasn't grabbed correctly on posts. Fixed.

= 0.2 =

* Sanitized/validated all user input.
* No more hardcoded path.

= 0.1 =

* Reacted to WP Plugin check suggestions.

= 0.0.1 =

* proof of concept
