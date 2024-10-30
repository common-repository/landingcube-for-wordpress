<?php


function fca_lcwp_landing_dequeue() {
	//REMOVE ALL OTHER CSS
	global $wp_styles;
	$wp_styles->queue = array();
}

function fca_lcwp_render_landing_page() {
	add_action('wp_print_styles', 'fca_lcwp_landing_dequeue', 100);
	remove_action('wp_head', '_admin_bar_bump_cb');
	return load_template( FCA_LCWP_PLUGIN_DIR . '/includes/landing/page_template.php' );
}

function fca_lcwp_check_for_deploy( $template ) {
	
	//CHECK IF SKIPPED
	if ( !empty( $_GET['fca_lcwp_skip'] ) ) {
		return $template;
	}
	
	if ( !empty( $_GET['landingcube_page'] ) ) {
		return fca_lcwp_render_landing_page();
	}
	
	$this_url = fca_lcwp_current_url(); 

	$query = new WP_Query( array(
		'post_type' => 'landingcube_page',
		'post_status' => 'publish',
		'post_per_page' => -1,
		'nopaging' => true,
	) );
	
	$current_post_meta = array();
	$deploy = false;
		
	if ( $query->have_posts() ) {
	
		while ( $query->have_posts() ) {
			
			$query->the_post();
			$current_post_meta = get_post_meta( get_the_ID(), 'fca_lcwp', true );
			$current_post_meta = empty( $current_post_meta ) ? array() : $current_post_meta;
			$mode = empty( $current_post_meta['deploy_mode'] ) ? '' : $current_post_meta['deploy_mode'];
			
			switch( $mode ) {
				
				case 'homepage':
					if ( is_front_page() ) {
						$deploy = true;
					}
					break;
				
				case 'url':
					$deploy_url = get_site_url() . '/' . $current_post_meta['deploy_url_url'];
					//CHECK TRAILING SLASH
					$deploy_url_alt = substr( $current_post_meta['deploy_url_url'], -1 ) !== '/' ? get_site_url() . '/' . $current_post_meta['deploy_url_url'] . '/' : get_site_url() . '/' . rtrim( $current_post_meta['deploy_url_url'], '/' );
					
					if ( $deploy_url === $this_url OR $deploy_url_alt === $this_url )   {
						$deploy = true;
					}
					break;
					
			}
			
			if ( $deploy === true ) {
				return fca_lcwp_render_landing_page();
			}
		}
	
	}
	wp_reset_query();
	return $template;
}
add_filter( 'template_include', 'fca_lcwp_check_for_deploy', 1, 99999 );