/* jshint asi: true */
jQuery(document).ready(function($){

	//SET UP SAVE AND PREVIEW BUTTONS
	var saveButton = '<button type="submit" class="button-primary" id="fca_lcwp_submit_button">Save</buttton>'
	$('#poststuff').append( saveButton )
	$('#fca_lcwp_submit_button').click( function( e ) {
		e.preventDefault()
		$(window).unbind('beforeunload')
		
		// Remove target blank
		var thisForm = $(this).closest('form')
		thisForm.removeAttr('target')

		// Remove preview url
		$('#fca_lcwp_preview_url').val('')
		
		// Submit form
		thisForm.submit()
		
		return false
	})
	
	var previewButton = '<button type="button" class="button-secondary" id="fca_lcwp_preview_button">Save & Preview</buttton>'
	$('#poststuff').append( previewButton )	
	$('#fca_lcwp_preview_button').click(function(e) {
		if( $('.fca-lcwp-deploy_mode').val() === 'homepage' ) {
			$('#fca_lcwp_preview_url').val('')
		}
		e.preventDefault()
		// Add target blank
		var thisForm = $(this).closest('form')
		thisForm.prop('target', '_blank')
		
		// Submit form
		thisForm.submit()
		
		return false
	})

	//ACTIVATE SELECT2
	$('.fca-lcwp-campaign_url').select2()		
	$('.fca-lcwp-campaign_url').change(function(){
		var id = $(this).children(':selected').data('id')
		$('#fca_lcwp_campaign_name').val( fcaLcwpData.campaigns[id].title )
		$('#fca_lcwp_campaign_type').val( fcaLcwpData.campaigns[id].type )
		$('.fca-lcwp-deploy_url_url').val( fcaLcwpData.campaigns[id].slug ).change()
	})
	// ACTIVATE TOOLTIPS
	$('.fca_lcwp_tooltip').tooltipster( {trigger: 'hover', maxWidth: '100%', contentAsHTML: true, arrow: false, theme: ['tooltipster-borderless', 'tooltipster-landing-page-cat'] } )
		
	//DEPLOY MODE TOGGLE
	$('.fca-lcwp-deploy_mode').change(function(){
		if ( $(this).val() === 'url' ) {
			$('#fca-lcwp-redirect-url-input').show()
			$('.fca-lcwp-deploy_url_url').change()
		} else {
			$('#fca-lcwp-redirect-url-input').hide()
		}
	}).change()
	
	//URL / SLUG INPUT CHANGE HANDLER
	$('.fca-lcwp-deploy_url_url').on('input change', function(){
		$('#fca_lcwp_preview_url').val( fcaLcwpData.site_url + '/' + $(this).val() )
	})
	
	var reloadButton = $('#fca-lcwp-select-campaign .fca_lcwp_spinner')
	
	reloadButton.click( function(){
		reloadButton.addClass('spin')
		jQuery.ajax({
			url: fcaLcwpData.ajaxurl,
			type: 'POST',
			data: {
				"action": "fca_lcwp_refresh_posts",
				"nonce": fcaLcwpData.nonce
			}
		}).done( function( response ) {
			reloadButton.removeClass( 'spin' )
			if ( response.success ) {
				fcaLcwpData.campaigns = response.data
				load_campaigns( response.data )
			} else if ( response.data ) {
				alert( response.data )
			}
		})
	})

	//HIDE UNUSED SUBMIT DIV
	$( '#submitdiv' ).hide()
	
	//FIX/HIDE SIDEBAR METABOX AREA
	$('.empty-container').removeClass('empty-container')
	$('#side-sortables').sortable({
        disabled: true
    })
	
    $('.postbox .hndle').css('cursor', 'pointer')
	
	$('#postimagediv').hide()
	
	//SHOW OUR MAIN DIV AFTER WE'RE DONE WITH DOM CHANGES
	$( '#wpbody-content').show()
	
	//UPDATE CAMPAIGN LIST SELECT
	function load_campaigns( campaigns ) {
		
		var $target = $('.fca-lcwp-campaign_url')
		
		var selectedUrl = $target.val()
		var lowPriority = [] //ONES TO SHOW AFTER RUNNING CAMPAIGNS
		$target.children('option').remove()
		
		$target.append('<option></option>')
		
		for ( prop in campaigns ) {
			var id = campaigns[prop].id
			var url = campaigns[prop].url
			var text = campaigns[prop].title + ' [' + campaigns[prop].status + ']'
			var selected = url === selectedUrl ? "selected='selected'" : ''
			
			if ( campaigns[prop].status !== 'Running' ) {
				lowPriority[id] = campaigns[prop]
				continue
			}
				
			$target.append( "<option " + selected + " data-id='" + prop +"' value='" + url + "'>" + text + "</option>" )	
		}
		
		for ( prop in lowPriority ) {
			var url = lowPriority[prop].url
			var text = lowPriority[prop].title + ' [' + lowPriority[prop].status + ']'
			var selected = url === selectedUrl ? "selected='selected'" : ''
				
			$target.append( "<option " + selected + " data-id='" + prop +"' value='" + url + "'>" + text + "</option>" )	
		}

	}
	load_campaigns( fcaLcwpData.campaigns )
})
