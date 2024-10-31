<?php 
/*Plugin Name: Puntuarte Woocommerce Total Reviews
Description: This widget displays the total Puntuarte reviews and ratings.
Version: 0.1
Author: Puntuarte
Author URI: https://www.puntuarte.com
License: GPLv2
*/

class Puntuarte_Total_Reviews_Widget extends WP_Widget {
	function __construct() {
		parent::__construct(
			// base ID of the widget
	        'puntuarte_total_reviews_widget',
	         
	        // name of the widget
	        __('Puntuarte Total Reviews', 'puntuarte' ),
	         
	        // widget options
	        array (
	            'description' => __( 'Displays Puntuarte total reviews widget, displaying number of reviews in the site and average rating.', 'puntuarte' )
	        )
		);
	}
	function form() {
	}
	function update() {
	}
	function widget($args, $instance) {
		$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
		?>
		<div id="puntuarte-global-widget" data-widget-lang="<?php echo $settings['widget_lang'] ?>"></div>
		<?php
	}
}

function puntuarte_register_total_reviews_widget() {
 
    register_widget( 'Puntuarte_Total_Reviews_Widget' );
 
}
add_action( 'widgets_init', 'puntuarte_register_total_reviews_widget' );