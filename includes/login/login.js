/* jshint asi: true */

if ( fcaLcwpSettingsData.logged_in ) {
	if ( confirm(fcaLcwpSettingsData.logout_msg) ) {
		window.location.href = window.location.href + '&logout=true'
	}
}

if ( fcaLcwpSettingsData.new_login ) {
	if ( confirm(fcaLcwpSettingsData.new_login_msg) ) {
		window.location.href = fcaLcwpSettingsData.redirect
	}
}
