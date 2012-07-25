/* @todo - call to ApiConferenceDelete line no.1144 */
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
	
	
	var imagePath = mw.config.get( 'wgExtensionAssetsPath' ) + '/ConventionExtension/resources/conference.dashboard/images';
	
	function Handlers(){
		
		this.months = ['Jan',
				'Feb',
				'Mar',
				'Apr',
				'May',
				'Jun',
				'Jul',
				'Aug',
				'Sep',
				'Oct',
				'Nov',
				'Dec'];
		this.page = {
			
				
			add:function(event){
				
				
				event.preventDefault();
				var $this = $(event.target);
				var value = $this.parent().parent().find('td:first').text();
				var ajaxData = {
						format:			'json',
						action:			'pagecreate',
						pagetype:		value,
						defaultcontent:	'true'
				};
				$('<td><img></img></td>')
				.find('img')
				.attr({'src':imagePath+'/loading-icon.gif','alt':'loading icon'})
				.end()
				.appendTo($this.parent().parent());
				ajaxPoster(ajaxData,function(jsondata,event){
					var target = event.target;
					var parent = $(target).parent().get(0);
					var value = $(target).parent().parent().find('td:first').text();
					var result = jsondata.pagecreate;
					var success = false;
					$.fx.off=false;
					if(result)
					{
						if(result['done'])
						{
							var createtext = $(target).text();
							var edittext = $(parent).children('span:first').text();
							var deletetext = $(parent).children('span:last').text();
							$(parent).contents().remove();
							//now the <td> element is empty
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
								.attr({'href':'#edit','class':'page'})
								.click(function(event){
										handlers.page.edit(event);
									})
								.text(edittext)
								.before(' | ')
								.after(' | ')
							.end()
							.find('a:last')
								.attr({'href':'#delete','class':'page'})
								.click(function(event){
											handlers.page.del(event);
										})
								.text(deletetext)
							.end()
							.before('<td></td>')
							.prev('td')
								.append('<a></a>')
								.find('a')
									.attr('href',result.pageurl)
									.text(result.pagetype)
								.end()
							.end()	
							.parent('tr')
								.find('img')
									.attr('src',imagePath+'/check_icon.gif')
									.after('   '+result.msg)
								.end()
								.find('td:last')
									.css({'font-style':'italic','color':'#1f1f1f'})
									.fadeOut('1500')
								.end()
								.find('td:first')
									.remove()
								.end();
						} else {
							$(parent).parent()
							.find('td > img')
								.attr('src',imagePath+'/icon_tiny_question_mark.gif')
								.after('   '+result.msg)
								.fadeOut('500')
							.end();
						}	
					} else {
						$(parent).parent()
						.find('td > img')
							.remove()
							.after('   '+result.msg)
						.end();
					}		
						
				},event);
			},
			
			
			edit:function(event){
				
				
				var target = event.target;
				var parent = $(target).parent().get(0);
				var value = $(target).parent().parent().find('td:first').text();
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
				$('<div></div>')
				.addClass('dashsection')
				.append('<img></img>')
				.find('img')
					.attr({'class':'closeIcon','src':imagePath+'/close-icon.gif','alt':'close icon'})
					.click(function(){
						$('div.opaque').remove();
						$('div.dashsection').remove();
					})
				.end()
				.appendTo('#mw-content-text');
				
				$(formString).attr('id','ajaxform').fadeIn('3000')
					.find('legend')
						.text('Edit the name of this page')
					.end()
					.find('label')
						.text('/pages/')
					.end()
					.find('input:text')
						.attr('id','ajaxpg')
						.val(value)
					.end()
					.find(':button')
						.text('Edit')
						.click(function(event){
								var ajaxData = {
										format:		'json',
										action:		'pageedit',
										pagetype:	value,
										pagetypeto:	$(this).prev().val()
								};
								ajaxPoster(ajaxData,function(jsondata,event){
									var result = jsondata.pageedit;
									var $div = $('div.dashsection');
									var msg ='';
									var pClass ='';
									if(result)
									{
										if(result['done'])
										{
											/* success */
											
											$div.find('fieldset').remove().end();
											$(parent)
											.siblings('td:first')
												.find('a')
													.attr('href',result.urlto)
													.text(result.pagetypeto)
												.end();
											pClass = 'noerrAjax';
											
										} else {
											/* failure */
											pClass = 'errAjax';
										}
										msg = result.msg;
									} else {
										/* api error */
										msg = jsondata.error.info;
										pClass = 'errAjax';
									}	
									
									$div
									.css('height','50px')
									.append('<p></p>')
									.find('p')
										.text(result.msg)
										.addClass(pClass)
									.end();	
								},event);
							})
					.end()
					.appendTo('div.dashsection');
			},
			
			
			del:function(event){
				
				
				var target = event.target;
				var parent = $(target).parent().get(0);
				var value = $(target).parent().parent().find('td:first').text();
				$('<div></div>').addClass('opaque')
				.appendTo('body');
				$('<div><table><tr><td></td></tr><tr><td><button /><button /></td></tr></table></div>').addClass('delsection')
				.find('table')
					.attr('id','delform')
				.end()	
				.find('td:first')
					.text('Are you sure ?')
					.addClass('textCenter')
				.end()
				.find(':button:first')
					.text('Delete')
					.click(function(event){
						var ajaxData = {
								format:'json',
								action:'pagedelete',
								pagetype:value
						};
						ajaxPoster(ajaxData,function(jsondata,event){
							
							var result = jsondata.pagedelete;
							var msg = '';
							var pClass = '';
							if(result)
							{
								if(result['done'])
								{
									
									/* remove the delete and cancel buttons */
									
									$('#delform').remove();
									pClass ='noerrAjax';
									if(result['preloaded'])
									{
										var ctext = $(parent).children('span:first').text();
										var etext = $(parent).children('a:first').text();
										var dtext = $(parent).children('a:last').text();
										$(parent).contents().remove();
										$(parent)
										.append('<a></a>')
										.find('a')
											.text(ctext)
											.addClass('page')
											.attr('href','#add')
											.click(function(event){
												handlers.page.add(event);
											})
										.end()
										.append('<span></span>')
										.find('span:first')
											.addClass('absent')
											.text(etext)
											.before(' | ')
											.after(' | ')
										.end()
										.append('<span></span>')
										.find('span:last')
											.addClass('absent')
											.text(dtext)
										.end()
										.before('<td></td>')
											.prev('td')
												.text(result.pagetype)
											.end()
										.siblings('td:first')
										 .remove();
									} else {
										
										$(parent).parent('tr').remove();
									}	
									/*  
									*/
								} else {
									pClass = 'errAjax';
								}
								msg = result.msg;
							} else {
								msg = jsondata.error.info;
								pClass = 'errAjax';
							}	
							
							
							$('div.delsection')
							.find('table')
								.remove()
							.end()
							.append('<img />')
							.find('img')
								.attr('src',imagePath+'/close-icon.gif')
								.addClass('closeIcon')
								.click(function(event){
									$('div.opaque').remove();
									$('div.delsection').remove();
								})
							.end()	
							.append('<p></p>')
							.find('p')
								.text(result.msg)
								.addClass(pClass)
							.end();
							
						},event);
					})
				.end()
				.find(':button:last')
					.text('Cancel')
					.click(function(event){
						$('div.opaque').remove();
						$('div.delsection').remove();
					})
				.end()
				.appendTo('#mw-content-text');
				
				
			},
			
			
			addNew:function(event){

				event.preventDefault();
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
				$('<div></div>')
				.addClass('pagecreatesection')
				.append('<img />')
				.find('img')
					.attr({'src':imagePath+'/close-icon.gif','alt':'close icon','class':'closeIcon'})
					.click(function(event){
						$('div.opaque').remove();
						$('div.pagecreatesection').remove();
					})
				.end()
				.appendTo('#mw-content-text');
				$(formString).attr('id','ajaxform').fadeIn('3000')
				.find('legend')
					.text('Create New Page')
				.end()
				.find('label')
					.text('/pages/')
				.end()
				.find('input:text')
					.attr('id','ajaxpg')
				.end()
				.find(':button')
					.text('Create')
					.click(function(event){
							var ajaxData = {
									format:			'json',
									action:			'pagecreate',
									pagetype:		$(this).parent('td').find('input:text').val(),
									defaultcontent:	'false'
							};
							ajaxPoster(ajaxData,function(jsondata,event){
								var result = jsondata.pagecreate;
								if(result)
								{
									if(result['done'])
									{
										$('div.pagecreatesection')
										.css('height','50px')
										.find('fieldset')
											.remove()
										.end()
										.append('<p></p>')
										.find('p')
											.text(result.msg)
											.addClass('noerrAjax')
										.end();
										$('#cvext-dashsection-pages')
										.find('table')
											.append('<tr><td></td><td></td></tr>')
											.find('tr:last')
												.addClass('cvext-res')
												.find('td:first')
													.append('<a></a>')
														.find('a')
															.attr({'href':result.pageurl})
															.text(result.pagetype)
														.end()
												.end()
												.find('td:last')
													.append('<span></span><a></a><a></a>')
													.find('span')
														.text('Create')
														.addClass('absent')
													.end()
													.find('a:first')
														.attr({'href':'#edit','class':'page'})
														.text('Edit')
														.before(' | ')
														.after(' | ')
														.click(function(event){
															handlers.page.edit(event);
														})
													.end()
													.find('a:last')
														.attr({'href':'#delete','class':'page'})
														.text('Delete')
														.click(function(event){
															handlers.page.del(event);
														})
													.end()
												.end()
											.end()
										.end();
									} else {	/* failure */
										$('div.pagecreatesection')
										.append('<p></p>')
										.find('p')
											.addClass('errAjax')
											.text(result.msg)
										.end();
									}
									
									
								} else {
									result = jsondata.error;
									$('div.pagecreatesection')
									.find('fieldset')
										.remove()
									.end()
									.append('<p></p>')
									.find('p')
										.text(result.info)
									.end();
								}
								
								
							},event);
						})
				.end()
				.appendTo('div.pagecreatesection');
			}
		};
		
		
		this.location = {
				
				
				edit:function(event){
					
					
					event.preventDefault();
					var target = event.target;
					var siblings = $(target).parent().siblings('td');
					var roomNo = siblings.filter('td:first').text();
					var description = siblings.filter('td:eq(1)').text();
					var url = siblings.filter('td:last').text();
					//also get the option element in events fieldset which we are gonna modify if everything goes fine
					var optionElem =$('#cvext-dashsection-evts option[value="'+roomNo+'"]');	
					//create the edit form 
					//instead of creating a new one just replicate it from the existing add form
					var formFieldset = $('#cvext-dashsection-lcts > fieldset:first').clone();
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div></div>')
					.addClass('loceditsection')
					.append('<img />')
					.find('img')
						.attr({'src':imagePath+'/close-icon.gif','alt':'closeIcon','class':'closeIcon'})
						.click(function(event){
							$('div.opaque').remove();
							$('div.loceditsection').remove();
						})
					.end()
					.append(formFieldset)
					.find('p')
						.remove()
					.end()
					.find('fieldset')
						.attr('id','locajaxform')
					.end()
					.find('legend')
						.text('Edit Location Details')
					.end()
					.find('input:submit')
					.remove()
					.end()
					.find('input:text:first')
						.val(roomNo)
					.end()
					.find('input:text:eq(1)')
						.val(description)
					.end()
					.find('input:text:last')
						.val(url)
					.end()
					.find('td:last')
						.append('<button />')
						.find('button')
							.text('Edit')
							.attr('id','loceditbtn')
							.click(function(event){
								var superParent = $(this).parent('td').parent('tr').parent('tbody');
								var ajaxData = {
										format:			'json',
										action:			'locedit',
										roomno:			roomNo,
										roomnoto:		$(superParent).find('input:text:first').val(),
										description:	$(superParent).find('input:text:eq(1)').val(),
										url:			$(superParent).find('input:text:last').val()
								};
								ajaxPoster(ajaxData,function(jsondata,event){
									var result = jsondata.locedit;
									var msg='';
									var pClass = '';
									var $loceditsec = $('div.loceditsection');
									if(result)
									{
										if(result['done'])
										{
											siblings.filter('td:first').find('a').attr('href',result.locurl).text(result.roomno);
											siblings.filter('td:eq(1)').text(result.description);
											siblings.filter('td:last').text(result.url);
											msg=result.msg;
											pClass = 'noerrAjax';
											optionElem.text(result.roomno);
											$loceditsec.css('height','50px').find('fieldset').remove();
										} else {
											pClass ='errAjax';
										}	
										msg = result.msg;
									} else {
										msg=jsondata.error.info;
										pClass = 'errAjax';
									}
									
									$loceditsec
									.append('<p></p>')
									.find('p')
										.text(msg)
										.addClass(pClass)
									.end();	
									
								},event);
							})
						.end()
					.end()	
					.appendTo('#mw-content-text');
				},
				
				
				del:function(event){
					
					
					event.preventDefault();
					var target = event.target;
					var siblings = $(target).parent().siblings('td');
					var roomNo = siblings.filter('td:first').text();
					var optionElem = $('#location > option[value="'+roomNo+'"]');
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div><table><tr><td></td></tr><tr><td><button /><button /></td></tr></table></div>').addClass('delsection')
					.find('table')
						.attr('id','delform')
					.end()	
					.find('td:first')
						.text('Are you sure ?')
						.addClass('textCenter')
					.end()
					.find(':button:first')
						.text('Delete')
						.click(function(event){
							var ajaxData = {
									format:	'json',
									action:	'locdelete',
									roomno:	roomNo
							};
							ajaxPoster(ajaxData, function(jsondata,event){
								var result = jsondata.locdelete;
								var msg='';
								var pClass = '';
								if(result)
								{
									if(result['done'])
									{
										$(target).parent('td').parent('tr').remove();
										optionElem.remove();
										pClass = 'noerrAjax';
									} else {
										pClass = 'errAjax';
									}	
									msg = result.msg;
								} else {
									msg=jsondata.error.info;
									pClass = 'errAjax';
								} 
								$('div.delsection')
								.append('<img />')
								.find('img')
									.attr({'src':imagePath+'/close-icon.gif','alt':'close icon','class':'closeIcon'})
									.click(function(event){
										$('div.opaque').remove();
										$('div.delsection').remove();
									})
								.end()
								.append('<p></p>')
								.find('p')
									.text(msg)
									.addClass(pClass)
								.end()
								.find('table')
								.remove();
							},event);
						})
					.end()
					.find(':button:last')
						.text('Cancel')
						.click(function(event){
							$('div.opaque').remove();
							$('div.delsection').remove();
						})
					.end()
					.appendTo('#mw-content-text');
				},
				
				
				submit:function(event){
					
					
					//fetch all the values
					event.preventDefault();
					var $tbody = $(event.target).parent('td').parent('tr').parent('tbody');
					var roomNo = $tbody.find('input:text:first').val();
					var description = $tbody.find('input:text:eq(1)').val();
					var url = $tbody.find('input:text:last').val();
					//now make the ajax data object and pass it to ajaxPoster
					var ajaxData = {
							format:			'json',
							action:			'loccreate',
							roomno:			roomNo,
							description:	description,
							url:			url
					};
					ajaxPoster(ajaxData, function(jsondata, event){
						var result = jsondata.loccreate;
						var msg = '';
						var $fieldSets = $('#cvext-dashsection-lcts > fieldset');
						var pClass ='';
						if(result)
						{
							if(result['done'])
							{
								$fieldSets
								.filter('fieldset:last')
									.find('tbody')
									.append('<tr><td></td><td></td><td></td><td><a></a><a></a></td></tr>')
										.find('tr:last')
											.find('td:first')
												.append('<a></a>')
												.find('a')
													.attr('href',result.locurl)
													.text(result.roomno)
												.end()
											.end()
											.find('td:eq(1)')
												.text(result.description)
											.end()
											.find('td:eq(2)')
												.text(result.url)
											.end()
											.find('a:not(:first):not(:last)')
												.text('Edit')
												.attr({'href':'#edit','class':'location'})
												.click(function(event){
													handlers.location.edit(event);
												})
											.end()
											.find('a:last')
												.text('Delete')
												.before(' | ')
												.attr({'href':'#delete','class':'location'})
												.click(function(event){
													handlers.location.del(event);
												})
											.end()
										.end()
									.end();
								$('#location').append('<option></option>')
								.find('option:last')
									.attr('value',result.roomno)
									.text(result.roomno)
								.end();
								pClass = 'noerrAjax';
							} else {
								pClass ='errAjax';
							}	
							msg=result.msg;
							
						} else {
							msg = jsondata.error.info;
							pClass = 'errAjax';
							
						}
						
						var $first = $fieldSets.filter(':first');
						$first
							.find('p')
								.remove()
							.end()
							.append('<p></p>')
							.find('p')
								.addClass(pClass)
								.text(msg)
							.end();
						
						
					},event);
				}
		};
		this.eventz = {
				
				
				edit:function(event){
					
					
					var $siblings=$(event.target).parent().siblings('td');
					var topicOld = $siblings.filter(':first').text();
					var starttimeOld = $siblings.filter(':eq(1)').text();
					var endtimeOld = $siblings.filter(':eq(2)').text();
					var dayOld = $siblings.filter(':eq(3)').text();
					var groupOld = $siblings.filter(':eq(4)').text();
					var locationOld = $siblings.filter(':last').text();
					var formFieldset = $('#cvext-dashsection-evts > fieldset:first').clone();
					$('<div></div>').addClass('opaque').appendTo('body');
					$('<div></div>').addClass('evteditsection')
					.append('<img />')
					.find('img')
						.attr({'src':imagePath+'/close-icon.gif','alt':'closeIcon','class':'closeIcon'})
						.click(function(event){
							$('div.opaque').remove();
							$('div.evteditsection').remove();
						})
					.end()
					.append(formFieldset)
					.find('p')
						.remove()
					.end()
					.find('fieldset')
						.attr('id','evtajaxform')
					.end()
					.find('legend')
						.text('Edit Event Details')
					.end()
					.find('input:text:first')
						.val(topicOld)
					.end()
					.find('select:first')
						.val(groupOld)
					.end()
					.find('select:not(:first):not(:last)')
						.val(dayOld)
					.end()
					.find('select:last')
						.val(locationOld)
					.end()
					.find('input:text:not(:first):not(:last)')
						.val(starttimeOld)
					.end()
					.find('input:text:last')
						.val(endtimeOld)
					.end()
					.find('td:last')
						.find('input:submit:first')
							.remove()
						.end()	
						.append('<button />')
						.find(':button:last')
							.text('Edit')
							.click(function(event){
							//alert($(this).data('events'));	
							//get the new values
							var $superParent=$(this).parent().parent().parent();
							dayOld = handlers.utils.textToDate(dayOld);
							var dayto = $superParent.find('select:not(:first):not(:last)').val();
							dayto = handlers.utils.textToDate(dayto);
							var ajaxData = {
									format:			'json',
									action:			'eventedit',
									starttime:		starttimeOld,
									endtime:		endtimeOld,
									topic:			topicOld,
									group:			groupOld,
									day:			dayOld,
									starttimeto:	$superParent.find('input:text:not(:first):not(:last)').val(),
									endtimeto:		$superParent.find('input:text:last').val(),
									topicto:		$superParent.find('input:text:first').val(),
									groupto:		$superParent.find('select:first').val(),
									dayto:			dayto,
									locationto:		$superParent.find('select:last').val()
										
							};
							ajaxPoster(ajaxData,function(jsondata,exevent){
								var result= jsondata.eventedit;
								var pClass = 'errAjax';
								var msg = '';
								var $editSection = $('div.evteditsection');
								if(result)
								{
									if(result['done'])
									{
										
										if(!result['noedit'])
										{
											var dayRes = handlers.utils.dateToText(result.day);
											$editSection
											.css('height','75px');
											$siblings
											.filter(':first')
												.find('a')
													.attr('href',result.eventurl)
													.text(result.topic)
												.end()	
											.end()
											.filter(':eq(1)')
												.text(result.starttime)
											.end()
											.filter(':eq(2)')
												.text(result.endtime)
											.end()
											.filter(':eq(3)')
												.text(dayRes)
											.end()
											.filter(':eq(4)')
												.text(result.group)
											.end()
											.filter(':last')
												.find('a')
													.attr('href',result.locationurl)
													.text(result.location)
												.end()
											.end();	
											$('#evtajaxform').remove();
										} 	
										
										pClass = 'noerrAjax';
									}	
									msg = result.msg;
								} else {
									msg = jsondata.error.info;
								}
								
								$editSection
								.append('<p></p>')
								.find('p')
									.text(msg)
									.addClass(pClass)
								.end();
							},event);
								
						})
						.end()
					.end()
					.appendTo('#mw-content-text');
				},
				
				del:function(event){
					
					
					var target = event.target;
					var $siblings=$(event.target).parent().siblings('td');
					var topicOld = $siblings.filter(':first').text();
					var starttimeOld = $siblings.filter(':eq(1)').text();
					var endtimeOld = $siblings.filter(':eq(2)').text();
					var dayOld = $siblings.filter(':eq(3)').text();
					dayOld = handlers.utils.textToDate(dayOld);
					var groupOld = $siblings.filter(':eq(4)').text();
					var locationOld = $siblings.filter(':last').text();
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div><table><tr><td></td></tr><tr><td><button /><button /></td></tr></table></div>').addClass('delsection')
					.find('table')
						.attr('id','evtdelform')
					.end()	
					.find('td:first')
						.text('Are you sure ?')
						.addClass('textCenter')
					.end()
					.find(':button:first')
						.attr('id','evtdelbtn')
						.text('Delete')
						.click(function(event){
							var ajaxData = {
									format:		'json',
									action:		'eventdelete',
									topic:		topicOld,
									starttime:	starttimeOld,
									endtime:	endtimeOld,
									day:		dayOld,
									group:		groupOld
							};
							var toDelete = $(target).parent().parent('tr');
							var table = $(this).parents('div.delsection table');
							var delsection = $(this).parents('div.delsection');
							ajaxPoster(ajaxData, function(jsondata,event){
								var result = jsondata.eventdelete;
								var msg = '';
								var pClass = 'errAjax';
								if(result)
								{
									if(result['done'])
									{
										$(toDelete).remove();
										table.remove();
										$(delsection).append('<img />')
										.find('img')
											.attr({'src':imagePath+'/close-icon.gif','alt':'close icon','class':'closeIcon'})
											.click(function(event){
												$('div.opaque').remove();
												$('div.delsection').remove();
											})
										.end();
										
									}
									pClass = 'noerrAjax';
									msg = result.msg;
								} else {
									msg = jsondata.error.info;
								}
								
								$(delsection)
								.css('width','200px')
								.append('<p></p>')
								.find('p')
									.text(msg)
									.addClass(pClass)
								.end();	
								
							},event);
						})
					.end()
					.find(':button:last')
						.text('Cancel')
						.click(function(event){
							$('div.opaque').remove();
							$('div.delsection').remove();
						})
					.end()
					.appendTo('#mw-content-text');
				},
				
				submit:function(event){
					
					
					event.preventDefault();
					var $siblings = $(event.target).parent().parent('tr').parent('tbody').find('input:text,select');
					var textBoxes = $siblings.filter('input:text');
					var comboBoxes = $siblings.filter('select');
					var topic = textBoxes.filter(':first').val();
					var endtime = textBoxes.filter(':last').val();
					var starttime = textBoxes.filter(':not(:first):not(:last)').val();
					var group = comboBoxes.filter(':first').val();
					var location = comboBoxes.filter(':last').val();
					var day = comboBoxes.filter(':not(:first):not(:last)').val();
					day = handlers.utils.textToDate(day);
					var ajaxData = {
							format:		'json',
							action:		'eventcreate',
							starttime:	starttime,
							endtime:	endtime,
							day:		day,
							topic:		topic,
							group:		group,
							location:	location
					};
					ajaxPoster(ajaxData, function(jsondata,event){
						var msg = '';
						var pClass ='';
						var result = jsondata.eventcreate;
						if(result)
						{
							if(result['done'])
							{
								$('#cvext-dashsection-evts > fieldset:last tr:last')//get the last tr element of table and add a new row to it
								.after('<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>')//create a whole new row
								.parent()
								.find('tr:last')
									.find('td:first')
										.append('<a></a>')
										.find('a')
											.attr('href',result.eventurl)
											.text(result.topic)
										.end()
									.end()
									.find('td:last')
										.append('<a></a><a></a>')
										.find('a:first')
											.attr({'href':'#edit','class':'event'})
											.text('Edit')
											.after(' | ')
											.click(function(event){
												handlers.eventz.edit(event);
											})
										.end()
										.find('a:last')
											.attr({'href':'#delete','class':'event'})
											.text('Delete')
											.click(function(event){
												handlers.eventz.del(event);
											})
										.end()
									.end()
									.find('td:eq(1)')
										.text(result.starttime)
									.end()
									.find('td:eq(2)')
										.text(result.endtime)
									.end()
									.find('td:eq(3)')
										.text(handlers.utils.dateToText(result.day))
									.end()
									.find('td:eq(4)')
										.text(result.group)
									.end()
									.find('td:eq(5)')
										.append('<a></a>')
										.find('a')
											.attr('href',result.locationurl)
											.text(result.location)
										.end()
									.end()
								.end();
								pClass = 'noerrAjax';
								textBoxes.val('');
								comboBoxes.val('');
							} else {
								pClass = 'errAjax';
							}	
					
							msg = result.msg;
							
						} else {
							msg=jsondata.error.info;
							pClass = 'errAjax';
						}

						$('#cvext-dashsection-evts > fieldset:first')
						.find('p')
							.remove()
						.end()	
						.append('<p></p>')
						.find('p')
							.addClass(pClass)
							.text(msg)
						.end();
						
					},event);
				}
		};
		
		
		this.conference = {
			
				
				edit:function(event){
					
					
					var formFieldset = $('#cvext-dashsection-confdetails > fieldset:first').clone();
					//fetch the values
					var target= event.target;
					var $superParent = $(target).parent('td').parent('tr').parent('tbody');
					var tdElements = $superParent.find('td:odd');
					var title = tdElements.filter(':first').text();
					var description = tdElements.filter(':eq(5)').text();
					var capacity = tdElements.filter(':eq(4)').text();
					var startdate = tdElements.filter(':eq(1)').text();
					startdate = handlers.utils.textToDate(startdate);
					var enddate = tdElements.filter(':eq(2)').text();
					enddate = handlers.utils.textToDate(enddate);
					var venue = tdElements.filter(':eq(3)').text();
					var venueArray = venue.split(',');
					var place = venueArray[0];
					var city = venueArray[1];
					var country = venueArray[2];	
					//modify this formFieldset
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div></div>')
					.addClass('confeditsection')
					.append('<img />')
					.find('img')
						.attr({'src':imagePath+'/close-icon.gif','alt':'closeIcon','class':'closeIcon'})
						.click(function(event){
							$('div.opaque').remove();
							$('div.confeditsection').remove();
						})
					.end()
					.append(formFieldset)
					.find('fieldset')
						.attr('id','confajaxform')
					.end()
					.find('legend')
						.text('Edit Conference Details')
					.end()
					.find('td:odd:first')
						.contents()
						.remove()
						.end()
						.append('<input type="text"/>')
						.find('input')
							.attr({'tabindex':'1','size':'25'})
							.val(title)
						.end()	
					.end()
					.find('td:odd:eq(1)')
						.contents()
						.remove()
						.end()
						.append('<input type="text"/>')
						.find('input')
							.attr({'tabindex':'2','size':'7'})
							.val(startdate)
							.datepicker()
						.end()	
					.end()
					.find('td:odd:eq(2)')
						.contents()
						.remove()
						.end()
						.append('<input type="text"/>')
						.find('input')
							.attr({'tabindex':'3','size':'7'})
							.val(enddate)
							.datepicker()
						.end()	
					.end()
					.find('td:even:eq(3)')
						.contents()
						.remove()
						.end()
						.append('<label></label>')
						.find('label')
							.text('Country')
							.attr('for','countries')
						.end()	
					.end()
					.find('td:odd:eq(3)')
						.contents()
						.remove()
						.end()
						.append('<select></select><label></label><input type="text"/><label></label><input type="text"/>')
						.find('select')
							.attr({'id':'countries','tabindex':'4'})
							.css('width','8em')
							.val(country)
						.end()
						.find('label:first')
							.attr('for','city')
							.text('City')
						.end()
						.find('input:text:first')
							.attr({'id':'city','tabindex':'5','size':'10'})
							.val(city)
						.end()
						.find('label:last')
							.attr('for','place')
							.text('Place')
						.end()
						.find('input:last')
							.attr({'id':'place','tabindex':'6','size':'15'})
							.val(place)
						.end()
					.end()
					.find('td:odd:eq(4)')
						.contents()
						.remove()
						.end()
						.append('<input type="text"/>')
						.find('input')
							.attr({'tabindex':'7','size':'3'})
							.val(capacity)
						.end()	
					.end()
					.find('td:odd:eq(5)')
						.contents()
						.remove()
						.end()
						.append('<textarea></textarea>')
						.find('textarea')
							.attr({'tabindex':'8','cols':'10','rows':'5'})
							.val(description)
						.end()	
					.end()
					.find('td:odd:last')
						.contents()
						.remove()
						.end()
						.append('<button />')
						.find('button')
							.text('Edit')
							.click(function(event){
								var tbody = $(this).parent('td').parent('tr').parent('tbody');
								var inputElems = tbody.find('input');
								var startdateto=inputElems.filter(':eq(1)').val();
								var enddateto=inputElems.filter(':eq(2)').val();
								var capacityto=inputElems.filter(':eq(5)').val();
								var descriptionto=tbody.find('textarea').val();
								var countryto=tbody.find('submit').val();
								countryto = 'India'; /* remove this once you have added countries in the edit form */
								var cityto=inputElems.filter(':eq(3)').val();
								var placeto=inputElems.filter(':eq(4)').val();
								var titleto=inputElems.filter(':first').val();	
								if(titleto==title)
								{
									titleto='';
								}	
								var ajaxData = {
										format:			'json',
										action:			'confedit',
										title:			title,
										titleto:		titleto,
										capacity:		capacityto,
										venue:			placeto+','+cityto+','+countryto,
										description:	descriptionto,
										startdate:		startdateto,
										enddate:		enddateto
								};
								ajaxPoster(ajaxData, function(jsondata,event){
									var result = jsondata.confedit;
									var msg = '';
									var pClass = '';
									if(result && result['done'])
									{
										
										tdElements.filter(':first').text(result.title);
										tdElements.filter(':eq(5)').text(result.description);
										tdElements.filter(':eq(4)').text(result.capacity);
										tdElements.filter(':eq(1)').text(handlers.utils.dateToText(result.startdate));
										tdElements.filter(':eq(2)').text(handlers.utils.dateToText(result.enddate));
										tdElements.filter(':eq(3)').text(result.venue);
										msg = result.msg;
										pClass='noerrAjax'; 
										$('div.confeditsection')
										.css('height','50px')
										.find('fieldset')
										.remove()
										.end();
									} else if(!result['done']) {
										msg = result.msg;
										pClass = 'errAjax';
									} else {
										msg = jsondata.error.info;
										pclass = 'errAjax';
									}
									$('div.confeditsection')
									.append('<p></p>')
									.find('p')
										.text(msg)
										.addClass(pClass)
									.end();
									
									
								},event);
								
							})
						.end()
					.end()
					.appendTo('#mw-content-text');
				},
				
				del:function(event){
					
					
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div><table><tr><td></td></tr><tr><td><button /><button /></td></tr></table></div>').addClass('delsection')
					.find('table')
						.attr('id','delform')
					.end()	
					.find('td:first')
						.text('Are you sure ?')
						.addClass('textCenter')
					.end()
					.find(':button:first')
						.text('Delete')
						.click(function(event){
							/* to be done */
						})
					.end()
					.find(':button:last')
						.text('Cancel')
						.click(function(event){
							$('div.opaque').remove();
							$('div.delsection').remove();
						})
					.end()
					.appendTo('#mw-content-text');
				}
		};
		
		
		this.organizer = {
				
				
				edit:function(event){
					
					event.preventDefault();
					var $siblings = $(event.target).parent().siblings('td');
					var username=$siblings.filter(':first').find('a').text();
					var categoryOld = $siblings.filter(':eq(1)').text();
					var postOld = $siblings.filter(':eq(2)').text();
					$('<div></div>')
					.addClass('opaque')
					.appendTo('body');
					$('<div><img /><fieldset><legend></legend><table><tbody></tbody></table></fieldset></div>')
					.addClass('orgeditsection')
					.find('fieldset')
						.attr('id','orgajaxform')
					.end()	
					.find('img')
						.attr({'src':imagePath+'/close-icon.gif','alt':'close icon','class':'closeIcon'})
						.click(function(event){
							$('div.orgeditsection').remove();
							$('div.opaque').remove();
						})
					.end()
					.find('legend')
						.text('Edit Organizer Details')
					.end()
					.find('table')
						.append('<tr><td></td><td></td></tr>')
						.find('td:first')
							.append('<label></label>')
							.find('label')
								.text('Username :')
							.end()
						.end()
						.find('td:last')
							.append('<span></span>')
							.find('span')
								.text(username)
							.end()
						.end()
						.append('<tr><td></td><td></td></tr>')
						.find('tr:last')
							.find('td:first')
								.append('<label></label>')
								.find('label')
									.text('Category :')
								.end()
							.end()
							.find('td:last')
								.append('<input type="text" />')
								.find('input')
									.attr('size','20')
									.val(categoryOld)
								.end()
							.end()
						.end()
						.append('<tr><td></td><td></td></tr>')
						.find('tr:last')
							.find('td:first')
								.append('<label></label>')
								.find('label')
									.text('Post :')
								.end()
							.end()
							.find('td:last')
								.append('<input type="text" />')
								.find('input')
									.attr('size','20')
									.val(postOld)
								.end()
							.end()
						.end()
						.append('<tr><td></td><td></td></tr>')
						.find('tr:last')
							.find('td:last')
								.append('<button />')
								.find(':button')
									.text('Edit')
									.click(function(event){
										var $superParent = $(this).parent().parent('tr').parent('tbody');
										var category = $superParent.find('input:text:first').val();
										var post = $superParent.find('input:text:last').val();
										if(category==='' ||  post==='')
										{
											$(this).parents('div.orgeditsection').append('<p></p>')
											.find('p')
											.text('* None of the fields should be left empty')
											.end()
											.css('height','200px');
											return ;
										}
										var ajaxData = {
												format:		'json',
												action:		'orgedit',
												categoryto:	category,
												postto:		post,
												username:	username,
												category:	categoryOld,
												post:		postOld	
												
										};
										ajaxPoster(ajaxData,function(jsondata,event){
											
											var result = jsondata.orgedit;
											var msg = '';
											var pClass = 'errAjax';
											var $orgeditSection = $('div.orgeditsection');
											if(result)
											{
												if(result['done'])
												{
													
													$('#orgajaxform').remove();
													$siblings.filter(':eq(1)').text(result.category);
													$siblings.filter(':eq(2)').text(result.post);
													pClass ='noerrAjax';
												}
												msg = result.msg;
											} else {
												msg = jsondata.error.info;
											}	
											$orgeditSection
											.css('height','75px')
											.append('<p></p>')
											.find('p')
												.text(msg)
												.addClass(pClass)
											.end();
											
											
										},event);
										
									})
								.end()
							.end()
						.end()
						
					.end()
				.appendTo('#mw-content-text');
					
				},
				
				
				del:function(event){
					
					
					event.preventDefault();
					var toDelete = $(event.target).parent().parent('tr');
					var $siblings = $(event.target).parent().siblings('td');
					var username = $siblings.filter(':first').find('a').text();
					var category = $siblings.filter(':eq(1)').text();
					var post = $siblings.filter(':eq(2)').text();
					$('<div></div>').addClass('opaque')
					.appendTo('body');
					$('<div><table><tr><td></td></tr><tr><td><button /><button /></td></tr></table></div>').addClass('delsection')
					.find('table')
						.attr('id','orgdelform')
					.end()	
					.find('td:first')
						.text('Are you sure ?')
						.addClass('textCenter')
					.end()
					.find(':button:first')
						.attr('id','orgdelbtn')
						.text('Delete')
						.click(function(event){
							
							var ajaxData = {
									format:		'json',
									action:		'orgdelete',
									username:	username,
									category:	category,
									post:		post	
							};
							var table = $(this).parents('div.delsection table');
							var delsection = $(this).parents('div.delsection');
							ajaxPoster(ajaxData, function(jsondata,event){
								
								var result = jsondata.orgdelete;
								var msg = '';
								var pClass = 'errAjax';
								if(result)
								{
									if(result['done'])
									{
										$(toDelete).remove();
										table.remove();
										pClass = 'noerrAjax';
									}
									msg = result.msg;
								} else {
									msg = jsondata.error.info;
								}
								$(delsection)
								.css('width','200px')
								.append('<img />')
								.find('img')
									.attr({'src':imagePath+'/close-icon.gif','alt':'close icon','class':'closeIcon'})
									.click(function(event){
										$('div.opaque').remove();
										$('div.delsection').remove();
									})
								.end()
								.append('<p></p>')
								.find('p')
									.text(msg)
									.addClass(pClass)
								.end();	
							},event);
						})
					.end()
					.find(':button:last')
						.text('Cancel')
						.click(function(event){
							$('div.opaque').remove();
							$('div.delsection').remove();
						})
					.end()
					.appendTo('#mw-content-text');
				},
				
				submit:function(event){
					
					
					event.preventDefault();
					var values= [];
					$(event.target).parent().parent('tr').siblings('tr').each(function(){
						var value = $(this).find('input:text').val();
						values.push(value);
					});
					
					var ajaxData = {
							format:		'json',
							action:		'orgcreate',
							username:	values[0],
							category:	values[1],
							post:		values[2]
					};
					ajaxPoster(ajaxData, function(jsondata,event){
						var result = jsondata.orgcreate;
						var formFieldset = $('#cvext-dashsection-orgs fieldset:first');
						var msg ='';
						var pClass = 'errAjax';
						if(result)
						{	
							if(result['done'])
							{
								$('#cvext-dashsection-orgs fieldset:last table tbody')
								.append('<tr><td></td><td></td><td></td><td></td></tr>')
								.find('tr:last')
									.addClass('cvext-res')
									.find('td:first')
										.append('<a></a>')
										.find('a')
											.attr('href',result.userpage)
											.text(result.username)
											.addClass(result.userpageclass)
										.end()	
									.end()
									.find('td:eq(1)')
										.text(result.category)
									.end()
									.find('td:eq(2)')
										.text(result.post)
									.end()
									.find('td:last')
										.append('<a></a><a></a>')
										.find('a:first')
											.attr({'href':'#edit','class':'org'})
											.text('Edit')
											.click(function(event){
											handlers.organizer.edit(event);	
											})
										.end()
										.find('a:last')
											.attr({'href':'#delete','class':'org'})
											.text('Delete')
											.before(' | ')
											.click(function(event){
												handlers.organizer.del(event);
											})
										.end()
									.end()	
								.end();
								
								// empty the input fields
								$(formFieldset)
								.find('input:text:first')
									.val('')
								.end()
								.find('input:text:not(:first):not(:last)')
									.val('')
								.end()
								.find('input:text:last')
									.val('')
								.end();
								pClass = 'noerrAjax';
							}	
							msg = result.msg;

						} else {
							
							msg = jsondata.error.info;
							
						}
						//set the message in <p> element
						$(formFieldset)
						.find('p')
							.remove()
						.end()
						.append('<p></p>')
						.find('p')
							.text(msg)
							.addClass(pClass)
						.end();	
						
					},event);
				}
		};
		
		
		this.utils = {
				
				textToDate:function(dateString){ /* converts from Jul 4, 2012 to 07/04/2012 */
					//first of all extract the month, date and year from the string
					dateString = dateString.replace(/,*\s+/g,'');
					//now we have a compact string
					var month = dateString.substr(0,3);
					month = handlers.months.indexOf(month)+1;
					if(month<10)
					{
						month = '0'+month;
					}
					var date = dateString.substr(3,2);
					var year = dateString.substr(5,4);
					return month+'/'+date+'/'+year;
				},
				
				dateToText:function(datetext){ /* converts from 07042012 to July 4, 2012 */
					//extract month, date and year and format it as a different string
					//alert(Object.prototype.toString.call(compactDateString));
					var mm = datetext.substr(0,2);
					//alert(Object.prototype.toString.call(mm));
					var dd = datetext.substr(2,2);
					var yy = datetext.substr(4,4);
					mm = mm.replace(/0/,'');
					var index = parseInt(mm,10)-1;
					return handlers.months[index]+' '+dd+', '+yy;
				}
		};
		
	}
	
	var handlers = new Handlers();

	/* registering handlers for <a> links */
	$('a').filter('.page').click(function(event){	  
		
		var action = $(this).attr('href');
		if(action=='#add') 
		{
			
			handlers.page.add(event);
			
		} else if(action=='#addnew') {
			
			handlers.page.addNew(event);
			
		} else if(action=='#edit') {
			
			handlers.page.edit(event);
			
		} else if(action=='#delete') {
			
			handlers.page.del(event);
			
		}		
				
	})
	.end()
	.filter('.org').click(function(event){ 
		var action = $(this).attr('href');
		if(action=='#edit')
		{
			
			handlers.organizer.edit(event);
			
		} else if(action=='#delete'){
			
			handlers.organizer.del(event);
			
		}	
		
		})
	.end()
	.filter('.event').click(function(event){
		var action = $(this).attr('href');
		if(action=='#edit')
		{
			
			handlers.eventz.edit(event);
			
		} else if(action=='#delete'){
			
			handlers.eventz.del(event);
			
		}	
	}).end()
	.filter('.location').click(function(event){
		var action = $(this).attr('href');
		if(action=='#edit')
		{
			
			handlers.location.edit(event);
			
		} else if(action=='#delete'){
			
			handlers.location.del(event);
			
		}	
		
	})
	.end()
	.filter('.conf').click(function(event){
		var action = $(this).attr('href');
		if(action=='#edit')
		{
			
			handlers.conference.edit(event);
			
		} else if(action=='#delete'){
			
			handlers.conference.del(event);
			
		}	
		
	})
	.end();
	
//	/* registering click listeners for submit buttons */
	$('#cvext-dashsection-lcts > fieldset:first  input:submit').click(function(event){
		handlers.location.submit(event);
	});
	
	$('#cvext-dashsection-evts input:submit').click(function(event){
		handlers.eventz.submit(event);
	});
		
	$('#cvext-dashsection-orgs input:submit').click(function(event){
		handlers.organizer.submit(event);
	});
		
	var ajaxPoster = function(ajaxData,successHandler,event){
		$.ajax({
			type:		"POST",
			url:  		mw.util.wikiScript('api'),
			data:		ajaxData,
			dataType:	'json',
			success:	function(jsondata,textStatus,jqXHR){	
				successHandler(jsondata,event);
			},
			error: 		function(jqXHR, textStatus, errorThrown){	
				$('<p></p>')
				.text(textStatus)
				.attr('id','ajaxmsg')
				.appendTo('#dashboard');
			}
		});
	};
		
})(jQuery, mediaWiki);