=== WooCommerce Pickupp ===
Contributors: pickupp
Tags: shipping, same day delivery, courier, last mile delivery, logistic
Donate link: https://pickupp.io
Requires at least: 4.6
Tested up to: 6.2.2
Requires PHP: 5.6
Stable tag: 2.4.0
License: ISC
License URI: https://opensource.org/licenses/ISC

Pickupp is a door-to-door, same-day delivery service, currently active in Hongkong, Singapore, Taiwan and Malaysia. Install this plugin to easily create orders from WooCommerce to Pickupp platform!

== Description ==

Pickupp is a door-to-door, same-day delivery service, currently active in Hongkong, Singapore, Taiwan and Malaysia. Install this plugin to easily create orders from WooCommerce to Pickupp platform!

== Installation ==

1. Install WooCommerce Pickupp either via the WordPress.org plugin repository or by uploading the files to your server. (See instructions on [how to install a WordPress plugin](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners))
2. Activate WooCommerce Pickupp.
3. Navigate to WooCommerce -> Settings -> Advanced -> Pickupp
4. Select region and submit
5. Click `Link to Pickupp`

== Frequently Asked Questions ==

= How do I create a delivery order via the Woocommerce Pickupp plugin? =

After installation and setup of the plugin is complete, your Woocommerce order will be automatically reflected when you click on the ‘Woocommerce’ tab in the Pickupp dashboard.

= How do I set the pick up location i.e. my store’s address? =

Go to the Woocommerce “General Settings” page and fill in the store address fields

= Why are my orders’ dimension and weight not reflected correctly in the Pickupp dashboard? =

The order dimension and weight are calculated base on selected product data, so you have to fill in dimension and weight in product shipping setting

= More questions regarding Pickupp’s delivery needs? =

Visit the FAQ section on our homepage here:

HK: [HK Pickupp](https://hk.pickupp.io/faq "hk.pickupp.io")
SG: [SG Pickupp](https://sg.pickupp.io/faq "sg.pickupp.io")
MY: [MY Pickupp](https://my.pickupp.io/faq "my.pickupp.io")

= More FAQ? =

You can visit the FAQ section in our homepage to checkout more.
HK: [HK Pickupp](https://hk.pickupp.io/faq "hk.pickupp.io")
SG: [SG Pickupp](https://sg.pickupp.io/faq "sg.pickupp.io")
MY: [MY Pickupp](https://my.pickupp.io/faq "my.pickupp.io")

== Screenshots ==

1. Download Pickupp Woocommerce plugin
2. Go to Wordpress, Add New Plugins, upload & install plugin
3. Activate Pickupp Woocommerce plugin
4. Go to Woocommerce Settings on the menu bar
5. Select Advanced tab, and click Pickupp
6. Choose the Region where you registered the account , save changes and click Link to Pickupp
7. Select YES to “confirm linking to Pickupp” and you’re all set!

== Changelog ==

= 2.4.0 =
* Add REST endpoint for products & added sku property for orders query

= 2.3.3 =
* Small bugs fixed

= 2.3.2 =
* Add auth method option

= 2.3.0 =
* Support Taiwan region

= 2.2.3 =
* Fix missing apache header authorization

= 2.2.0 =
* Fix heading issue with Apache server

= 2.2.0 =
* Support searching by order number

= 2.1.1 =
* Convert dimension to "cm" unit
* Convert weight to "kg" unit

= 2.1.0 =
* Remove test mode
* Update screenshots

= 2.0.0 =
* Improve integration linking flow
* Show Pickupp order status in woocommerce order detail
* Deprecate create Pickupp order action

= 1.1.4 =
* Simplify some process

= 1.1.3 =
* Small bugs fixed

= 1.1.2 =
* Small bugs fixed

= 1.1.1 =
* Fix default dimention issue

= 1.1.0 =
* Improve plugin stability and user experience

= 1.0.13 =
* Add express option for delivery window

= 1.0.12 =
* Internal improvement on regional settings

= 1.0.11 =
* Internal improvement on order creation

= 1.0.10 =
* Fix http request error on some PHP configurations

= 1.0.9 =
* Better error handling on order creation

= 1.0.8 =
* Minor fixes and updates

= 1.0.6 =
* Display currency of generated delivery order price

= 1.0.5 =
* Minor bug fix

= 1.0.4 =
* Fix syntax error, update PHP minimum version to 5.6

= 1.0.3 =
* Fix plugin update

= 1.0.2 =
* Fix status map and enable redeliver when order fail to deliver

= 1.0.1 =
* Added order status update endpoint
* Require adding webhook url in Pickupp merchant portal

= 1.0.0 =
* First Release.


== Upgrade Notice ==

Updated to be able to accept order status update
