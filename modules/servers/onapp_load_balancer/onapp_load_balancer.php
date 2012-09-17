<?php

require_once dirname( __FILE__ ) . '/classes/LoadBalancer.php';

OnAppLoadBalancer::loadloadbalancer_language();
OnAppLoadBalancer::init_wrapper();
if( ! defined( 'ONAPPLOADBALANCER_FILE_NAME' ) ) {
	define( 'ONAPPLOADBALANCER_FILE_NAME', 'onapploadbalancer.php' );
}

function onapp_load_balancer_ConfigOptions() {
	global $packageconfigoption, $_GET, $_POST, $_LANG;

	// Create Tables
	$table_result = OnAppLoadBalancer::createTables();

	if( $table_result[ 'error' ] ) {
		return array(
			sprintf(
				'<font color="red"><b>%s</b></font>',
				$table_result[ 'error' ]
			) => array()
		);
	}

	// Localization
	$js_localization_array = array(
		'servers',
		'billingplans',
		'timezones',
		'usergroups',
		'userroles',
		'usersproperties',
		'cdnresourceproperties',
		'servernotdefined',
		'defaultport',
		'clusterconfiguration',
		'instance',
		'hvzone',
		'hv',
		'networkzone',
		'portspeed',
		'type',
		'autoscaling',
		'cluster',
		'clusternodes',
		'clusternodetemplates',
		'imagetemplate',
		'minnodamount',
		'maxnodamount',
		'clusternodeparameters',
		'memory',
		'cpus',
		'cpuguarantee',
		'ratelimit',
		'autoscaleoutparams',
		'autoscaleinparams',
		'ifcpuusage',
		'isabove',
		'minutes',
		'for',
		'add',
		'morevms',
		'islessthen',
		'morethen',
		'remove',
		'iffreeram',
		'morethen',
	);

	$js_localization_string = '';

	foreach( $js_localization_array as $string ) {
		if( isset( $_LANG[ 'onapplb' . $string ] ) ) {
			$js_localization_string .= '    LANG[ \'onapplb' . $string . '\' ] = \'' . $_LANG[ 'onapplb' . $string ] . '\';' . PHP_EOL;
		}
	}

	// Getting Servers
	$loadbalancerservers = OnAppLoadBalancer::getservers();

	// Error check
	if( count( $loadbalancerservers ) == 0 ) {
		$configarray[ '' ][ 'Description' ] .= '  <b>' . $_LANG[ 'onapplberrcantfoundactiveserver' ] . '</b>';
		return $configarray;
	}

	// Sellect servers of a group
	foreach( $loadbalancerservers as $key => $value ) {
		if( $value[ 'groupid' ] != $value[ 'servergroup' ] ) {
			unset( $loadbalancerservers[ $key ] );
		}
	}

	// Error check
	if( count( $loadbalancerservers ) == 0 ) {
		$configarray[ '' ][ 'Description' ] .= '  <b>' . $_LANG[ 'onapplberrcantfoundactiveserverforgroup' ] . '</b>';
		return $configarray;
	}

	// Javascript
	$css             = '<link rel="stylesheet" type="text/css" href="../modules/servers/onapploadbalancer/includes/css/admin_style.css" />';
	$base_javascript = '<script type="text/javascript" src="../modules/servers/onapploadbalancer/includes/js/base.js"></script>
        <script type="text/javascript">
            var servers = ' . json_encode( $loadbalancerservers ) . '
            var LANG = [];' . $js_localization_string . '</script>';

	$javascript = $base_javascript;
	$server_id = $packageconfigoption[ 1 ];

	if( $server_id ) {
		$javascript .= '<script type="text/javascript" src="../modules/servers/onapploadbalancer/includes/js/onapploadbalancer.js"></script>
                        <script type="text/javascript" src="../modules/servers/onapploadbalancer/includes/js/slider.js"></script> ';
	}

	// Passing options to the view
	$configarray = array(
		'&nbsp'      => array(
			'Type' => 'text'
		),
		'&nbsp'      => array(
			'Type' => 'text'
		),
		'javascript' => array(
			'Type'        => 'text',
			'Description' => PHP_EOL . $javascript,
		)
	);

	// Error check
	if( is_null( $server_id ) || $server_id == 0 || ! in_array( $server_id, array_keys( $loadbalancerservers ) ) ) {
		$configarray[ '' ][ 'Description' ] .= '  <b>' . $_LANG[ 'onapplbnoserverselected' ] . '</b>';
		return $configarray;
	}

	// check config json
	if( $packageconfigoption[ 2 ] != '' && ! json_decode( htmlspecialchars_decode( $packageconfigoption[ 2 ] ) ) ) {
		$configarray[ '' ][ 'Description' ] .= '  <b>' . $_LANG[ 'onapplberrorinvalidconfigjson' ] . '</b>';
		return $configarray;
	}

	// Check wrapper
	// todo fix path
	if( ! file_exists( ONAPP_WRAPPER_INIT ) ) {
		$configarray[ '' ][ 'Description' ] .= "<b>" . $_LANG[ 'onapplbwrappernotfound' ] . "</b> " . realpath( dirname( __FILE__ ) . '/../../../' ) . '/includes';
		return $configarray;
	}

	// Get OnApp Instance
	$server = $loadbalancerservers[ $server_id ];

	$ipaddress = $server[ 'ipaddress' ];
	$hostname  = $server[ 'hostname' ];
	$username  = $server[ 'username' ];
	$password  = $server[ 'password' ];

	if( $username && $password && ( $hostname || $ipaddress ) ) {
		$onapp_instance = new OnApp_Factory( ( $ipaddress ) ? $ipaddress : $hostname, $username, $password );
	}

	// Error check
	if( ! isset( $onapp_instance ) || ! $onapp_instance->_is_auth ) {
		$configarray[ '' ][ 'Description' ] .= ' <b> ' . $_LANG[ 'onapplbwrongserverconfig' ] . '</b>';
		$configarray[ 'javascript' ] = array(
			'Type'        => 'text',
			'Description' => PHP_EOL . $base_javascript
		);
		return $configarray;
	}

	// Load Hypervisor Zones
	if( $onapp_instance ) {
		$hv_zone = $onapp_instance->factory( 'HypervisorZone' );

		$hv_zones = $hv_zone->getList();

		$_hv_zones[ 0 ] = 'autoselect';
		foreach( $hv_zones as $zone ) {
			$_hv_zones[ $zone->_id ] = $zone->_label;
		}
	}

	// Load Image Templates
	if( $onapp_instance ) {
		$tpl = $onapp_instance->factory( 'Template' );

		$tpls = $tpl->getList();

		$_tpls[ 0 ] = 'autoselect';
		foreach( $tpls as $tpl ) {
			if( $tpl->_operating_system != 'freebsd' && $tpl->_operating_system != 'windows' ) {
				$_tpls[ $tpl->_id ] = htmlspecialchars( addslashes( preg_replace( '/\r\n|\n|\r/', " ", $tpl->_label ) ) );
			}
		}
	}

	// Load Hypervisors
	if( $onapp_instance ) {
		$hv = $onapp_instance->factory( 'Hypervisor' );

		$hvs = $hv->getList();

		$hv_ids        = array();
		$js_hvZonesRel = array();
		$js_hvOptions  = array( 0 => 'autoselect' );

		if( ! empty( $hvs ) ) {
			foreach( $hvs as $_hv ) {
				if( $_hv->online == true && $_hv->hypervisor_group_id ) {
					$hv_ids[ $_hv->id ] = array(
						'label' => $_hv->label
					);

					$js_hvOptions[ $_hv->_id ]  = $_hv->_label;
					$js_hvZonesRel[ $_hv->_id ] = $_hv->_hypervisor_group_id;
				}
			}
		}
	}

	// Load Network Zone
	if( $onapp_instance ) {
		$network_zone = $onapp_instance->factory( 'NetworkZone' );

		$network_zones = $network_zone->getList();

		$js_nzOptions = array( 0 => 'autoselect' );

		if( ! empty( $network_zones ) ) {
			foreach( $network_zones as $_nz ) {
				$js_nzOptions[ $_nz->_id ] = $_nz->_label;
			}
		}
	}

	// Passing additional javascript variables
	$javascript .= '
        <script type="text/javascript">
            hvZones     = ' . json_encode( $_hv_zones ) . '
            hvZonesRel  = ' . json_encode( $js_hvZonesRel ) . '
            hv          = ' . json_encode( $js_hvOptions ) . '
            nz          = ' . json_encode( $js_nzOptions ) . '
            tpl         = ' . json_encode( $_tpls ) . '
            css         = ' . $css . '
        </script>';

	$configarray[ 'javascript' ] = array(
		'Type'        => 'text',
		'Description' => PHP_EOL . $javascript
	);
	return $configarray;
}

function onapp_load_balancer_CreateAccount( $params ) {
}

function onapp_load_balancer_TerminateAccount( $params ) {
}

function onapp_load_balancer_SuspendAccount( $params ) {
}

function onapp_load_balancer_UnsuspendAccount( $params ) {
}

function onapp_load_balancer_ClientArea( $params ) {
}