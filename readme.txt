=== Puntuarte Reviews Woo Commerce ===
Contributors: Puntuarte
Tags: reviews, ratings, commerce, mail
Requires at least: 3.0.1
Tested up to: 3.4
Stable tag: 4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates your Woo Commerce site with Puntuarte Reviews system. Use different Review widgets and get feedback from your customers.

== Description ==

This plugin integrates your Woo Commerce site with Puntuarte Reviews system. It will allow you to display different widgets at your website, generate customer feedback, drive traffic and increase conversions.
Besides displaying the customer ratings and reviews from products, this plugin sends automatically an email to the customer once a purchase has been completed. This email will allow the customer to give your site feedback letting him to rate his experience and leave a comment that automatically will be added to your widgets, so that information can be useful for other potential customers.

== Installation ==

1. Upload `puntuarte-reviews-woocommerce` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings and set your API KEY (you need to have an account in Puntuarte.com to get this key)
4. Select what widget you will use to show it in the Product page and the location for each one
5. If you want the Puntuarte Total Reviews widget enable it at Widgets section

== Frequently Asked Questions ==

= Why the widgets are not shown? =

You need a valid API KEY to connect with Puntuarte and get all your associated info from your account. You will find this API KEY in the profile page at [Your Profile Page](https://www.puntuarte.com/profile)

= I want to display a specific product widget in other region =

In order to locate one of the product widgets in a custome location open 'wp-content/plugins/woocommerce/templates/content-single-product.php' and add the following line for **Product Full Reviews Widget**  `<?php wc_puntuarte_show_widget('productfull'); ?>` or `<?php wc_puntuarte_show_widget('productmin'); ?>` for the **Product Reviews Mini Widget**  in the requested location.
