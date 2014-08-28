=== LinkedIn Lite ===
Contributors: alexfurr, simon.ward
Tags: linkedin, multisite, page, widget.
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 1.0

Display your LinkedIn profile in a page, post or the sidebar  of your blog. Has a network mode for multisite installations.

== Description ==

LinkedIn LITE allows a blog admin to authenticate their linkedIn profile for public display on the site. In Multisite mode it allows a single set of API keys to work across the entire multisite network.

Features:

- Choose which parts of your profile to display.
- Show your profile in pages and posts by using a shortcode.
- Show your profile in sidebars using the widget.
- Multisite options - Ability to run on a multisite network with just 1 set of API keys.

The plugin uses the LinkedIn API, to use it you will need a set of API keys. This is easy to set up and all instructions are included in the plugin.


IMPORTANT INFO FOR MULTISITE NETWORK USAGE:

- Put the plugin in the regular 'plugins' folder.
 
- In order to use a single set of API keys for the network you must activate the plugin on the root blog, this will add a new admin page into the Network-Admin 'Settings' menu where you can use your API key and secret key.

 
== Installation ==

Installing on single site using WordPress:

1. Log in and go to 'plugins' -> 'Add New'.
2. Search for 'linkedin lite' and hit the 'Install now' link in the results, Wordpress will install it.
3. Activate the plugin.

Installing on multisite if wanting shared API keys:

1. Install from Network Admin > Plugins
2. Activate the plugin on the MAIN (root) BLOG. DO NOT add any API keys at this point
3. To use shared API keys on all sites in the network, now go to Network Admin > Settings > LinkedInLite


== Screenshots ==

1. Authorisation of the plugin via LinkedIn
2. Select the fields you want for your page profile
3. Network API keys for multisite installations


== Changelog ==

= 1.0 =
* First release.
