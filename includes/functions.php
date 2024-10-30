<?php

////////////////////////////
// FUNCTIONS
////////////////////////////

//DETECT BOTS
function fca_lcwp_is_bot() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|facebookexternalhit|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']);
}

//GET CURRENT URL
function fca_lcwp_current_url() {
	$http = 'https';
	if ( !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on' ) {
		$http = 'http';
	}
	$url_parts = parse_url( "$http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" );
	return $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path']; 
}

//TOOLTIP
function fca_lcwp_tooltip( $text = 'Tooltip', $icon = 'dashicons dashicons-editor-help' ) {
	return "<span class='$icon fca_lcwp_tooltip' title='" . htmlspecialchars( $text, ENT_QUOTES ) . "'></span>";
}	

//DELETE ICONS
function fca_lcwp_delete_icons() {
	ob_start(); ?>
		<span class='dashicons dashicons-trash fca_delete_icon fca_delete_button'></span>
		<span class='dashicons dashicons-no fca_delete_icon fca_delete_icon_cancel' style='display:none;'></span>
		<span class='dashicons dashicons-yes fca_delete_icon fca_delete_icon_confirm' style='display:none;'></span>
	<?php
	return ob_get_clean();
}
	
//RETURN GENERIC INPUT HTML
function fca_lcwp_input( $name, $placeholder = '', $value = '', $type = 'input', $atts = '' ) {

	$html = "<div class='fca-lcwp-field fca-lcwp-field-$type'>";
	
		switch ( $type ) {
			
			case 'checkbox':
				$checked = !empty( $value ) && $value !== 'off' ? "checked='checked'" : '';
				
				$html .= "<div class='onoffswitch'>";
					$html .= "<input $atts style='display:none;' type='checkbox' id='fca_lcwp[$name]' class='onoffswitch-checkbox fca-lcwp-input-$type fca-lcwp-$name' name='fca_lcwp[$name]' $checked>"; 
					$html .= "<label class='onoffswitch-label' for='fca_lcwp[$name]'><span class='onoffswitch-inner' data-content-on='ON' data-content-off='OFF'><span class='onoffswitch-switch'></span></span></label>";
				$html .= "</div>";
				break;
				
			case 'textarea':
				$placeholder = htmlspecialchars( $placeholder, ENT_QUOTES );
				$html .= "<textarea $atts placeholder='$placeholder' class='fca-lcwp-input-$type fca-lcwp-$name' name='fca_lcwp[$name]'>$value</textarea>";
				break;
				
			default: 
				$value = htmlspecialchars( $value, ENT_QUOTES );
				$placeholder = htmlspecialchars( $placeholder, ENT_QUOTES );
				$html .= "<input $atts type='$type' placeholder='$placeholder' class='fca-lcwp-input-$type fca-lcwp-$name' name='fca_lcwp[$name]' value='$value'>";
		}
	
	$html .= '</div>';
	
	return $html;
	
}

//SINGLE-SELECT
function fca_lcwp_select( $name, $selected = '', $options = array(), $atts = '' ) {
	$html = "<div class='fca-lcwp-field fca-lcwp-field-select'>";
		$html .= "<select name='fca_lcwp[$name]' class='fca-lcwp-input-select fca-lcwp-$name' $atts>";
			if ( is_array( $options ) ) {
				forEach ( $options as $key => $text ) {
					$sel = $selected === $key ? 'selected="selected"' : '';
					$html .= "<option $sel value='$key'>$text</option>";
				}
			}
		$html .= '</select>';
	$html .= '</div>';
	
	return $html;
}

//LOADING SPINNER
function fca_lcwp_spinner( $title = '' ) {
	$tooltipster = !empty( $title ) ? 'fca_lcwp_tooltip' : '';
	return "<span title='$title' class='fca_lcwp_spinner dashicons dashicons-image-rotate $tooltipster'></span>";
}

//INFO SPAN
function fca_lcwp_info_span( $text = '', $link = '' ) {
	if ( empty( $link ) ) {
		return "<span class='fca_lcwp_info_span'>$text</span>";
	} else {
		return "<span class='fca_lcwp_info_span'><a class='fca_lcwp_api_link' href='$link' target='_blank'>$text</a></span>";
	}
}

function fca_lcwp_sanitize_text_array( $array ) {
	if ( !is_array( $array ) ) {
		return sanitize_text_field( $array );
	}
	foreach ( $array as $key => &$value ) {
		if ( is_array( $value ) ) {
			$value = fca_sp_sanitize_text_array( $value );
		} else {
			$value = sanitize_text_field( $value );
		}
	}

	return $array;
}

//TRY TO MAKE AN SEO FRIENDLY URL BASED ON A STRING
function fca_lcwp_clean_string_for_url( $str ) {
	//TRIM & LOWERCASE
	$str = strtolower( trim( $str ) );
	
	//SIMPLIFY CHARACTERS
	$utf8 = array(
		'/[áàâãªä]/u'	=>	 'a',
		'/[ÁÀÂÃÄ]/u'	=>	 'A',
		'/[ÍÌÎÏ]/u'		=>	 'I',
		'/[íìîï]/u'		=>	 'i',
		'/[éèêë]/u'		=>	 'e',
		'/[ÉÈÊË]/u'		=>	 'E',
		'/[óòôõºö]/u'	=>	 'o',
		'/[ÓÒÔÕÖ]/u'	=>	 'O',
		'/[úùûü]/u'		=>	 'u',
		'/[ÚÙÛÜ]/u'		=>	 'U',
		'/ç/'			=>	 'c',
		'/Ç/'			=>	 'C',
		'/ñ/'			=>	 'n',
		'/Ñ/'			=>	 'N',
		'/–/'			=>	 '-', // UTF-8 hyphen to "normal" hyphen
		'/[’‘‹›‚]/u'	=>	 ' ', // Literally a single quote
		'/[“”«»„]/u'	=>	 ' ', // Double quote
		'/ /'			=>	 ' ', // nonbreaking space (equiv. to 0x160)
	);
	
	$str = preg_replace( array_keys( $utf8 ), array_values( $utf8 ), $str );
	
	// CONVERT HYPHENS AND CLEAN
	return preg_replace( array( '#[\\s-]+#', '#[^A-Za-z0-9\ -]+#' ), array( '-', '' ), $str );
}

function fca_lcwp_post_url( $post_id ) {
	$meta = get_post_meta ( $post_id, 'fca_lcwp', true );
	$mode = empty( $meta['deploy_mode'] ) ? 'url' : $meta['deploy_mode'];
	$slug = empty( $meta['deploy_url_url'] ) ? false : $meta['deploy_url_url'];
	
	if ( $mode === 'homepage' OR empty( $slug ) ) {
		return get_site_url();
	}
	if ( $mode === 'url' && $slug ) {
		return get_site_url() . '/' . $slug;
	}
	
	return get_site_url() . '?landingcube_page=' . $post_id;
	
}