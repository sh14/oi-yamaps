(function () {
	'use strict';

	let shortcode = '[showyamap zoom="16" lang="en_US" center="55.74,37.63" title="Какой-то заголовок"][placemark address="Москва, Садовническая ул., 33с1" coordinates="55.745605,37.637596" iconimage="https://d30y9cdsu7xlg0.cloudfront.net/png/192295-200.png" iconsize="(200,200)" iconoffset="(-100,-200)"][/showyamap]' +
		'еще текст и все такое';

	//let matches = shortcode.match( /\[showyamap.*?\]/g );
	let map    = shortcode.match( /\[showyamap(.*?)]/ );
	console.log(  );
	map = map[1].trim();
	map = map.match(/(.*?)="(.*?)"/g).map(function ( text ) {
		return text.replace(/"/g,'');
	});
	console.log( map );
	let result = {};
	map.forEach( function ( x ) {
		let arr = x.split( '=' );
		//console.log( arr );
		arr[ 1 ] && ( result[ arr[ 0 ] ] = arr[ 1 ] );
	} );

	//console.log( get_shortcode_json_as_array(map[0]) );
	console.log( result );
	//get_shortcode_json_as_array(map[0]);
}());
