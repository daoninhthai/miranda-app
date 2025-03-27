/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
var sellerStoreLocatorMap = function () {
    this.initialize = function(latitude, longtitude, zoom_val, id_map){
        this.defaultLatlng = new google.maps.LatLng(latitude, longtitude);
        this.markerArr = [];
        this.myOptions = {
            zoom: zoom_val,
            center: this.defaultLatlng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        this.map = new google.maps.Map(document.getElementById(id_map), this.myOptions);
        this.bounds = new google.maps.LatLngBounds();
    },
    this.placeStoreMarker = function(point, store_info, store_id, image,zoomLevel, infoWindow, x ){                
        var marker;
        if(image){
            marker = new google.maps.Marker({
                position: point,
                map: this.map,
                icon: image,
                store_id :store_id 
            });
        }
        else {
            marker = new google.maps.Marker({
                position: point,
                map: this.map,
                store_id :store_id 
            });
        }
        this.markerArr.push(marker);
        google.maps.event.addListener(marker, 'click', function(event) {
            infoWindow.setContent(store_info);
            infoWindow.setPosition(event.latLng);
			this.map.setCenter(event.latLng);            
            infoWindow.open(this.map, marker);
            if(zoomLevel!=0){
                this.map.setZoom(zoomLevel);
            }
        }.bind(this));
    },
    this.extendStoreBound = function(marker){
        this.bounds.extend(marker);
    },
    this.setFitStoreBounds = function (){
        this.map.fitBounds(this.bounds);
    },
	this.setFitStoreBoundsOne = function (){
        this.map.setCenter(this.bounds.getCenter());
    }
};

var InfostorePopup = function () {
    this.initialize = function(store_id,html, zoom, point){
        this.store_id = store_id;
        this.html = html;
        this.point = point;
        this.zoom = zoom;
    }
}