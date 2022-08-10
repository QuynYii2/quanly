// REQUIRED: Include "jQuery Query Parser" plugin here or before this point: 
// 	     https://github.com/mattsnider/jquery-plugin-query-parser

jQuery(document)
		.ready(
				function() {

					// BOOTSTRAP 3.0 - Open YouTube Video Dynamicaly in Modal
					// Window
					// Modal Window for dynamically opening videos
					jQuery('a[href^="http://www.youtube.com"]')
							.on(
									'click',
									function(e) {
										// Store the query string variables and
										// values
										// Uses "jQuery Query Parser" plugin, to
										// allow for various URL formats (could
										// have extra parameters)
										var queryString = jQuery(this)
												.attr('href')
												.slice(
														jQuery(this).attr('href')
																.indexOf('?') + 1);
										var queryVars = jQuery
												.parseQuery(queryString);

										// if GET variable "v" exists. This is
										// the Youtube Video ID
										if ('v' in queryVars) {
											// Prevent opening of external page
											e.preventDefault();

											// Variables for iFrame code. Width
											// and height from data attributes,
											// else use default.
											//var vidWidth = 560; // default
											//var vidHeight = 315; // default
											//if (jQuery(this).attr('data-width')) {
											//	vidWidth = parseInt(jQuery(this)
											//			.attr('data-width'));
											//}
											//if (jQuery(this).attr('data-height')) {
											//	vidHeight = parseInt(jQuery(this)
											//			.attr('data-height'));
											//}
											
											var vidWidth 	= 270;
											var vidHeight 	= 230;
											
											var iFrameCode = '<iframe width="'
													+ vidWidth
													+ '" height="'
													+ vidHeight
													+ '" scrolling="no" allowtransparency="true" allowfullscreen="true" src="http://www.youtube.com/embed/'
													+ queryVars['v']
													+ '?rel=0&wmode=transparent&showinfo=0" frameborder="0"></iframe>';

											// Replace Modal HTML with iFrame
											// Embed
											jQuery('#youtubeModal .modal-title').html(jQuery(this).attr('title'));
											jQuery('#youtubeModal .modal-body').html(iFrameCode);
											// Set new width of modal window,
											// based on dynamic video content
											jQuery('#youtubeModal')
													.on(
															'show.bs.modal',
															function() {
																// Add video
																// width to left
																// and right
																// padding, to
																// get new width
																// of modal
																// window
																var modalBody = jQuery(
																		this)
																		.find(
																				'.modal-body');
																var modalDialog = jQuery(
																		this)
																		.find(
																				'.modal-dialog');
																var newModalWidth = vidWidth
																		+ parseInt(modalBody
																				.css("padding-left"))
																		+ parseInt(modalBody
																				.css("padding-right"));
																newModalWidth += parseInt(modalDialog
																		.css("padding-left"))
																		+ parseInt(modalDialog
																				.css("padding-right"));
																newModalWidth += 'px';
																// Set width of modal (Bootstrap 3.0)
																jQuery(this)
																		.find(
																				'.modal-dialog')
																		.css(
																				'width',
																				newModalWidth);
															});

											// Open Modal
											jQuery('#youtubeModal').modal();
										}
									});

					// Clear modal contents on close. 
					// There was mention of videos that kept playing in the background.
					jQuery('#youtubeModal').on('hidden.bs.modal', function() {
						jQuery('#youtubeModal .modal-body').html('');
					});

				});