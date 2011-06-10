/**
 * Show Google Map and processes other functions to submit report
 */


// cache regions and cities data retrieved via ajax 
// to eliminate the amount of calls to server
// TODO: if browser supports client side storage then use that storage 
//       to eliminate the amount of AJAX calls to the server
var citiesCache = Array();

/**
 * On load functionality initialization
 */
$(function(){

	$('#problem-description').focus();
	
	
	var defaultLocation = new google.maps.LatLng(49, 31);

	var myOptions = {
		      zoom: 6,
		      disableDefaultUI: true,
		      zoomControl: true,
		      mapTypeId: google.maps.MapTypeId.HYBRID,
	};

	var map = new google.maps.Map(document.getElementById("location_map"), myOptions);

	map.setCenter(defaultLocation);

	var marker = new google.maps.Marker({
		map:map,
		draggable:true,
		animation: google.maps.Animation.DROP,
		position: defaultLocation
	});
	
	marker.setAnimation(google.maps.Animation.BOUNCE);
	
	google.maps.event.addDomListener(map, "dragend", function (){
		marker.setPosition(map.center);
	});
	
	google.maps.event.addDomListener(marker, "dragend", function (){
		// marker.setAnimation(google.maps.Animation.BOUNCE);
		map.setCenter(marker.position);
		
		if (map.zoom < 18) {
			map.setZoom(map.zoom + 2);
		}
	});
	
	google.maps.event.addListener(map, 'zoom_changed', function() {
		marker.setPosition(map.center);
    });

	
	
	// bind change action for region select control
	$('#city-finder-regions').change(function(){
		
		var regionId = $(this).val();
		
		// if region was selected then show cities selection control for that region 
		if (regionId > 0) {
			// show city selectbox
			loadCities(regionId);
			
		} else {
			// hide city selectbox
			$('#city-finder-city').addClass('hidden-element');
		}
		
	});
	
	$('#city-finder-city').change(function(){
		var value = $(this).val();
		if (value == "") {
			return;
		}
		var coords = value.split(',');
		var cityCoords = new google.maps.LatLng(coords[1],coords[0]);
		map.setZoom(13);
		map.setCenter(cityCoords);
		marker.setPosition(cityCoords)
	});

	
	
	$('#submit-report-button').click(function(){
		// read & validate description
		var formDescription = $('#problem-description').attr('value');
		if (formDescription == "") {
			$('#problem-description').focus();
			alert('коротко опишіть проблему');
			return;
		}
	
		// read & validate category
		var formCategory = $('#category').val();
		if (!formCategory) {
			alert('вкажіть категорію проблеми');
			return;
		}
		
		// read & validate coords
		if (map.zoom < 16) {
			alert('перетягніть маркер щоб вказати точніші координати');
			return;
		}
		
		// save report to database
		var lat = marker.position.lat();
		var lng = marker.position.lng();
		
		$.post('/upload/post/', {"msg":formDescription, "type":formCategory, "long":lng, "lat":lat}, function(data){
			// redirect to newly created report
			if (data.success) {
				location.href = data.url;
			}
		}, "json");
		
		return false;
		
	});
	
});




//this will fill the cities selectbox with the available entries
//if entries are available locally we will use that data otherwise 
//the AJAX call will be made and data will be cached
function loadCities(regionId)
{
	elem = $('#city-finder-city');
		
	// get data from the cache if available
	if (citiesCache[regionId]) {
		elem.removeClass('hidden-element');
		elem.html(citiesCache[regionId]);
	} else {
		elem.addClass('hidden-element');
		elem.load('/searchdata/list/citiescoords/regionid/' + regionId, null, function(){
			$('#loading-progress').remove();
			elem.removeClass('hidden-element');
			citiesCache[regionId] = $(this).html();
		});
		$('#city-finder-regions').after('<img id="loading-progress" src="/images/ajax-loader.gif" />');
	}
}