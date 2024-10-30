<?php 

global $post, $wp_query;
if ( !empty ( $_GET['landingcube_page'] ) ) {
	$post = get_post( $_GET['landingcube_page'] );
}

if ( is_404() ) {
	$wp_query->is_404 = false;		
	status_header(200);
}

$meta = get_post_meta( $post->ID, 'fca_lcwp', true );
$meta = empty( $meta ) ? array() : $meta;

$campaign_url = empty( $meta['campaign_url'] ) ? false : $meta['campaign_url'];
$title = get_the_title( $post->ID );
$embed_src = 'https://pages.landingcube.com/embed.min.js?' . current_time('timestamp');

?>
<!doctype html>
<html>
	<head>
		<title><?php echo esc_html( $title ) ?></title>
		<style>iframe{display:block}#fca-sp-icon-spin{display:none;}</style>
		<?php wp_head(); ?>
		<meta name='viewport' content='width=device-width, initial-scale=1'>
	</head>
	<body style='margin: 0;'><script data-promo-url='<?php echo $campaign_url ?>' id='landingcube_embed' src='<?php echo $embed_src ?>'></script></body>
</html>