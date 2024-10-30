<?php

////////////////////////////
// API
////////////////////////////


//INPUT USER NAME AND SECRET
function fca_lcwp_get_campaigns( $hard_refresh = false ) {
	
	// Get any existing copy of our transient data
	if ( $hard_refresh == false && get_transient( 'fca_lcwp_campaigns' ) ) {
		return get_transient( 'fca_lcwp_campaigns' );
	}

	$fca_lcwp_options = get_option( 'fca_lcwp_options', array() );
	
	$email = !empty( $fca_lcwp_options['email'] ) ? $fca_lcwp_options['email'] : '';
	$secret = !empty( $fca_lcwp_options['secret'] ) ? $fca_lcwp_options['secret'] : '';
	
	$args = array(
		'method' => 'POST',
		'timeout'     => 30,
		'redirection' => 30,
		'body' => array( 
			'email' => $email,
			'secret' => $secret
		),
	);
	
	$url = 'https://pages.landingcube.com/wpapi';

	$response = wp_remote_request( $url, $args );
	
	if( is_wp_error( $response ) ) {
		return $response;
	}
	
	$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
	$success = !empty( $decoded['success'] ) ? $decoded['success'] : false;
	$data = !empty( $decoded['data'] ) ? $decoded['data'] : '';
	$campaigns = !empty( $data['campaigns'] ) ? $data['campaigns'] : array();
	
	if ( $success == false ) {
		return new WP_Error( 'fca_lcwp_unauthorized', $data );
	}
	
	set_transient( 'fca_lcwp_campaigns', $campaigns, 60 * 15 );
	return $campaigns;
}

//REFRESH POSTS ENDPOINT
function fca_lcwp_refresh_posts() {
	
	$nonce = sanitize_text_field( $_REQUEST['nonce'] );
	$nonceVerified = wp_verify_nonce( $nonce, 'fca_lcwp_admin_nonce') == 1;

	if ( $nonceVerified ) {
		$campaigns = fca_lcwp_get_campaigns( true );
		if ( is_wp_error( $campaigns ) ) {
			wp_send_json_error( $campaigns->get_error_message() );
		}
		wp_send_json_success( $campaigns );
	}
	wp_send_json_error();

}
add_action( 'wp_ajax_fca_lcwp_refresh_posts', 'fca_lcwp_refresh_posts' );


//ATTEMPT TO LOGIN TO LANDING CUBE API AND GET SECRET
function fca_lcwp_api_login( $email, $pass ) {
	//CLEAR THE OLD SECRET
	update_option( 'fca_lcwp_options', array( 'email' => $email, 'secret' => false ) );
	
	$args = array(
		'method' => 'POST',
		'timeout'     => 30,
		'redirection' => 30,
		'body' => array( 
			'email' => $email,
			'password' => $pass,
		),
	);
	$url = 'https://pages.landingcube.com/wpapi';
	
	$response = wp_remote_request( $url, $args );
	
	if( !is_wp_error( $response ) ) {
		if ( $response['response']['code'] == 200 && isSet( $response['body'] ) ) {
			$decoded = json_decode( $response['body'], true );
			if ( !empty( $decoded['data'] ) ) {
				update_option( 'fca_lcwp_options', array( 'email' => $email, 'secret' => $decoded['data'] ) );
				return true;
			}
		}
	}
	return false;
	
}