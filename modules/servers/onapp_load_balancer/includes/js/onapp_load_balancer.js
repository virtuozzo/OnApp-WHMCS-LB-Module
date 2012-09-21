$( document ).ready( function () {
	$( 'head' ).append( '<link rel="stylesheet" type="text/css" href="../modules/servers/onapp_load_balancer/includes/css/admin_style.css" />' );

	var table = '<div id="onapplb">' + $( 'table' ).eq( 5 ).find( 'table:first' ).parent().html() + '</div>';
	$( 'table' ).eq( 5 ).remove();
	$( 'table' ).eq( 4 ).next().after( table );

	// Refresh data if server group was changed
	$( "select[name$='servergroup']" ).bind( 'change', function () {
		$( 'div#onapplb td:first' ).html( LANG.onappusersjsloadingdata );
		$.ajax( {
			url:     document.location.href,
			data:    'servergroup=' + this.value,
			success: function ( data ) {
				buildFields( data );
			},
			error: function ( xhr ) {
				$( 'div#onapplb td:first' ).html( LANG.onappusersjsloadingdataerror + xhr.status );
			}
		} );
	} );

	$( 'li#tab2' ).bind( 'click', function () {
		alignSelects();
	} );
	alignSelects();

	$( '.sld' ).each( function ( i, val ) {
		var tmp = create_slider_html( 1000, 0, 1, this.name )
		$( val ).before( tmp );
	} );
	init_sliders();
} );

var OnAppLBData = {
	SelectedHVZ:      {},
	SelectedHV:      {},
	SelectedNZ:        {},
	SelectedPortSpeed: {},
	SelectedType: {}
};

SelectedHVZ();
SelectedHV();
SelectedNZ();
SelectedPortSpeed();
SelectedType();

function SelectedHVZ() {
	$( "select[name^='hvzones_packageconfigoption']" ).each( function ( i, val ) {
		var tmp = val.value.split( ':' );
		OnAppLBData.SelectedHVZ[ tmp[ 0 ] ] = tmp[ 1 ];
	} );

	$( "input[name^='packageconfigoption[1]']" ).val( objectToString( OnAppLBData ) );
}
$( "select[name^='hvzones_packageconfigoption']" ).live( 'change', function () {
	SelectedHVZ();
} );

function SelectedHV() {
	$( "select[name^='hvs_packageconfigoption']" ).each( function ( i, val ) {
		var tmp = val.value.split( ':' );
		OnAppLBData.SelectedHV[ tmp[ 0 ] ] = tmp[ 1 ];
	} );

	$( "input[name^='packageconfigoption[1]']" ).val( objectToString( OnAppLBData ) );
}
$( "select[name^='hvs_packageconfigoption']" ).live( 'change', function () {
	SelectedHV();
} );

function SelectedNZ() {
	$( "select[name^='nzs_packageconfigoption']" ).each( function ( i, val ) {
		var tmp = val.value.split( ':' );
		OnAppLBData.SelectedNZ[ tmp[ 0 ] ] = tmp[ 1 ];
	} );

	$( "input[name^='packageconfigoption[1]']" ).val( objectToString( OnAppLBData ) );
}
$( "select[name^='nzs_packageconfigoption']" ).live( 'change', function () {
	SelectedNZ();
} );

function SelectedPortSpeed() {
	$( "input[name^='ps_packageconfigoption']" ).each( function ( i, val ) {
		OnAppLBData.SelectedPortSpeed[ $( val ).attr( 'rel' ) ] = $( val ).val();
	} );

	$( "input[name^='packageconfigoption[1]']" ).val( objectToString( OnAppLBData ) );
}

function SelectedType() {
	OnAppLBData.SelectedType = {};
	var states = {}, serverID;
	$( "input[name^='lbtypes']" ).each( function ( i, val ) {
		serverID = $( val ).attr( 'rel' );
		if( ! $( val ).attr( 'checked' ) ) {
			var state = 0;
		}
		else {
			var state = 1;
		}
		states[ i ] = state;
	} );
	OnAppLBData.SelectedType[ serverID ] = states;

	$( "input[name^='packageconfigoption[1]']" ).val( objectToString( OnAppLBData ) );
}
$( "input[name^='lbtypes']" ).live( 'change', function () {
	SelectedType();
} );



function buildFields( data ) {
	$( 'div#onapplb' ).html( data );

	$( '.sld' ).each( function ( i, val ) {
		var tmp = create_slider_html( 1000, 0, 1, this.name )
		$( val ).before( tmp );
	} );
	init_sliders();
}

function alignSelects() {
	if( ! $( "select[name='servertype']:visible" ).length ) {
		return;
	}

	var max = 0;
	$( 'div#tab2box select' ).each( function ( i, val ) {
		width = $( val ).width();
		if( width > max ) {
			max = width + 30;
		}
	} );

	$( 'div#tab2box select' ).css( 'max-width', max );
}

function objectToString( o ) {
	return jQuery.toJSON( o );
}