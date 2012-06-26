/*
* Dashboard js
*/
(function($, mw){
	var $dashtoc = $('<ul id="dashtoc"></ul>');
	var $dashboard = $( '#dashboard' )
	.addClass( 'jsdash' )
	.before( $dashtoc );
	var $fieldsets = $dashboard.children( 'fieldset' )
	.hide()
	.addClass( 'dashsection' );
	var $legends = $fieldsets.children( 'legend' )
	.addClass( 'mainLegend' );
	
	$legends.each( function( i, legend ) {
		var $legend = $(legend);
		if ( i === 0 ) {
			$legend.parent().show();
		}
		var ident = $legend.parent().attr( 'id' );

		var $li = $( '<li/>', {
			'class' : ( i === 0 ) ? 'selected' : null
		});
		var $a = $( '<a/>', {
			text : $legend.text(),
			id : ident.replace( 'cvext-dashsection', 'dashtab' ),
			href : '#' + ident
		}).click( function( e ) {
			e.preventDefault();
			// Handle hash manually to prevent jumping
			// Therefore save and restore scrollTop to prevent jumping
			var scrollTop = $(window).scrollTop();
			window.location.hash = $(this).attr('href');
			$(window).scrollTop(scrollTop);

			$dashtoc.find( 'li' ).removeClass( 'selected' );
			$(this).parent().addClass( 'selected' );
			$( '#dashboard > fieldset' ).hide();
			$( '#' + ident ).show();
		});
		$li.append( $a );
		$dashtoc.append( $li );
	} );
	
	$( function() {
		var hash = window.location.hash;
		if( hash.match( /^#cvext-dashsection-[\w-]+/ ) ) {
			var $tab = $( hash.replace( 'cvext-dashsection', 'dashtab' ) );
			$tab.click();
		}
	} );
	
})(jQuery, mediaWiki);