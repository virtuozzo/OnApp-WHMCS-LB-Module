<?php

if( ! function_exists( 'emailtpl_template' ) ) {
	require_once dirname( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) ) . '/includes/functions.php';
}

class OnAppLoadBalancer {
	private $salt = 'ec457d0a974c48d5685a7efa03d137dc8bbde7e3';
	private $hostname;
	private $serviceid;
	private $created;
	private $server;
	private $user;
	private $onapp;

	public $error;

	function __construct( $serviceid = null ) {
		$this->hostname = $_SERVER[ 'HTTP_HOST' ];

		if( is_null( $serviceid ) ) {
			$serviceid = self::get_value( 'id' );
		}
		if( ! is_numeric( $serviceid ) ) {
			die( 'Invalid token' );
		}

		$this->serviceid = $serviceid;
	}

	/**
	 * Get GET or POST value
	 */
	public static function get_value( $value ) {
		return $_GET[ $value ] ? $_GET[ $value ] : ( $_POST[ $value ] ? $_POST[ $value ] : null );
	}

	/**
	 * Init OnApp PHP wrapper
	 */
	public static function init_wrapper() {
		if( ! defined( 'ONAPP_FILE_NAME' ) ) {
			define( "ONAPP_FILE_NAME", "onappcdn.php" );
		}

		if( ! defined( 'ONAPP_WRAPPER_INIT' ) ) {
			define( 'ONAPP_WRAPPER_INIT', dirname( __FILE__ ) . '/../../../includes/wrapper/OnAppInit.php' );
		}

		if( file_exists( ONAPP_WRAPPER_INIT ) ) {
			require_once ONAPP_WRAPPER_INIT;
		}
		else {
			return false;
		}

		return true;
	}

	/**
	 * Load $_LANG from language file
	 */
	public static function loadloadbalancer_language() {
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

	/**
	 * Create base OnApp Load Balancer module tables structure
	 */
	public static function createTables() {
		global $_LANG, $whmcsmysql;

		define( 'CREATE_TABLE_ONAPPLBSERVICES', 'CREATE TABLE IF NOT EXISTS `tblonapplbservices` (
            `service_id` int(11) NOT NULL,
            `cluster_id` int(11) NOT NULL,
            `balancer_id` int(11) NOT NULL,
            `ports` varchar(255) NOT NULL,
            `port_speed` int(11) NOT NULL,
            `cluster_type` varchar(150) NOT NULL,
            `node_ids` varchar(255) DEFAULT NULL,
            `template_id` int(11) DEFAULT NULL,
            `min_node_amount` int(11) DEFAULT NULL,
            `max_node_amount` int(11) DEFAULT NULL,
            `memory` int(11) DEFAULT NULL,
            `cpus` int(11) DEFAULT NULL,
            `cpu_shares` int(11) DEFAULT NULL,
            `rate_limit` int(11) DEFAULT NULL,
            `auto_scaling_out_cpu_attributes_value` int(11) DEFAULT NULL,
            `auto_scaling_out_cpu_attributes_units` int(11) DEFAULT NULL,
            `auto_scaling_out_cpu_attributes_for_minutes` int(11) DEFAULT NULL,
            `auto_scaling_out_memory_attributes_value` int(11) DEFAULT NULL,
            `auto_scaling_out_memory_attributes_units` int(11) DEFAULT NULL,
            `auto_scaling_out_memory_attributes_for_minutes` int(11) DEFAULT NULL,
            `auto_scaling_in_cpu_attributes_value` int(11) DEFAULT NULL,
            `auto_scaling_in_cpu_attributes_units` int(11) DEFAULT NULL,
            `auto_scaling_in_cpu_attributes_for_minutes` int(11) DEFAULT NULL,
            `auto_scaling_in_memory_attributes_value` int(11) DEFAULT NULL,
            `auto_scaling_in_memory_attributes_units` int(11) DEFAULT NULL,
            `auto_scaling_in_memory_attributes_for_minutes` int(11) DEFAULT NULL
            ) DEFAULT CHARSET=utf8;' );

		define( "CREATE_TABLE_ONAPPLBCLIENTS", "CREATE TABLE IF NOT EXISTS `tblonapplbclients` (
            `service_id` int(11) NOT NULL,
            `client_id` int(11) NOT NULL,
            `onapp_user_id` int(11) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL
            ) DEFAULT CHARSET=utf8;" );

		if( ! full_query( CREATE_TABLE_ONAPPLBCLIENTS, $whmcsmysql ) ) {
			return array(
				'error' => sprintf( $_LANG[ 'onappcdnerrtablecreate' ], 'onappcdnclients' )
			);
		}

		if( ! full_query( CREATE_TABLE_ONAPPLBSERVICES, $whmcsmysql ) ) {
			return array(
				'error' => sprintf( $_LANG[ 'onappcdnerrtablecreate' ], 'onappcdnclients' )
			);
		}

		return;
	}

	/**
	 * Get list of onapp cdn servers
	 */
	public static function getservers() {
		$product_id = self::get_value( 'id' );

		$sql = 'SELECT
    tblservers.*,
    tblservergroupsrel.groupid,
    tblproducts.servergroup
FROM
    tblservers
    LEFT JOIN tblservergroupsrel ON
        tblservers.id = tblservergroupsrel.serverid
    LEFT JOIN tblproducts ON
        tblproducts.id = $product_id
WHERE
    tblservers.disabled = 0
    AND tblservers.type = "onapploadbalancer"';

		$sql_servers_result = full_query( $sql );

		$servers = array();
		while( $server = mysql_fetch_assoc( $sql_servers_result ) ) {
			if( is_null( $server[ 'groupid' ] ) ) {
				$server[ 'groupid' ] = 0;
			}
			$server[ 'password' ] = decrypt( $server[ 'password' ] );

			$servers[ $server[ 'id' ] ] = $server;
		}

		return $servers;
	}

	public function show() {
		die( 'You need to define function show in class' . __CLASS__ );
	}
}
