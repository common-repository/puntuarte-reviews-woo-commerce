<?php
function wc_display_puntuarte_admin_page() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__(''));
	}
	if (isset($_POST['log_in_button']) ) {
		wc_display_puntuarte_settings();
	}
	elseif (isset($_POST['puntuarte_settings'])) {
		check_admin_referer( 'puntuarte_settings_form' );
		wc_proccess_puntuarte_settings();
		wc_display_puntuarte_settings();
	}
	else {
		$puntuarte_settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
		wc_display_puntuarte_settings();
	}
}

function wc_display_puntuarte_settings($success_type = false) {
	$puntuarte_settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
	$app_key = $puntuarte_settings['app_key'];
	$widget_tab_name = $puntuarte_settings['widget_tab_name'];

	if(empty($puntuarte_settings['app_key'])) {
		wc_puntuarte_display_message('Set your Puntuarte API key to display your customer reviews and send new comments requests.', true);			
	}
	$google_tracking_params = 'utm_source=puntuarte_plugin_woocommerce&utm_medium=header_link&utm_campaign=woocommerce_customize_link';

	$dashboard_link = "<a href='https://www.puntuarte.com/login?$google_tracking_params' target='_blank'>Puntuarte Dashboard.</a></div>";
	
	$read_only = isset($_POST['log_in_button']) ? '' : 'readonly';

	$settings_html =  
		"<div class='wrap'>"			
		   .screen_icon( ).
		   "<h2>Puntuarte Settings</h2>						  
			  <h4>For tracking access and other features you can log into Puntuarte and access to your ".$dashboard_link."</h4>
			  <form  method='post' id='puntuarte_settings_form'>
			  	<table class='form-table'>".
			  		wp_nonce_field('puntuarte_settings_form').
			  	  "
					 <tr valign='top'>
		   		       <th scope='row'><div>API KEY:</div></th>
		   		       <td><div class='y-input'><input id='app_key' type='text' name='puntuarte_app_key' value='$app_key' '/></div></td>
		   		     </tr>
					 <tr valign='top'>
		   		       <th scope='row'><div>Disable native reviews system:</div></th>
		   		       <td><input type='checkbox' name='disable_native_review_system' value='1' ".checked(1, $puntuarte_settings['disable_native_review_system'], false)." /></td>
		   		     </tr>
		   		     <tr valign='top'>			
				       <th scope='row'><div>Select widget language</div></th>
				       <td>
				         <select name='puntuarte_widget_lang' class='puntuarte-widget-lang'>" .
			 		       "<option value='es' ".selected('es',$puntuarte_settings['widget_lang'], false).">Spanish</option>
			 	           <option value='en' ".selected('en',$puntuarte_settings['widget_lang'], false).">English</option>
				         </select>
		   		       </td>
		   		     </tr>	                 	                 
	    	         <tr valign='top'>			
				       <th scope='row'><div>Select widget</div></th>
				       <td>
				         <select name='puntuarte_widget_type' class='puntuarte-widget-type'>" .
			 		       "<option value='productfull' ".selected('productfull',$puntuarte_settings['widget_type'], false).">Product Full (Detail user and comment reviews)</option>
			 	           <option value='productmin' ".selected('productmin',$puntuarte_settings['widget_type'], false).">Product min (Stars and number of total reviews)</option>
				         </select>
		   		       </td>
		   		     </tr>
	    	         <tr valign='top'>			
				       <th scope='row'><div>Select widget location</div></th>
				       <td>
				         <select name='puntuarte_widget_location' class='puntuarte-widget-location'>
				  	       <option value='footer' ".selected('footer',$puntuarte_settings['widget_location'], false).">Page footer</option>
			 		       <option value='tab' ".selected('tab',$puntuarte_settings['widget_location'], false).">Tab in Product Page</option>
			 	           <option value='other' ".selected('other',$puntuarte_settings['widget_location'], false).">Other</option>
				         </select>
		   		       </td>
		   		     </tr>
		   		     <tr valign='top' class='puntuarte-widget-tab-name'>
		   		       <th scope='row'><div>Select Tab name:</div></th>
		   		       <td><div><input type='text' name='puntuarte_widget_tab_name' value='$widget_tab_name' /></div></td>
		   		     </tr>
		   		     <tr valign='top' class='puntuarte-widget-location-other-explain'>
                 		<td scope='row'><p class='description'>In order to locate one of the product widgets in a custome location open 'wp-content/plugins/woocommerce/templates/content-single-product.php' and add the following line for <strong>Product Full Reviews Widget</strong>  <code>wc_puntuarte_show_widget('productfull');</code> or <code>wc_puntuarte_show_widget('productmin');</code> for the <strong>Product Reviews Mini Widget</strong>  in the requested location.</p></td>	                 																	
	                 </tr>
		   		     <tr valign='top'>
		   		       <th scope='row'><p class='description'>If you want the Puntuarte Total Reviews widget enable it at <a href='widgets.php' target='_blank'>Widgets section.</a></p></th>		   		       
		   		     </tr>
		         </table><input type='submit' name='puntuarte_settings' value='Save changes' class='button-primary' id='save_puntuarte_settings'/>
</br>
		  </form>	  		  
		</div>";		

	echo $settings_html;		  
}

function wc_proccess_puntuarte_settings() {
	$current_settings = get_option('puntuarte_settings', wc_puntuarte_get_default_settings());
	$new_settings = array('app_key' => $_POST['puntuarte_app_key'],
						 'widget_lang' => isset($_POST['puntuarte_widget_lang']) ? $_POST['puntuarte_widget_lang'] : 'es',
						 'widget_type' => $_POST['puntuarte_widget_type'],
						 'widget_location' => $_POST['puntuarte_widget_location'],
						 'widget_tab_name' => $_POST['puntuarte_widget_tab_name'],
						 'bottom_line_enabled_product' => isset($_POST['puntuarte_bottom_line_enabled_product']) ? true : false,
						 'disable_native_review_system' => isset($_POST['disable_native_review_system']) ? true : false
					);
	$request = wp_remote_post('https://www.puntuarte.com/API/verify/api_key/' . $_POST['puntuarte_app_key']);
	if($request['response']['code'] == 200){
			update_option( 'puntuarte_settings', $new_settings );
			wc_puntuarte_display_message('Settings values have been updated.',false);
	}
	else if (isset($_POST['puntuarte_app_key'])){
		wc_puntuarte_display_message('The API KEY you have submitted is not a valid key.', true);
	}
	if($current_settings['disable_native_review_system'] != $new_settings['disable_native_review_system']) {
		if($new_settings['disable_native_review_system'] == false) {		
			update_option( 'woocommerce_enable_review_rating', get_option('native_star_ratings_enabled'));
		}			
		else {
			update_option( 'woocommerce_enable_review_rating', 'no');
		}
	}
}

function wc_puntuarte_display_message($messages = array(), $is_error = false) {
	$class = $is_error ? 'error' : 'updated fade';
	if(is_array($messages)) {
		foreach ($messages as $message) {
			echo "<div id='message' class='$class'><p><strong>$message</strong></p></div>";
		}
	}
	elseif(is_string($messages)) {
		echo "<div id='message' class='$class'><p><strong>$messages</strong></p></div>";
	}
}