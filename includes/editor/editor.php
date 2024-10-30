<?php
	
////////////////////////////
// EDITOR PAGE 
////////////////////////////

//ENQUEUE ANY SCRIPTS OR CSS FOR OUR ADMIN PAGE EDITOR
function fca_lcwp_admin_cpt_script( $hook ) {
	global $post;
	if ( ($hook == 'post-new.php' || $hook == 'post.php')  &&  $post->post_type === 'landingcube_page' ) {  
		wp_enqueue_script('jquery');
		wp_enqueue_style('dashicons');		
		wp_enqueue_style('fca_lcwp_tooltipster_stylesheet', FCA_LCWP_PLUGINS_URL . '/includes/vendor/tooltipster/tooltipster.bundle.min.css', array(), FCA_LCWP_PLUGIN_VER);
		wp_enqueue_style('fca_lcwp_tooltipster_borderless_css', FCA_LCWP_PLUGINS_URL . '/includes/vendor/tooltipster/tooltipster-borderless.min.css', array(), FCA_LCWP_PLUGIN_VER);
		wp_enqueue_style('fca_lcwp_tooltipster_fca_css', FCA_LCWP_PLUGINS_URL . '/includes/vendor/tooltipster/tooltipster-fca-theme.min.css', array(), FCA_LCWP_PLUGIN_VER);
		wp_enqueue_script('fca_lcwp_tooltipster_js', FCA_LCWP_PLUGINS_URL . '/includes/vendor/tooltipster/tooltipster.bundle.min.js', array('jquery'), FCA_LCWP_PLUGIN_VER, true );
		
		wp_enqueue_style('fca_lcwp_select2', FCA_LCWP_PLUGINS_URL . '/includes/vendor/select2/select2.min.css', array(), FCA_LCWP_PLUGIN_VER );
		wp_enqueue_script('fca_lcwp_select2', FCA_LCWP_PLUGINS_URL . '/includes/vendor/select2/select2.min.js', array(), FCA_LCWP_PLUGIN_VER, true );

		wp_enqueue_script('fca_lcwp_editor_js', FCA_LCWP_PLUGINS_URL . '/includes/editor/editor.js', array( 'jquery', 'fca_lcwp_select2', 'fca_lcwp_tooltipster_js' ), FCA_LCWP_PLUGIN_VER, true );		
		wp_enqueue_style('fca_lcwp_editor_stylesheet', FCA_LCWP_PLUGINS_URL . '/includes/editor/editor.css', array(), FCA_LCWP_PLUGIN_VER);
		
		$admin_data = array (
			'ajaxurl' => admin_url ( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'fca_lcwp_admin_nonce' ),
			'post_id' => $post->ID,
			'site_url' => get_site_url(),
			'campaigns' => fca_lcwp_get_campaigns(),
		);
		
		wp_localize_script( 'fca_lcwp_editor_js', 'fcaLcwpData', $admin_data );
		
	}

}
add_action( 'admin_enqueue_scripts', 'fca_lcwp_admin_cpt_script', 10, 1 );  

//ADD META BOXES TO EDIT CPT PAGE
function fca_lcwp_add_custom_meta_boxes( $post ) {
	
	add_meta_box( 
		'fca_lcwp_landing_page_deploy_meta_box',
		__( 'Setup', 'landingcube-wp' ),
		'fca_lcwp_render_deploy_meta_box',
		null,
		'normal',
		'high'
	);
	
	remove_meta_box( 'slugdiv', 'landingcube_page', 'normal' );
	
}
add_action( 'add_meta_boxes_landingcube_page', 'fca_lcwp_add_custom_meta_boxes', 10 );

function fca_lcwp_remove_screen_options_tab ( $show_screen, $screen ) {
	if ( $screen->id == 'landingcube_page' ) {
		return false;
	}
	return $show_screen;
}	
add_filter('screen_options_show_screen', 'fca_lcwp_remove_screen_options_tab', 10, 2);


function fca_lcwp_render_deploy_meta_box ( $post ) {
	
	$meta = get_post_meta ( $post->ID, 'fca_lcwp', true );
	$meta = empty ( $meta ) ? array() : $meta;
	
	//DEFAULTS
	
	$settings = array(
		'deploy_mode',
		'deploy_url_url',
		'campaign_url',
		'campaign_type',
		'campaign_name'
	);
	
	//MAKE SURE ARRAY IS SET
	forEach ($settings as $s) {
		$meta[$s] = !isSet( $meta[$s] ) ? '' : $meta[$s];
	}

	$campaigns = fca_lcwp_get_campaigns();
	
	ob_start();
	?>
	<input type='hidden' name='fca_lcwp_preview_url' id='fca_lcwp_preview_url' value='<?php echo fca_lcwp_post_url( $post->ID ) ?>'>
	<input type='hidden' name='fca_lcwp[campaign_type]' id='fca_lcwp_campaign_type' value='<?php echo $meta['campaign_type'] ?>'>
		<table class='fca-lcwp-setting-table'>
		<tr>
			<th><?php _e('Landing Page Behavior', 'landingcube-wp') ?></th>
			<td>
				<?php 
				echo fca_lcwp_select( 'deploy_mode', $meta['deploy_mode'], array(
					'url' => __( 'Publish on a specific URL', 'landingcube-wp' ),
					'homepage' => __( 'Replace my homepage', 'landingcube-wp' )
				) );
			?>
			</td>
		</tr>			
		<tr id="fca-lcwp-redirect-url-input">
			<th></th>
			<td>
				<span id='fca-lcwp-site-url'><?php echo get_site_url() . '/' ?></span>
				<input type='text' class='fca-lcwp-deploy_url_url' name='fca_lcwp[deploy_url_url]' value='<?php echo $meta['deploy_url_url'] ?>'>
			</td>
		</tr>
		<tr id="fca-lcwp-select-campaign">
			<th><?php _e('Campaign', 'landingcube-wp') ?></th>
			<td>
				<?php 
				echo fca_lcwp_select( 'campaign_url', $meta['campaign_url'], array( $meta['campaign_url'] => $meta['campaign_url'] ), 'style="width:100%"' );
				echo fca_lcwp_spinner( __('Refresh campaign list', 'landingcube-wp' ) );
				if ( empty( $campaigns ) ) { 
					echo __( 'It appears you have no active pages.', 'landingcube-wp' );
					echo " <a href='https://pages.landingcube.com/dashboard' target='_blank'>";
					echo __( 'Click here</a> to create a new page.', 'landingcube-wp' );
					echo '<br>';
				} ?>
			</td>			
		</tr>
		<tr>
			<th><?php _e('Page Title', 'landingcube-wp') ?></th>
			<td>
				<?php echo fca_lcwp_input( 'campaign_name', '', $meta['campaign_name'], 'text', "id='fca_lcwp_campaign_name'" ); ?>
			</td>
		</tr>
	</table>	
	<?php 
	echo ob_get_clean();
}

//CUSTOM SAVE HOOK
function fca_lcwp_save_post( $post_id ) {
	
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
		return $post_id;
	}
	
	//ONLY DO OUR STUFF IF ITS A REAL SAVE, NOT A NEW IMPORTED ONE
	if ( array_key_exists( 'fca_lcwp_preview_url', $_POST ) ) {
		
		// unhook this function so it doesn't loop infinitely
		remove_action('save_post_landingcube_page', 'fca_lcwp_save_post');
	
		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $post_id, 'post_title'=> sanitize_text_field( $_POST['fca_lcwp']['campaign_name'] ) ) );

		// re-hook this function
		add_action('save_post_landingcube_page', 'fca_lcwp_save_post');
		
		update_post_meta( $post_id, 'fca_lcwp', fca_lcwp_sanitize_post_save() );
		wp_publish_post( $post_id );
	}	
}
add_action( 'save_post_landingcube_page', 'fca_lcwp_save_post' );

function fca_lcwp_sanitize_post_save() {
	return array(
		'campaign_url' => empty( $_POST['fca_lcwp']['campaign_url'] ) ? '' : sanitize_text_field( $_POST['fca_lcwp']['campaign_url'] ),
		'campaign_name' => empty( $_POST['fca_lcwp']['campaign_name'] ) ? '' : sanitize_text_field( $_POST['fca_lcwp']['campaign_name'] ),
		'campaign_type' => empty( $_POST['fca_lcwp']['campaign_type'] ) ? '' : sanitize_text_field( $_POST['fca_lcwp']['campaign_type'] ),
		'deploy_mode' => empty( $_POST['fca_lcwp']['deploy_mode'] ) ? '' : sanitize_text_field( $_POST['fca_lcwp']['deploy_mode'] ),
		'deploy_url_url' => empty( $_POST['fca_lcwp']['deploy_url_url'] ) ? '' : fca_lcwp_clean_string_for_url( $_POST['fca_lcwp']['deploy_url_url'] ),
	);
}

//Redirect when Save & Preview button is clicked
function fca_lcwp_save_preview_redirect ( $location ) {
	global $post;
	if ( !empty( $_POST['fca_lcwp_preview_url'] ) ) {
		// Flush rewrite rules
		global $wp_rewrite;
		$wp_rewrite->flush_rules( true );

		return esc_url( $_POST['fca_lcwp_preview_url'] );
		
	}
 
	return $location;
}
add_filter('redirect_post_location', 'fca_lcwp_save_preview_redirect');

	
function fca_lcwp_admin_notices() {
	
	$notices = array();
	$new_login = null;
	
	$fca_lcwp_options = get_option( 'fca_lcwp_options', array() );
	$secret = !empty( $fca_lcwp_options['secret'] ) ? $fca_lcwp_options['secret'] : false;
	
	$screen = get_current_screen();
	$login_url = admin_url('edit.php?post_type=landingcube_page&page=fca_lcwp_login_page');
	$is_login_screen = $screen && $screen->id === 'landingcube_page_page_fca_lcwp_login_page';
	$is_our_cpt = $screen && ( $screen->post_type === 'landingcube_page' );
	
	if ( $is_login_screen ) {
		$nonce = !empty( $_POST['fca_lcwp_login_nonce'] ) ? sanitize_text_field( $_POST['fca_lcwp_login_nonce'] ) : false;
		$nonce_ok = wp_verify_nonce( $nonce, 'fca_lcwp_login_nonce' );
		$password = !empty( $_POST['fca_lcwp_password'] ) ? sanitize_text_field( $_POST['fca_lcwp_password'] ) : false;
		$email = !empty( $_POST['fca_lcwp_email'] ) ? sanitize_text_field( $_POST['fca_lcwp_email'] ) : false;	
		if ( isSet( $_GET['logout'] ) ) { 
			//REMOVE CREDENTIALS
			delete_option( 'fca_lcwp_options' );
			delete_transient( 'fca_lcwp_campaigns' );
		}
		if ( $password && $email && $nonce_ok ) { 
			//UPDATE CREDENTIALS
			if ( fca_lcwp_api_login( $email, $password ) === true ) {
				$new_post_url = admin_url('post-new.php?post_type=landingcube_page');
				echo "<script>window.location.href='$new_post_url'</script>";
				wp_die();
			} else {
				$notices[] = array(
					'class'=> 'error',
					'notice' => __('Login Failed. Please double check your email address and password and try again.', 'landingcube-wp' )
				);
			}
		}	
	}

	if ( !$is_login_screen && empty( $secret ) ) {
		//NOT LOGGED / NEVER LOGGED IN
		if( $is_our_cpt ) {
			//REDIRECT TO LOGIN PAGE
			echo "<script>window.location.href='$login_url'</script>";
			wp_die();
		}
		
		$notices[] = array(
			'class'=> 'notice-warning',
			'notice' => sprintf( __('You are not logged in to LandingCube. To get started, click the link below.%s%sLogin to LandingCube%s', 'landingcube-wp' ), 
				"<br><br>",
				"<a href='$login_url'>",
				"</a>"
			)
		);
	}

	if ( get_option('permalink_structure') == '' ) {
		$permalinks_url = admin_url('options-permalink.php');
		$notices[] = array(
			'class'=> 'notice-error',
			'notice' => sprintf( __('LandingCube plugin needs %spermalinks%s enabled!', 'landingcube-wp' ), 
				"<a href='$permalinks_url'>",
				"</a>"
			)
		);
	}

	$html = '';
	forEach ( $notices as $notice ) {
		$html .= "<div class='notice is-dismissible " . $notice['class'] . "'>";
		$html .= "<p>" . $notice['notice'] . "</p>";
		$html .= "</div>";
	}
	echo $html;
	
}

add_action( 'admin_notices', 'fca_lcwp_admin_notices' );

