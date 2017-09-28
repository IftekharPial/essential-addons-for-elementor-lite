( function( $ ) {
	'use strict';
	// Init jQuery Ui Tabs
	$( ".eael-settings-tabs" ).tabs();

	$( '.eael-get-pro' ).on( 'click', function() {
		swal({
	  		title: '<h2><span>Go</span> Premium',
	  		type: 'info',
	  		html:
	    		'Purchase our <b><a href="https://codecanyon.net/item/essential-addons-for-elementor/20278675?ref=Codetic" rel="nofollow">premium version</a></b> to unlock these pro components!',
	  		showCloseButton: true,
	  		showCancelButton: false,
	  		focusConfirm: true,
		});
	} );

	// Adding link id after the url
	$('.eael-settings-tabs ul li a').click(function () {
		var tabUrl = $(this).attr( 'href' );
	   window.location.hash = tabUrl;
	   return false;
	});

	// Saving Data With Ajax Request
	$( 'form#eael-settings' ).on( 'submit', function(e) {
		e.preventDefault();

		var contactForm7 		= $( '#contact-form-7' ).attr( 'checked' ) ? 1 : 0;
		var countDown 			= $( '#count-down' ).attr( 'checked' ) ? 1 : 0;
		var creativeBtn 		= $( '#creative-btn' ).attr( 'checked' ) ? 1 : 0;
		var fancyText 			= $( '#fancy-text' ).attr( 'checked' ) ? 1 : 0;
		var postGrid 			= $( '#post-grid' ).attr( 'checked' ) ? 1 : 0;
		var postTimeline 		= $( '#post-timeline' ).attr( 'checked' ) ? 1 : 0;
		var productGrid 		= $( '#product-grid' ).attr( 'checked' ) ? 1 : 0;
		var teamMembers 		= $( '#team-members' ).attr( 'checked' ) ? 1 : 0;
		var testimonials 		= $( '#testimonials' ).attr( 'checked' ) ? 1 : 0;
		var weForms 			= $( '#weforms' ).attr( 'checked' ) ? 1 : 0;
		var callToAction 		= $( '#call-to-action' ).attr( 'checked' ) ? 1 : 0;
		var flipBox 			= $( '#flip-box' ).attr( 'checked' ) ? 1 : 0;
		var infoBox 			= $( '#info-box' ).attr( 'checked' ) ? 1 : 0;
		var dualHeader 		= $( '#dual-header' ).attr( 'checked' ) ? 1 : 0;

		var customCss 			= $( '#eael-custom-css' ).val();
		var customJs 			= $( '#eael-custom-js' ).val();

		$.ajax( {
			url: settings.ajaxurl,
			type: 'post',
			data: { 
				action: 'save_settings_with_ajax',
				contactForm7: contactForm7, 
				countDown: countDown, 
				creativeBtn: creativeBtn, 
				fancyText: fancyText, 
				postGrid: postGrid, 
				postTimeline: postTimeline, 
				productGrid: productGrid, 
				teamMembers: teamMembers, 
				testimonials: testimonials, 
				weForms: weForms,
				callToAction: callToAction,
				flipBox: flipBox,
				infoBox: infoBox,
				dualHeader: dualHeader,
				customCss: customCss,
				customJs: customJs,
			},
			success: function( response ) {
				swal(
				  'Settings Saved!',
				  'Click OK to continue',
				  'success'
				);
			},
			error: function() {
				swal(
				  'Oops...',
				  'Something went wrong!',
				  'error'
				);
			}
		} );
		
	} );

} )( jQuery );
