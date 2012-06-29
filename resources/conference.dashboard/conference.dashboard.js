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
	
	
	/*function for adding ajax listeners */
		var pageSuccessHandler = function(jsondata,event){
			
			var target = event.target;
			var parent = $(target).parent().get(0);
			var action = $(target).attr('href');
			action=action.replace('#','');
			var result = jsondata.pagecreate;
			var success = false;
			var createtext = $(target).text();
			var edittext = $(parent).children('span:first').text();
			var deletetext = $(parent).children('span:last').text();
			$(parent).contents().remove();
			//now the <td> element is empty
			if(result.status=='Success')
			{
				success= true;
			} 	
			if(action=='add')
			{
				
				if(success)
				{
					
					//do some modifications in DOM
					//disable create and enable edit | delete links
					$(parent)
					.append('<span></span>')
					.append('<a></a><a></a>')
						.find('span')
							.addClass('absent')
							.text(createtext)
						.end()
						.find('a:first')
							.attr('href','#edit')
							.text(edittext)
							.before(' | ')
							.after(' | ')
						.end()
						.find('a:last')
							.attr('href','#delete')
							.text(deletetext)
						.end();	
	
				}
				 
			} else if (action=='#edit'){
				
			} else if (action=='#delete'){
				
			}
			
		};
		var pageErrorHandler = function(jsondata,event){
			
			
		};
		var orgSuccessHandler = function(jsondata,event){
			
		};
		var orgErrorHandler = function(jsondata, event){
			
		};
		var eventSuccessHandler = function(jsondata,event){
		};
		var eventErrorHandler = function(jsondata, event){
		};	
		var locErrorHandler = function(jsondata, event){
		};	
		var locSuccessHandler = function(jsondata, event){
		};	
		var ajaxPoster = function(ajaxData,successHandler,errorHandler,event){
			$.ajax({
				type:		"POST",
				url:  		mw.util.wikiScript('api'),
				data:		ajaxData,
				dataType:	'json',
				success:	function(jsondata){	
					successHandler(jsondata,event);
				},
				error: 		function(error){	
					errorHandler(error,event);
				}
			});
		};

		$('a').filter('.page').click(function(event){
			
			event.preventDefault();
			var action = $(this).attr('href');
			var value = $(this).parent().parent().find('td:first').text();
			var ajaxData={};
			ajaxData.format='json';
			switch(action){
			case '#add':
				ajaxData.action='pagecreate';
				ajaxData.pagetype=value;
				ajaxData.defaultcontent='true';
				ajaxPoster(ajaxData,pageSuccessHandler,pageErrorHandler,event);
				break;
			case '#edit':
				var formString = '<fieldset>'+
				'<legend></legend>'+
				'<table><tbody>'+
					'<tr><td>'+
						'<label></label>'+
					'</td><td>'+
					'<input type="text"/>'+
					'<button />'+
						'</td></tr>'+
				'</tbody></table>'+
				'</fieldset>';
				$('<div></div>').addClass('opaque')
					.appendTo('body');
				//$('body').css('opacity','0.5');
				$('<div></div>')
				.addClass('dashsection')
				.appendTo('#cvext-dash-form');
				$(formString).attr('id','ajaxform')
					.find('legend')
						.text('Edit the name of this page')
					.end()
					.find('label')
						.text('/pages/')
					.end()
					.find('input:text')
						.attr('id','ajaxpg')
					.end()
					.find(':button')
						.text('Edit').click(function(event){
							ajaxData.action='pageedit';
							ajaxData.pagetype=value;
							ajaxData.pagetypeto=$(this).prev().val();
							ajaxPoster(ajaxData,pageSuccessHandler,pageErrorHandler,event);
						})
					.end()
					.appendTo('div.dashsection');
				
				break;
			case '#delete':	
				ajaxData.action='pagedelete';
				ajaxData.pagetype=value;
				ajaxPoster(ajaxData,pageSuccessHandler,pageErrorHandler,event);
				break;
			default:
				break;
			}
		})
		.end()
		.filter('.org').click(function(event){
			event.preventDefault();
			var action = $(this).attr('href');
			var username=$(this).parent().siblings('td:last').text();
			var ajaxData ={};
			ajaxData.format='json';
			switch(action){
			case "#edit":
				break;
			case "#delete":
				ajaxData.action='orgdelete';
				ajaxData.username=username;
				ajaxPoster(ajaxData,orgSuccessHandler,orgErrorHandler,event);
				break;
			default:
				break;
			}
		})
		.end()
		.filter('.event').click(function(event){
			event.preventDefault();
			var action = $(this).attr('href');
			var $children=$(this).parent().parent().children('td');
			var ajaxData = {};
			ajaxData.format = 'json';
			switch(action){
			case "#edit":
				ajaxData.action='eventedit';
				break;
			case "#delete":
				ajaxData.action='eventdelete';
				ajaxData.starttime=$children.get(1).text();
				ajaxData.endtime=$children.get(2).text();
				ajaxData.day=$children.get(3).text();
				ajaxData.group=$children.get(4).text();
				ajaxData.topic=$children.get(0).text();	
				ajaxPoster(ajaxData,eventSuccessHandler,eventErrorHandler,event);
				break;
			default:
				break;
			}
		}).end()
		.filter('.location').click(function(event){
			event.preventDefault();
			var action = $(this).attr('href');
			var ajaxData={};
			ajaxData.format = 'json';
			switch(action){
			case '#delete':
				ajaxData.action='locdelete';
				ajaxData.roomno=$(this).parent().siblings('td:first').text();
				ajaxPoster(ajaxData, locSuccessHandler, locErrorHandler, event);
				break;
			case '#edit':
				break;
			}
			
		})
		.end();
})(jQuery, mediaWiki);