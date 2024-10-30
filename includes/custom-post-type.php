<?php 

////////////////////////////
// SET UP POST TYPE
////////////////////////////

//REGISTER CPT
function fca_lcwp_register_post_type() {

	$labels = array(
		'name' => _x('Landing Pages','landingcube-wp'),
		'singular_name' => _x('Landing Page','landingcube-wp'),
		'add_new' => _x('Add New','landingcube-wp'),
		'all_items' => _x('All Landing Pages','landingcube-wp'),
		'add_new_item' => _x('Add New Landing Page','landingcube-wp'),
		'edit_item' => _x('Edit Landing Page','landingcube-wp'),
		'new_item' => _x('New Landing Page','landingcube-wp'),
		'view_item' => _x('View Landing Page','landingcube-wp'),
		'search_items' => _x('Search Landing Pages','landingcube-wp'),
		'not_found' => _x('Landing Page not found','landingcube-wp'),
		'not_found_in_trash' => _x('No Landing Pages found in trash','landingcube-wp'),
		'parent_item_colon' => _x('Parent Landing Page:','landingcube-wp'),
		'menu_name' => _x('LandingCube','landingcube-wp')
	);
		
	$args = array(
		'labels' => $labels,
		'description' => "",
		'public' => false,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => false,
		'show_in_menu' => true,
		'show_in_admin_bar' => true,
		'menu_position' => 119,
		'menu_icon' => FCA_LCWP_PLUGINS_URL . '/icon.png',
		'capability_type' => 'post',
		'hierarchical' => false,
		'supports' => false,
		'has_archive' => false,
		'rewrite' => false,
		'query_var' => true,
		'can_export' => true
	);
	
	register_post_type( 'landingcube_page', $args );
}
add_action ( 'init', 'fca_lcwp_register_post_type' );

//CHANGE CUSTOM 'UPDATED' MESSAGES FOR OUR CPT
function fca_lcwp_post_updated_messages( $messages ){
	
	$post = get_post();
	$preview_url = fca_lcwp_post_url( $post->ID );

	$messages['landingcube_page'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => sprintf( __( 'Landing Page updated. %sView Page%s','landingcube-wp'), "<a href='$preview_url' target='_blank'>", '</a>' ),
		2  => sprintf( __( 'Landing Page updated. %sView Page%s','landingcube-wp'), "<a href='$preview_url' target='_blank'>", '</a>' ),
		3  => __( 'Landing Page deleted.','landingcube-wp'),
		4  => sprintf( __( 'Landing Page updated.  %sView Page%s.','landingcube-wp'), "<a href='$preview_url' target='_blank'>", '</a>' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Landing Page restored to revision from %s','landingcube-wp'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Landing Page published.' ,'landingcube-wp'),
		7  => __( 'Landing Page saved.' ,'landingcube-wp'),
		8  => __( 'Landing Page submitted.' ,'landingcube-wp'),
		9  => sprintf(
			__( 'Landing Page scheduled for: <strong>%1$s</strong>.','landingcube-wp'),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Landing Page draft updated.' ,'landingcube-wp'),
	);

	return $messages;
}
add_filter('post_updated_messages', 'fca_lcwp_post_updated_messages' );

function fca_lcwp_post_type_url( $url, $post ) {
	if ( get_post_type( $post ) === 'landingcube_page' ) {
		return fca_lcwp_post_url( $post->ID );
	}
	return $url;
}
add_filter( 'post_type_link', 'fca_lcwp_post_type_url', 10, 2 );

//Customize CPT table columns
function fca_lcwp_add_new_post_table_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb'] = '<input type="checkbox" />';
	$new_columns['title'] = __('Name', 'column name', 'landingcube-wp' );
	$new_columns['type'] = __('Type', 'landingcube-wp' );
	$new_columns['url'] = __('URL', 'landingcube-wp' );
	$new_columns['date'] = __('Date', 'column name', 'landingcube-wp' );
 
	return $new_columns;
}
add_filter('manage_landingcube_page_posts_columns', 'fca_lcwp_add_new_post_table_columns', 10, 1 );

function fca_lcwp_manage_post_table_columns( $column_name, $id ) {
	
	
	switch ( $column_name ) {
		case 'type':
			$meta = get_post_meta( $id, 'fca_lcwp', true );		
			echo isSet( $meta['campaign_type'] ) ? ucwords( str_replace( '_', ' ', $meta['campaign_type'] ) ) : '';
			break;
		
		case 'url':
			echo '<a href="' . fca_lcwp_post_url( $id ) . '" target="_blank" >' . fca_lcwp_post_url( $id ) . '</a>';
			break;
		
	}
}
add_action('manage_landingcube_page_posts_custom_column', 'fca_lcwp_manage_post_table_columns', 10, 2);