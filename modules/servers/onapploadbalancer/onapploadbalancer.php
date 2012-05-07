<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );

require_once dirname(__FILE__).'/class_onapploadbalancer.php';

OnAppLoadBalancer::loadloadbalancer_language();
OnAppLoadBalancer::init_wrapper();
if ( ! defined('ONAPPLOADBALANCER_FILE_NAME') ) define('ONAPPLOADBALANCER_FILE_NAME', 'onapploadbalancer.php' );

function onapploadbalancer_ConfigOptions() {
    global $packageconfigoption, $_GET, $_POST, $_LANG;
    
/// Create Tables ////////////////
//////////////////////////////////

    $table_result = OnAppLoadBalancer::createTables();

    if ( $table_result["error"] )
        return array(
            sprintf(
                "<font color='red'><b>%s</b></font>",
                $table_result["error"]
            ) => array()
        );

/// END Create Tables ////////////
//////////////////////////////////

/// Localization ///
///////////////////
    
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

    foreach ($js_localization_array as $string)
        if (isset($_LANG['onapplb'.$string]))
            $js_localization_string .= "    LANG['onapplb$string'] = '".$_LANG['onapplb'.$string]."';\n";

/// END Localization ///
///////////////////////

// Getting Servers //
////////////////////
        
    $loadbalancerservers = OnAppLoadBalancer::getservers();
 
// Error check    
    if ( count($loadbalancerservers) == 0 ) {
        $configarray['']['Description'] .= '  <b>'.$_LANG["onapplberrcantfoundactiveserver"]. '</b>';
        return $configarray;
    }    

// Sellect servers of a group    
    foreach($loadbalancerservers as $key => $value)
        if( $value['groupid'] != $value['servergroup'] )
            unset($loadbalancerservers[$key]);
 
// Error check
    if ( count($loadbalancerservers) == 0 ) {
        $configarray['']['Description'] .= '  <b>'.$_LANG["onapplberrcantfoundactiveserverforgroup"] . '</b>';
        return $configarray;
    }
    
// END Getting Servers //
////////////////////////    

///// Javascript ///
///////////////////
    $css = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../modules/servers/onapploadbalancer/includes/css/admin_style.css\" />";
    $base_javascript = "
        <script type=\"text/javascript\" src=\"../modules/servers/onapploadbalancer/includes/js/base.js\"></script>
        <script type=\"text/javascript\">
        
            var servers         = " . json_encode( $loadbalancerservers ) . "
            var LANG = new Array()
                $js_localization_string

        </script>
    ";
    
    $javascript = $base_javascript;
    
    $server_id = $packageconfigoption[1];
    
    if ( $server_id ) {
        $javascript .= "<script type=\"text/javascript\" src=\"../modules/servers/onapploadbalancer/includes/js/onapploadbalancer.js\"></script>
                        <script type=\"text/javascript\" src=\"../modules/servers/onapploadbalancer/includes/js/slider.js\"></script> ";
    }
    
///// END Javascript ///
///////////////////////    

// Passing options to the view    
    $configarray = array(
        "&nbsp" => array(
            "Type" => "text" ),
        "&nbsp" => array(
            "Type" => "text" ),
        "javascript" => array(
            "Type"        => "text",
            "Description" => "\n$javascript",
        )
    ); 
    
// Error check    
    if ( is_null($server_id)                               // NULL
        || $server_id == 0                                 // not defined
        || ! in_array($server_id, array_keys($loadbalancerservers)) // not in group
    ) {
        $configarray['']['Description'] .= '  <b>'.$_LANG['onapplbnoserverselected'] . '</b>';
        return $configarray;
    } 
    
// check config json
    if ( $packageconfigoption[2] != '' &&
        ! json_decode( htmlspecialchars_decode ( $packageconfigoption[2] ) ) )
    {
        $configarray['']['Description'] .= '  <b>'.$_LANG['onapplberrorinvalidconfigjson'] . '</b>';
        return $configarray;
    }
    
// Check wrapper
    if ( ! file_exists( ONAPP_WRAPPER_INIT ) ) {
        $configarray['']['Description'] .= "<b>". $_LANG['onapplbwrappernotfound']. "</b> " . realpath( dirname(__FILE__).'/../../../' ) . "/includes";
        return $configarray;
    }  
    
// Get OnApp Instance ///////////
////////////////////////////////
    $server = $loadbalancerservers[$server_id];
    
    $ipaddress = $server['ipaddress'];
    $hostname  = $server['hostname'];
    $username  = $server['username'];
    $password  = $server['password'];

    if ( $username && $password && ( $hostname || $ipaddress )  ) {
        $onapp_instance = new OnApp_Factory(
            ( $ipaddress ) ? $ipaddress : $hostname,
            $username,
            $password
        );
    }   

// Error check    
    if( ! isset( $onapp_instance ) || ! $onapp_instance->_is_auth ) {
        $configarray['']['Description'] .= ' <b> '.$_LANG['onapplbwrongserverconfig'] . '</b>';
        $configarray['javascript'] = array("Type"=>"text", "Description"=> "\n$base_javascript");
        return $configarray;
    }
    
// BEGIN Load Hypervisor Zones //
////////////////////////////////    
    if ( $onapp_instance ) {
            $hv_zone = $onapp_instance->factory( 'HypervisorZone' );

        $hv_zones = $hv_zone->getList();

        $_hv_zones[0] = 'autoselect';
        foreach( $hv_zones as $zone ) {
            $_hv_zones[$zone->_id] = $zone->_label;
        }
    }
// END Load Hypervisor Zones //
//////////////////////////////
    
// BEGIN Load Image Templates  //
////////////////////////////////    
    if ( $onapp_instance ) {
            $tpl = $onapp_instance->factory( 'Template' );

        $tpls = $tpl->getList();
        

        $_tpls[0] = 'autoselect';
        foreach( $tpls as $tpl ) {
            if ( $tpl->_operating_system != 'freebsd' && $tpl->_operating_system != 'windows' ) {
                $_tpls[$tpl->_id] = htmlspecialchars( addslashes( preg_replace('/\r\n|\n|\r/', " ", $tpl->_label ) ) );
            }
        }
    }
// END Load Image Templates //
//////////////////////////////     
    
// BEGIN Load Hypervisors //
///////////////////////////    
    if ( $onapp_instance ) {
        $hv = $onapp_instance->factory( 'Hypervisor' );

        $hvs = $hv->getList();

        $hv_ids = array();
        $js_hvZonesRel = array();
        $js_hvOptions = array(0 => 'autoselect');

        if (!empty($hvs)) {
            foreach ($hvs as $_hv) {
                if ( $_hv->_online == "true" && $_hv->_hypervisor_group_id ) {
                    $hv_ids[$_hv->_id] = array(
                        'label' => $_hv->_label
                    );

                    $js_hvOptions[$_hv->_id] = $_hv->_label;
                    $js_hvZonesRel[$_hv->_id] = $_hv->_hypervisor_group_id;
                }
            }
        }
    }
 // END Load Hypervisors   //
////////////////////////////  
    
// BEGIN Load Network Zone //
////////////////////////////    
    if ( $onapp_instance ) {
        $network_zone = $onapp_instance->factory( 'NetworkZone' );

        $network_zones = $network_zone->getList();
        
        $js_nzOptions = array(0 => 'autoselect');

        if (!empty($network_zones)) {
            foreach ($network_zones as $_nz) {
                $js_nzOptions[$_nz->_id] = $_nz->_label;
            }
        }
    }
 // END Load NetworkZone //
////////////////////////// 

// Passing additional javascript variables    
    $javascript .= "
        <script type=\"text/javascript\">
            hvZones     = " . json_encode( $_hv_zones) . "
            hvZonesRel  = " . json_encode( $js_hvZonesRel ) ."
            hv          = " . json_encode( $js_hvOptions ) . "
            nz          = " . json_encode( $js_nzOptions) ."
            tpl         = " . json_encode( $_tpls ) . "
            css         = '$css'    
        </script>    
    ";
        
    $configarray['javascript'] = array("Type"=>"text", "Description"=> "\n$javascript");
    return $configarray;
}

function onapploadbalancer_CreateAccount($params) {

//    $onappcdn = new OnAppCDN($params["serviceid"]);
//
//    $user = $onappcdn->get_user();
//
//    if( $user )
//        $result = 'OnApp CDN user already exists (onapp user id #'.$user['onapp_user_id'].')';
//    else
//        $result = $onappcdn->create_user();
//
//    return $result;
}

function onapploadbalancer_TerminateAccount($params) {

//    $onappcdn = new OnAppCDN($params["serviceid"]);
//
//    $user = $onappcdn->get_user();
//
//    if( ! $user )
//        $result = "OnApp CDN user do not exists";
//    else
//        $result = $onappcdn->delete_user();
//
//    return $result;
}

function onapploadbalancer_SuspendAccount($params) {

//    $onappcdn = new OnAppCDN($params["serviceid"]);
//
//    $user = $onappcdn->get_user();
//
//    if( ! $user )
//        $result = "OnApp CDN user do not exists";
//    else
//        $result = $onappcdn->suspend_user();
//
//    return $result;
}

function onapploadbalancer_UnsuspendAccount($params) {
//
//    $onappcdn = new OnAppCDN($params["serviceid"]);
//
//    $user = $onappcdn->get_user();
//
//    if( ! $user )
//        $result = "OnApp CDN user do not exists";
//    else
//        $result = $onappcdn->unsuspend_user();
//
//    return $result;
}

function onapploadbalancer_ClientArea( $params ) {
//    global $_LANG;
//
//    if ( ! $init_wrapper = OnAppCDN::init_wrapper() )
//        return
//            sprintf(
//                "%s ",
//                $_LANG['onapponmaintenance']
//     );
//    
//    $onappcdn = new OnAppCDN( $params['serviceid']);
//    $user = $onappcdn->get_user();
//    
//    if ( ! is_null($user["onapp_user_id"]) )
//        return '<a href="' . ONAPPCDN_FILE_NAME . '?page=resources&id=' . $params['serviceid'] . '">' . $_LANG["onappcdnresources"] . '</a>';
//    else
//        return '<a href="' . ONAPPCDN_FILE_NAME . '?page=default&id=' . $params['serviceid'] . '&action=create">' . $_LANG["onappcdncreate"] . '</a>';
}
/*
function onappcdn_ChangePassword($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_ChangePackage($params) {

//    # Code to perform action goes here...

//    if ($successful) {
        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
    return $result;
}

function onappcdn_ClientArea($params) {

//    # Output can be returned like this, or defined via a clientarea.tpl onappcdn file (see docs for more info)

//    $code = '<form action="http://'.$serverip.'/controlpanel" method="post" target="_blank">
//<input type="hidden" name="user" value="'.$params["username"].'" />
//<input type="hidden" name="pass" value="'.$params["password"].'" />
//<input type="submit" value="Login to Control Panel" />
//<input type="button" value="Login to Webmail" onClick="window.open(\'http://'.$serverip.'/webmail\')" />
//</form>';
//    return $code;

}

function onappcdn_AdminLink($params) {

    $code = "";
//    $code = '<form action=\"http://'.$params["serverip"].'/controlpanel" method="post" target="_blank">
//<input type="hidden" name="user" value="'.$params["serverusername"].'" />
//<input type="hidden" name="pass" value="'.$params["serverpassword"].'" />
//<input type="submit" value="Login to Control Panel" />
//</form>';
    return $code;

}

//function onappcdn_LoginLink($params) {

//    echo "<a href=\"http://".$params["serverip"]."/controlpanel?gotousername=".$params["username"]."\" target=\"_blank\" style=\"color:#cc0000\">login to control panel</a>";

//}

//function onappcdn_reboot($params) {

//    # Code to perform reboot action goes here...

//    if ($successful) {
//        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
//    return $result;

//}

//function onappcdn_shutdown($params) {

//    # Code to perform shutdown action goes here...

//    if ($successful) {
//        $result = "success";
//    } else {
//        $result = "Error Message Goes Here...";
//    }
//    return $result;

//}

//function onappcdn_ClientAreaCustomButtonArray() {
//    $buttonarray = array(
//     "Reboot Server" => "reboot",
//    );
//    return $buttonarray;
//}

//function onappcdn_AdminCustomButtonArray() {
//    $buttonarray = array(
//     "Reboot Server" => "reboot",
//     "Shutdown Server" => "shutdown",
//    );
//    return $buttonarray;
//}

//function onappcdn_extrapage($params) {
//    $pagearray = array(
//     'onappcdnfile' => 'example',
//     'breadcrumb' => ' > <a href="#">Example Page</a>',
//     'vars' => array(
//        'var1' => 'demo1',
//        'var2' => 'demo2',
//     ),
//    );
//    return $pagearray;
//}

//function onappcdn_UsageUpdate($params) {

//    $serverid = $params['serverid'];
//    $serverhostname = $params['serverhostname'];
//    $serverip = $params['serverip'];
//    $serverusername = $params['serverusername'];
//    $serverpassword = $params['serverpassword'];
//    $serveraccesshash = $params['serveraccesshash'];
//    $serversecure = $params['serversecure'];

//    # Run connection to retrieve usage for all domains/accounts on $serverid

//    # Now loop through results and update DB

//    foreach ($results AS $domain=>$values) {
//        update_query("tblhosting",array(
//         "diskused"=>$values['diskusage'],
//         "dislimit"=>$values['disklimit'],
//         "bwused"=>$values['bwusage'],
//         "bwlimit"=>$values['bwlimit'],
//         "lastupdate"=>"now()",
//        ),array("server"=>$serverid,"domain"=>$values['domain']));
//    }

//}

//function onappcdn_AdminServicesTabFields($params) {

//    $result = select_query("mod_customtable","",array("serviceid"=>$params['serviceid']));
//    $data = mysql_fetch_array($result);
//    $var1 = $data['var1'];
//    $var2 = $data['var2'];
//    $var3 = $data['var3'];
//    $var4 = $data['var4'];

//    $fieldsarray = array(
//     'Field 1' => '<input type="text" name="modulefields[0]" size="30" value="'.$var1.'" />',
//     'Field 2' => '<select name="modulefields[1]"><option>Val1</option</select>',
//     'Field 3' => '<textarea name="modulefields[2]" rows="2" cols="80">'.$var3.'</textarea>',
//     'Field 4' => $var4, # Info Output Only
//    );
//    return $fieldsarray;

//}

//function onappcdn_AdminServicesTabFieldsSave($params) {
//    update_query("mod_customtable",array(
//        "var1"=>$_POST['modulefields'][0],
//        "var2"=>$_POST['modulefields'][1],
//        "var3"=>$_POST['modulefields'][2],
//    ),array("serviceid"=>$params['serviceid']));
//}

function onappcdn_UsageUpdate($params) {
// 
//    $serverid = $params['serverid'];
//    $serverhostname = $params['serverhostname'];
//    $serverip = $params['serverip'];
//    $serverusername = $params['serverusername'];
//    $serverpassword = $params['serverpassword'];
//    $serveraccesshash = $params['serveraccesshash'];
//    $serversecure = $params['serversecure'];
// 
//    # Run connection to retrieve usage for all domains/accounts on $serverid

//    # Now loop through results and update DB

//    foreach ($results AS $domain=>$values) {
//        update_query("tblhosting",array(
//         "diskused"=>$values['diskusage'],
//         "dislimit"=>$values['disklimit'],
//         "bwused"=>$values['bwusage'],
//         "bwlimit"=>$values['bwlimit'],
//         "lastupdate"=>"now()",
//        ),array("server"=>$serverid,"domain"=>$values['domain']));
//    }
}
*/
