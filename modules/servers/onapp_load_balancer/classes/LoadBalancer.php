<?php


class OnApp_LoadBalancer_Module {
	private $server;

	public function __construct( array $params ) {
		if( ! empty( $params ) ) {
			$this->server       = new stdClass;
			$this->server->ip   = empty( $params[ 'serverip' ] ) ? $params[ 'serverhostname' ] : $params[ 'serverip' ];
			$this->server->user = $params[ 'serverusername' ];
			$this->server->pass = $params[ 'serverpassword' ];
		}
	}

	public function getHypervisors() {
		return $this->getOnAppObject( 'OnApp_Hypervisor' )->getList();
	}

	public function getHypervisorZones() {
		return $this->getOnAppObject( 'OnApp_HypervisorZone' )->getList();
	}

	public function getNetworkZones() {
		return $this->getOnAppObject( 'OnApp_NetworkZone' )->getList();
	}

	public function getTemplates() {
		return array();
		return $this->getOnAppObject( 'OnApp_Template' )->getList();
	}

	public function getJSLang() {
		global $_LANG;
		return json_encode( $_LANG );
	}






	///////////////////////////////////////////////////////////////
	/**
	 * @param string $className
	 *
	 * @return OnApp
	 */
	private function getOnAppObject( $className ) {
		$obj = new $className;
		$obj->auth( $this->server->ip, $this->server->user, $this->server->pass );

		return $obj;
	}

	private function buildArray( $data ) {
		$tmp = array();
		foreach( $data as $item ) {
			$tmp[ $item->id ] = $item->label;
		}
		return $tmp;
	}








	/*
	public function getUserGroups() {
		$data = $this->getOnAppObject( 'OnApp_UserGroup' )->getList();
		return $this->buildArray( $data );
	}

	public function getRoles() {
		$data = $this->getOnAppObject( 'OnApp_Role' )->getList();
		return $this->buildArray( $data );
	}

	public function getBillingPlans() {
		$data = $this->getOnAppObject( 'OnApp_BillingPlan' )->getList();
		return $this->buildArray( $data );
	}

	public function getLocales() {
		$tmp = array();
		foreach( $this->getOnAppObject( 'OnApp_Locale' )->getList() as $locale ) {
			if( empty( $locale->name ) ) {
				continue;
			}
			$tmp[ $locale->code ] = $locale->name;
		}

		return $tmp;
	}

	public function getOnAppObject( $class ) {
		$obj = new $class;
		$obj->auth( $this->server->ip, $this->server->user, $this->server->pass );

		return $obj;
	}

	private function buildArray( $data ) {
		$tmp = array();
		foreach( $data as $item ) {
			$tmp[ $item->_id ] = $item->_label;
		}
		return $tmp;
	}
	*/
}

//check code below

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

		define( 'CREATE_TABLE_ONAPPLBCLIENTS', 'CREATE TABLE IF NOT EXISTS `tblonapplbclients` (
            `service_id` int(11) NOT NULL,
            `client_id` int(11) NOT NULL,
            `onapp_user_id` int(11) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL
            ) DEFAULT CHARSET=utf8;' );

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
		$productID = self::get_value( 'id' );

		$sql = 'SELECT
					tblservers.*,
					tblservergroupsrel.groupid,
					tblproducts.servergroup
				FROM
					tblservers
					LEFT JOIN tblservergroupsrel ON
						tblservers.id = tblservergroupsrel.serverid
					LEFT JOIN tblproducts ON
						tblproducts.id = :productID
				WHERE
					tblservers.disabled = 0
					AND tblservers.type = "onapp"';
		$sql = str_replace( ':productID', $productID, $sql );

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
