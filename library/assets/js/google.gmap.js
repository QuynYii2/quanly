var VnsGmap = new Class(
		{
			getOptions : function() {
				return {
					ipbaseurl 	: '',
					latitude 	: '',
					longitude 	: ''
				};
			},

			initialize : function(options) {
				this.setOptions(this.getOptions(), options);

				PartnerIcons = new google.maps.MarkerImage();
				PartnerIcons.icon = this.options.ipbaseurl + "frontend/templates/thiacanh/_assets/images/gmap/iconpoint.png";
				PartnerIcons.shadow = this.options.ipbaseurl + "frontend/templates/thiacanh/_assets/images/gmap/iconshadow.png";
				PartnerIcons.iconSize = new google.maps.Size(32, 32);
				PartnerIcons.shadowSize = new google.maps.Size(59, 32);
				PartnerIcons.iconAnchor = new google.maps.Point(9, 34);
				PartnerIcons.infoWindowAnchor = new google.maps.Point(9, 2);
				PartnerIcons.infoShadowAnchor = new google.maps.Point(18, 25);
				PartnerIcons.transparent = "http://www.google.com/intl/en_ALL/mapfiles/markerTransparent.png";
				PartnerIcons.printImage = "coldmarkerie.gif";
				PartnerIcons.mozPrintImage = "coldmarkerff.gif";

				// GMAP V3
				var houseIcon = new google.maps.MarkerImage();
				houseIcon.icon = this.options.ipbaseurl + "frontend/templates/thiacanh/_assets/images/gmap/iconhome.png";
				houseIcon.shadow = this.options.ipbaseurl + "frontend/templates/thiacanh/_assets/images/gmap/iconhomes.png";
				houseIcon.iconSize = new google.maps.Size(32, 32);
				houseIcon.shadowSize = new google.maps.Size(59, 32);
				houseIcon.iconAnchor = new google.maps.Point(9, 34);
				houseIcon.infoWindowAnchor = new google.maps.Point(9, 2);
				houseIcon.infoShadowAnchor = new google.maps.Point(18, 25);
				houseIcon.transparent = "http://www.google.com/intl/en_ALL/mapfiles/markerTransparent.png";
				houseIcon.printImage = "coldmarkerie.gif";
				houseIcon.mozPrintImage = "coldmarkerff.gif";

				// GMAP V3
				var gmarkers = [];
				var map = new google.maps.Map(document.getElementById('vns_gmap'), {
					center : new google.maps.LatLng(this.options.latitude, this.options.longitude),
					zoom : 13,
					mapTypeId : google.maps.MapTypeId.ROADMAP
				});

				var infowindow = new google.maps.InfoWindow({
					content : '',
					maxWidth : 350
				});

				function loadresult() {
					jQuery('.partner-intro li').each(function(){
						var input = new Object();
						input.id		= jQuery(this).attr('partner');
						input.latitude	= jQuery(this).attr('latitude');
						input.longitude	= jQuery(this).attr('longitude');
						var marker = CreateMarker(input);
					});
				}

				// format gmap bubble window
				function formatWindow(input) {
					var html = '<div class="map-overlay">';
					html+= '	<img src="'+jQuery('li[partner='+input.id+'] img').attr('src')+'" style=""/>';
					html+= '	<div class="introtext">'+jQuery('.show-map-'+input.id).html()+'</div>';
					html+= '</div>';
					return html;
				}

				// GMAP V3
				function CreateMarker(input) {
					if (input.latitude && input.longitude) {
						var coord = new google.maps.LatLng(input.latitude, input.longitude);
						var shape = {
							coord : [ 1, 1, 1, 25, 25, 25, 25, 1 ],
							type : 'poly'
						};
						var marker = new google.maps.Marker({
							map : map,
							position : coord,
							icon : PartnerIcons.icon,
							shadow : PartnerIcons.shadow,
							shape : shape
						});
						var html = formatWindow(input);

						bounds.extend(coord);
						google.maps.event.addListener(marker, 'click',
								function() {
									infowindow.setContent(html);
									infowindow.open(map, marker);
								});
						gmarkers[input.id] = marker;
						htmls[input.id] = html;
						marker.setMap(map);
						return marker;
					} else {
						return false;
					}
				}

				myclick = function(i) {
					infowindow.setContent(htmls[i]);
					infowindow.open(map, gmarkers[i]);
				}
				clearBackGround = function(i) {
				}

				function readMap() {
					// GMAP V3
					bounds 		= new google.maps.LatLngBounds();
					gmarkers 	= [];
					htmls 		= [];
					
					loadresult();
				}

				function deleteOverlays() {
					if (gmarkers.length != 0) {
						var keys = Object.keys(gmarkers);
						for (n = 0; n < keys.length; n++) {
							var key = keys[n];
							gmarkers[key].setMap(null);
						}
						gmarkers.length = 0;
					}
				}

				function stopRKey(evt) {
					var evt = (evt) ? evt : ((event) ? event : null);
					var node = (evt.target) ? evt.target
							: ((evt.srcElement) ? evt.srcElement : null);
					if ((evt.keyCode == 13) && (node.type == "text")) {
						return false;
					}
				}
				
				readMap();
				document.onkeypress = stopRKey;
			}
		});

VnsGmap.implement(new Events);
VnsGmap.implement(new Options);