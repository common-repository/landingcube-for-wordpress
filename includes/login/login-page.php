<?php

	
////////////////////////////
// SETTINGS PAGE 
////////////////////////////
function fca_lcwp_register_login_page() {
	
	$fca_lcwp_options = get_option( 'fca_lcwp_options', array() );
	$page_name = !empty( $fca_lcwp_options['secret'] ) ? __( 'Logout', 'landingcube-wp' ) : __( 'Login', 'landingcube-wp' );

	add_submenu_page(
		'edit.php?post_type=landingcube_page',
		$page_name,
		$page_name,
		'manage_options',
		'fca_lcwp_login_page',
		'fca_lcwp_render_login_page'
	);

}
add_action( 'admin_menu', 'fca_lcwp_register_login_page' );

function fca_lcwp_render_login_page( $post ) {
	
	$user = wp_get_current_user();
	$fca_lcwp_options = get_option( 'fca_lcwp_options', array() );
	$logged_in = !empty( $fca_lcwp_options['secret'] );
	$email = isSet( $fca_lcwp_options['email'] ) ? $fca_lcwp_options['email'] : $user->user_email;
	
	wp_enqueue_script('jquery');
	wp_enqueue_style( 'fca_lcwp_settings_stylesheet', FCA_LCWP_PLUGINS_URL . '/includes/login/login.css', array(), FCA_LCWP_PLUGIN_VER );
	wp_enqueue_script('fca_lcwp_settings_js', FCA_LCWP_PLUGINS_URL . '/includes/login/login.js', array( 'jquery' ), FCA_LCWP_PLUGIN_VER, true );		
	
	$settings_data = array (
		'ajaxurl' => admin_url ( 'admin-ajax.php' ),
		'redirect' => admin_url ( 'post-new.php?post_type=landingcube_page' ),
		'logout_msg' => __( 'Log out of your LandingCube account now?', 'landingcube-wp' ),
		'logged_in' => $logged_in,
	);
	
	wp_localize_script( 'fca_lcwp_settings_js', 'fcaLcwpSettingsData', $settings_data );
	
	?>

	<section id="login">
		<form method="post">
			<h1 id="login-welcome">Welcome Back!</h1>
			<?php wp_nonce_field( 'fca_lcwp_login_nonce', 'fca_lcwp_login_nonce' ) ?>
			<input type="email" name="fca_lcwp_email" placeholder="Email Address" class="input" value="<?php echo $email ?>" size="20" required="">
			<input type="password" name="fca_lcwp_password" placeholder="Password" class="input" value="" size="20" required="">
			<a class="forgot-password" target='_blank'  href="https://pages.landingcube.com/wp-sp-login.php?action=lostpassword&redirect_to=https%3A%2F%2Fpages.landingcube.com%2F" title="Lost Password">I forgot my password</a>
			
			<button type="submit" id="login-submit" class="button button-primary">Login</button>

			<p class="signup-text">Don't have a login? Start your free trial <a  target='_blank'  href="https://landingcube.com/pricing/">here</a>.</p>
		</form>
	</section>	
	<?php 
}
