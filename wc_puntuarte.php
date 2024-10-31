<?php
/*
	Plugin Name: Puntuarte Reviews for Woocommerce
	Description: Puntuarte allows Woocommerce owners generate reviews for their products automatically. 
	Author: Puntuarte
	Version: 1.1.1
	Author URI: https://www.puntuarte.com?utm_source=puntuarte_wooc&utm_medium=wooc_plugin_page_link&utm_campaign=wooc_plugin_page_link	
	Plugin URI: https://www.puntuarte.com?utm_source=wooc_plugin_woocommerce&utm_medium=wooc_plugin_page_link&utm_campaign=wooc_plugin_page_link
 */
register_activation_hook(   __FILE__, 'wc_puntuarte_activation' );
register_uninstall_hook( __FILE__, 'wc_puntuarte_uninstall' );
register_deactivation_hook( __FILE__, 'wc_puntuarte_deactivate' );
add_action('plugins_loaded', 'wc_puntuarte_init');
add_action('init', 'wc_puntuarte_redirect');
add_action( 'woocommerce_order_status_completed', 'wc_puntuarte_map');
		
function wc_puntuarte_init() {
	include( plugin_dir_path( __FILE__ ) . 'wc_puntuarte-widget.php');
	$is_admin = is_admin();	
	if($is_admin) {
		include( plugin_dir_path( __FILE__ ) . 'inc/wc-puntuarte-settings.php');
		add_action( 'admin_menu', 'wc_puntuarte_admin_settings' );
	}
	$puntuarte_settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
	if(!empty($puntuarte_settings['app_key'])) {			
		if(!$is_admin) {
			add_action( 'wp_enqueue_scripts', 'wc_puntuarte_load_js' );
			add_action( 'template_redirect', 'wc_puntuarte_front_end_init' );	
		}								
	}			
}

function wc_puntuarte_admin_settings() {
	add_action( 'admin_enqueue_scripts', 'wc_puntuarte_admin_styles' );	
	$page = add_menu_page( 'Puntuarte', 'Puntuarte', 'manage_options', 'woocommerce-puntuarte-settings-page', 'wc_display_puntuarte_admin_page', 'none', null );			
}

function wc_puntuarte_redirect() {
	if ( get_option('wc_puntuarte_just_installed', false)) {
		delete_option('wc_puntuarte_just_installed');
		wp_redirect( ( ( is_ssl() || force_ssl_admin() || force_ssl_login() ) ? str_replace( 'http:', 'https:', admin_url( 'admin.php?page=woocommerce-puntuarte-settings-page' ) ) : str_replace( 'https:', 'http:', admin_url( 'admin.php?page=woocommerce-puntuarte-settings-page' ) ) ) );
		exit;
	}	
}

function wc_puntuarte_front_end_init() {
	$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
	if(is_product()) {
		$widget_location = $settings['widget_location'];				
		if($settings['disable_native_review_system']) {
			add_filter( 'comments_open', 'wc_puntuarte_remove_native_review_system', null, 2);	
		}		
		if($widget_location == 'footer') {		
			add_action('woocommerce_after_single_product', 'wc_puntuarte_show_widget', 5,1);
		}
		elseif($widget_location == 'tab') {
			add_action('woocommerce_product_tabs', 'wc_puntuarte_show_widget_in_tab');		
		}	
	}						
}

function wc_puntuarte_activation() {
	if(current_user_can( 'activate_plugins' )) {
		update_option('wc_puntuarte_just_installed', true);
	    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    	check_admin_referer( "activate-plugin_{$plugin}" );
		$default_settings = get_option('puntuarte_settings', false);
		if(!is_array($default_settings)) {
			add_option('puntuarte_settings', wc_puntuarte_get_default_settings());
		}
		update_option('native_star_ratings_enabled', get_option('woocommerce_enable_review_rating'));
		update_option('woocommerce_enable_review_rating', 'no');			
	}        
}

function wc_puntuarte_uninstall() {
	if(current_user_can( 'activate_plugins' ) && __FILE__ == WP_UNINSTALL_PLUGIN ) {
		check_admin_referer( 'bulk-plugins' );
		delete_option('puntuarte_settings');	
	}	
}

function wc_puntuarte_show_widget($type = null) {		
	if($type === '' || $type === null){
		$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
		$type = $settings['widget_type'];
	}
	$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
	switch ($type) {
	    case 'productfull':
	        $product = get_product();
	        $puntuarte_div = "<div class='puntuarte-product-widget' data-product-sku='".$product->get_sku() ."' data-widget-lang='" . $settings['widget_lang'] ."'></div>";
	        echo $puntuarte_div;
	        break;
	    case 'productmin':
	        $product = get_product();
	        $puntuarte_div = "<div class='puntuarte-product-mini-widget' data-product-sku='".$product->get_sku() ."' data-widget-lang='" . $settings['widget_lang'] ."'></div>";
	        echo $puntuarte_div;
	        break;
	}

}

function wc_puntuarte_show_widget_in_tab($tabs) {
	$product = get_product();
	if($product->post->comment_status == 'open') {
		$settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
	 	$tabs[$settings['widget_type']] = array(
	 	'title' => $settings['widget_tab_name'],
	 	'priority' => 50,
	 	'callback' => 'wc_puntuarte_show_widget'
	 	);
	}
	return $tabs;		
}

function wc_puntuarte_load_js(){
	if(wc_puntuarte_is_commerce_installed()) {	
		add_action('wp_head', 'wc_embed_js_wrapper');	
    	wp_enqueue_script('js-init', plugins_url('js/init.js', __FILE__) ,null,null);
		$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
		wp_localize_script('js-init', 'puntuarte_settings', array('app_key' => $settings['app_key']));    	    	
	}
}

function wc_puntuarte_get_product_data($product) {	
	$product_data = array();
	$settings = get_option('puntuarte_settings',wc_puntuarte_get_default_settings());
	$product_data['app_key'] = $settings['app_key'];
	$product_data['shop_domain'] = wc_puntuarte_get_shop_domain(); 
	$product_data['url'] = get_permalink($product->id);
	$product_data['description'] = strip_tags($product->get_post_data()->post_excerpt);
	$product_data['id'] = $product->id;	
	$product_data['title'] = $product->get_title();
	$product_data['product-models'] = $product->get_sku();	
	return $product_data;
}

function wc_puntuarte_get_shop_domain() {
	return parse_url(get_bloginfo('url'),PHP_URL_HOST);
}

function wc_embed_js_wrapper() {
	echo '<script type="text/javascript" id="puntuarte-widget-embedder" class="puntuarte-widget">' . '</script>';
}

function wc_puntuarte_remove_native_review_system($open, $post_id) {
	if(get_post_type($post_id) == 'product') {
		return false;
	}
	return $open;
}

function wc_puntuarte_is_commerce_installed() {
	return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
}


function wc_puntuarte_map($order_id) {
	$puntuarte_settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
	$app_key = $puntuarte_settings['app_key'];
	$admin_email = get_option('admin_email');
	if(!empty($app_key)){
		try {		
				$purchase_data = wc_puntuarte_get_single_map_data($order_id);
				if(!is_null($purchase_data) && is_array($purchase_data)) {
					//todo
					foreach ($purchase_data['products'] as $product){
					$data = "?customer_uid=" .  $app_key. "&product_sku=" . $product['sku'] . "&product_name=" . urlencode($product['name']) . "&product_url=" . urlencode($product['url']) . "&to=" . $purchase_data['email']  . "&username=" . $admin_email;
					$request = wp_remote_post('https://www.puntuarte.com/API/send/mail/request_rating' . $data);
					}
				}
			}		
		catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
}

function wc_puntuarte_get_single_map_data($order_id) {
	$order = new WC_Order($order_id);
	$data = null;
	if(!is_null($order->id)) {
		$data = array();
		$data['order_date'] = $order->order_date;
		$data['email'] = $order->billing_email;
		$data['customer_name'] = $order->billing_first_name.' '.$order->billing_last_name;
		$products_arr = array();
		foreach ($order->get_items() as $product) 
		{
			$product_instance = get_product($product['product_id']);
 
			$description = '';
			if (is_object($product_instance)) {
				$description = strip_tags($product_instance->get_post_data()->post_excerpt);	
			}
			$product_data = array();   
			$product_data['url'] = get_permalink($product['product_id']); 
			$product_data['name'] = $product['name'];
			$product_data['description'] = $description;
			$product_data['price'] = $product['line_total'];
			$product_data['sku'] = $product_instance->get_sku();
			$products_arr[$product['product_id']] = $product_data;	
		}	
		$data['products'] = $products_arr;
	}
	return $data;
}

function wc_puntuarte_get_default_settings() {
	return array( 'app_key' => '',
				  'widget_location' => 'footer',
				  'widget_type' => 'productfull',
				  'widget_tab_name' => 'Reviews',
				  'disable_native_review_system' => true,
				  'native_star_ratings_enabled' => 'no',
				  'bottom_line_enabled_product' => true);
}

function wc_puntuarte_admin_styles($hook) {
 	wp_enqueue_style('puntuarteSideLogoStylesheet', plugins_url('css/side-menu-logo.css', __FILE__));
 }

add_filter('woocommerce_tab_manager_integration_tab_allowed', 'wc_puntuarte_disable_tab_manager_managment');

function wc_puntuarte_deactivate() {
	update_option('woocommerce_enable_review_rating', get_option('native_star_ratings_enabled'));	
}