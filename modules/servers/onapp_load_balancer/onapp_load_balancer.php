<?php

moduleInit();

function moduleInit() {
	if( ! defined( 'ONAPP_WRAPPER_INIT' ) ) {
		define( 'ONAPP_WRAPPER_INIT', dirname( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) . '/includes/wrapper/OnAppInit.php' );

		if( file_exists( ONAPP_WRAPPER_INIT ) ) {
			require_once ONAPP_WRAPPER_INIT;
		}
	}

	if( ! defined( 'ONAPPLOADBALANCER_FILE_NAME' ) ) {
		define( 'ONAPPLOADBALANCER_FILE_NAME', 'onapp_load_balancer.php' );
	}

	require_once dirname( __FILE__ ) . '/classes/OnApp_LoadBalancer_Module.php';
	getLang();
}

function onapp_load_balancer_ConfigOptions() {
	global $templates_compiledir, $_LANG;

	// check wrapper
	if( ! defined( 'ONAPP_WRAPPER_INIT' ) ) {
		return array(
			'' => array( 'Description' => '<b>' . $_LANG[ 'onapplbwrappernotfound' ] . '</b> ' . realpath( ROOTDIR ) . '/includes' )
		);
	}

	include_once ROOTDIR . '/includes/smarty/Smarty.class.php';
	$smarty = new Smarty();
	$compile_dir = file_exists( $templates_compiledir ) ? $templates_compiledir : ROOTDIR . '/' . $templates_compiledir;
	$smarty->compile_dir = $compile_dir;
	$smarty->template_dir = dirname( __FILE__ ) . '/includes/html/';
	$smarty->assign( 'LANG', $_LANG );

	$serverGroupID = isset( $_GET[ 'servergroup' ] ) ? $_GET[ 'servergroup' ] : (int)$GLOBALS[ 'servergroup' ];
	$sql         = 'SELECT
						srv.`id`,
						srv.`name`,
						srv.`ipaddress` AS serverip,
						srv.`hostname` AS serverhostname,
						srv.`username` AS serverusername,
						srv.`password` AS serverpassword,
						grp.`id` AS groupid
					FROM
						`tblservers` AS srv
					LEFT JOIN
						`tblservergroupsrel` AS rel ON srv.`id` = rel.`serverid`
					LEFT JOIN
						`tblservergroups` AS grp ON grp.`id` = rel.`groupid`
					WHERE
						grp.`id` = :serverGroupID';
	$sql         = str_replace( ':serverGroupID', $serverGroupID, $sql );

	$html        = '';
	$serversData = array();
	$res         = full_query( $sql );
	$module      = new OnApp_LoadBalancer_Module( array() );
	if( mysql_num_rows( $res ) == 0 ) {
		$smarty->assign( 'NoServers', sprintf( $_LANG[ 'onappuserserrorholder' ], $_LANG[ 'onappuserserrornoserveringroup' ] ) );
	}
	else {
		while( $serverConfig = mysql_fetch_assoc( $res ) ) {
			//Error if server adress (IP and hostname) not set
			if( empty( $serverConfig[ 'serverip' ] ) && empty( $serverConfig[ 'serverhostname' ] ) ) {
				$msg = sprintf( $_LANG[ 'onapperrcantfoundadress' ] );

				$data[ 'Name' ]                       = $serverConfig[ 'name' ];
				$data[ 'NoAddress' ]                  = sprintf( $_LANG[ 'onappuserserrorholder' ], $msg );
				$serversData[ $serverConfig[ 'id' ] ] = $data;
				continue;
			}
			$serverConfig[ 'serverpassword' ] = decrypt( $serverConfig[ 'serverpassword' ] );

			$module = new OnApp_LoadBalancer_Module( $serverConfig );
			$data   = array();

			// get hypervisor zones
			$data[ 'HypervisorZones' ] = array();
			foreach( $module->getHypervisorZones() as $zone ) {
				$data[ 'HypervisorZones' ][ $zone->id ] = $zone->label;
			}

			// get hypervisors
			$data[ 'Hypervisors' ] = array();
			foreach( $module->getHypervisors() as $hv ) {
				if( $hv->online && $hv->hypervisor_group_id ) {
					$data[ 'Hypervisors' ][ $hv->id ] = array(
						'label' => $hv->label,
						'hypervisorzone' => $hv->hypervisor_group_id
					);
				}
			}

			// get network zones
			$data[ 'NetworkZones' ] = array();
			foreach( $module->getNetworkZones() as $nz ) {
				$data[ 'NetworkZones' ][ $nz->id ] = $nz->label;
			}

			// common data
			$data[ 'Name' ] = $serverConfig[ 'name' ];
			$data[ 'GID' ] = $serverConfig[ 'groupid' ];
			$serversData[ 'Servers' ][ $serverConfig[ 'id' ] ] = $data;
			unset( $data );
		}

		$sql = 'SELECT
					prod.`configoption1` AS options,
					prod.`servergroup` AS `group`
				FROM
					`tblproducts` AS prod
				WHERE
					prod.`id` = :id';
		$sql                  = str_replace( ':id', (int)$_GET[ 'id' ], $sql );
		$results              = full_query( $sql );
		$results              = mysql_fetch_assoc( $results );
		$results[ 'options' ] = htmlspecialchars_decode( $results[ 'options' ] );

		//$serversData[ 'Group' ] = $results[ 'group' ];
		if( ! empty( $results[ 'options' ] ) ) {
			$results[ 'options' ] = json_decode( $results[ 'options' ], true );
			//$serversData += $results[ 'options' ];
			$serversData[ 'Group' ][ $results[ 'group' ] ] = $results[ 'options' ];
		}

		$smarty->assign( 'serversData', $serversData );
	}

	$html .= $smarty->fetch( $smarty->template_dir . 'admin.tpl' );
	$html .= PHP_EOL . PHP_EOL;

	if( isset( $_GET[ 'servergroup' ] ) ) {
		ob_end_clean();
		exit( $html );
	}
	else {
		$js = '<script type="text/javascript" src="../modules/servers/onapp_load_balancer/includes/js/jquery.json-2.2.min.js"></script>
			<script type="text/javascript" src="../modules/servers/onapp_load_balancer/includes/js/slider.js"></script>
			<script type="text/javascript" src="../modules/servers/onapp_load_balancer/includes/js/onapp_load_balancer.js"></script>
			<script type="text/javascript">
				var LANG = ' . $module->getJSLang() . ';
			</script>';

		$config = array(
			''  => array(
				'Description' => $html
			),
			'js'  => array(
				'Description' => $js
			),
		);
	}
	return $config;
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

function getLang() {
	global $_LANG;

	$dir = dirname( __FILE__ ) . '/lang/';

	if( ! file_exists( $dir ) ) {
		return;
	}

	$dh = opendir( $dir );

	while( false !== $file2 = readdir( $dh ) ) {
		if( ! is_dir( '' . 'lang/' . $file2 ) ) {
			$pieces = explode( '.', $file2 );
			if( $pieces[ 1 ] == 'txt' ) {
				$arrayoflanguagefiles[ ] = $pieces[ 0 ];
				continue;
			}
			continue;
		}
	}

	closedir( $dh );

	if( ! isset( $_SESSION[ 'Language' ] ) ) {
		$_SESSION[ 'Language' ] = 'English';
	}

	$language = $_SESSION[ 'Language' ];

	if( ! in_array( $language, $arrayoflanguagefiles ) ) {
		$language = 'English';
	}

	if( file_exists( dirname( __FILE__ ) . '/lang/' . $language . '.txt' ) ) {
		ob_start();
		include dirname( __FILE__ ) . '/lang/' . $language . '.txt';
		$templang = ob_get_contents();
		ob_end_clean();
		eval ( $templang );
	}
}