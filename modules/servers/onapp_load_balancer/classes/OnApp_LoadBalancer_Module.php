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
		return $this->getOnAppObject( 'OnApp_Template' )->getList();
	}

	public function getJSLang() {
		global $_LANG;
		return json_encode( $_LANG );
	}

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
}
