/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
define([], function () {
    var sellerStorePickupBackendMap = function () {
        this.getSellerStorePickupPoint = function (latti,lonti,intZoom) {
			this.lat = latti;
			this.lon = lonti;
			this.zoom = intZoom;
			this.stockkhom = new google.maps.LatLng(this.lat,this.lon);
			this.mapOption = {
				zoom : this.zoom,
				center : this.stockkhom,
				scrollwheel: true,
				scaleControl: true,
				zoomControlOptions: {
					position: google.maps.ControlPosition.TOP_LEFT
				},
			}
			var map = new google.maps.Map(document.getElementById("googleMap"),this.mapOption);
            
			/* auto complete */
			var input = /** @type {HTMLInputElement} */(document.getElementById('sellerstorepickup_shop_location'));
            var autocomplete = new google.maps.places.Autocomplete(input);
			autocomplete.bindTo('bounds', map);
			var infowindow = new google.maps.InfoWindow();
			var marker = new google.maps.Marker({
    			map: map,
    			anchorPoint: new google.maps.Point(0, -29),
    			draggable : true,
    			position : map.getCenter()
			});
            
			google.maps.event.addListener(autocomplete, 'place_changed', function() {
				infowindow.close();
				marker.setVisible(false);
				var place = autocomplete.getPlace();
				if (!place.geometry) {
                    window.alert("Autocomplete's returned place contains no geometry");
                    return;
				}
				// If the place has a geometry, then present it on a map.
				if (place.geometry.viewport) {
					map.fitBounds(place.geometry.viewport);
				} else {
                    map.setCenter(place.geometry.location);
                    map.setZoom(17);  // Why 17? Because it looks good.
				}
				document.getElementById("sellerstorepickup_latitude").value = place.geometry.location.lat();
				document.getElementById("sellerstorepickup_longitude").value = place.geometry.location.lng();
                $('sellerstorepickup_latitude').setStyle({background: '#FAE6B4'});
                $('sellerstorepickup_longitude').setStyle({background: '#FAE6B4'});
                $('sellerstorepickup_shop_location').setStyle({background: '#FAE6B4'});
				marker.setIcon(/** @type {google.maps.Icon} */({
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(35, 35)
				}));
				marker.setPosition(place.geometry.location);
				marker.setVisible(true);

				var address = '';
				if (place.address_components) {
                    address = [
                        (place.address_components[0] && place.address_components[0].short_name || ''),
                        (place.address_components[1] && place.address_components[1].short_name || ''),
                        (place.address_components[2] && place.address_components[2].short_name || '')
                    ].join(' ');
				}

				infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
				infowindow.open(map, marker);
			});
            
			google.maps.event.addListener(marker,'dragend',function(event){
				document.getElementById("sellerstorepickup_latitude").value = event.latLng.lat();
				document.getElementById("sellerstorepickup_longitude").value = event.latLng.lng();
                $('sellerstorepickup_latitude').setStyle({background: '#FAE6B4'});
                $('sellerstorepickup_longitude').setStyle({background: '#FAE6B4'});
				infowindow.close();
			});
                                    
            google.maps.event.addListener(map, 'zoom_changed', function() {            
                var zoomLevel = map.getZoom();            
                $('sellerstorepickup_zoom_level').value = zoomLevel;
                $('sellerstorepickup_zoom_level').setStyle({background: '#FAE6B4'});
            }.bind(this));            
		}
	}
    return sellerStorePickupBackendMap;
});