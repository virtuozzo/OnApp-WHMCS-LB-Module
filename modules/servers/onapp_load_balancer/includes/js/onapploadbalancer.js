$(document).ready(function(){
    
    $('head').append(css) 

// Create Default Port Element //    
    var port_label = LANG['onapplbdefaultport'] 
    var port_html  = '<input class="lb" value="'+ lbPort +'" type="text" name="lb[port]" />'
// END Create Default Port Element //

// Create Hypervisor Zone select Element //
    var hv_zones_label = LANG['onapplbhvzone']
    var hv_zones_html  = '<select name="lb[hv_zone]">'
        
    for ( var option in hvZones ) {
        var selected = ( option == hvZoneId ) ? ' selected="selected"' : ''

        hv_zones_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ hvZones[option] +
            '</option>';
    }

    hv_zones_html += '</select>' 
// END Create Hypervisor Zone select Element //
 
// Create Hypervisor select Element //
/////////////////////////////////////
    var hv_label = LANG['onapplbhv']
    var hv_html  = '<select name="lb[hv]">'
        
    for ( var option in hv ) {
        var selected = ( option == hvId ) ? ' selected="selected"' : ''

        hv_html +=
            '<option zone="'+hvZonesRel[option]+'" value="'+option+'"'+selected+'>'+
                ''+ hv[option] +
            '</option>';
    }

    hv_html += '</select>' 
// END Create Hypervisor select Element //
/////////////////////////////////////////

// Create Network Zone select Element //
///////////////////////////////////////
    var nz_label = LANG['onapplbnetworkzone']
    var nz_html  = '<select name="lb[nz]">'
        
    for ( var option in nz ) {
        var selected = ( option == nzId ) ? ' selected="selected"' : ''

        nz_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ nz[option] +
            '</option>';
    }

    nz_html += '</select>' 
// END Create Network Zone select Element //
///////////////////////////////////////////

// Create Port Speed Element //
//////////////////////////////
    
    var port_speed_label = LANG['onapplbportspeed'];
    var port_speed_html  = '<input name="lb[rate_limit]" type="text" size="5" value="'+ portSpeed +'" /> Mbps ( Unlimited if not set )'
    var port_speed_slider = create_slider_html(port_speed_html, 1000, 0, 1, 'lb[rate_limit]'); 
    
// End Port Speed Elements //
////////////////////////////

// Create Templates Element //
/////////////////////////////
    var tpl_label = LANG['onapplbimagetemplate']
    var tpl_html  = '<select name="lb[image_template_id]">'
        
    for ( var option in tpl ) {
        var selected = ( option == tplId ) ? ' selected="selected"' : ''

        tpl_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ tpl[option] +
            '</option>';
    }

    tpl_html += '</select>' 
// END Create Templates select Element //
////////////////////////////////////////

// Create Load Balancer Type Elements //
///////////////////////////////////////

    var type_cluster_html = '<input id="settings_cluster" type="radio" name="lb[cluster_type]" value="cluster"/>'
    var type_cluster_label = LANG['onapplbcluster']
    
    var type_autoscaleout_html = '<input id="settings_autoscaleout" type="radio" name="lb[cluster_type]" value="autoscaleout" />'
    var type_autoscaleout_label = LANG['onapplbautoscaling']

// End Create Load Balancer Type Elements //
///////////////////////////////////////////

// Create Nodes Element //
/////////////////////////
    var min_nodes_label = LANG['onapplbminnodamount'] 
    var min_nodes_html   = '<input class="lb" value="'+ minNodeAmount +'" type="text" name="lb[config][min_node_amount]" />'
// END Create Nodes Element //
/////////////////////////////

// Create Nodes Element //
/////////////////////////
    var max_nodes_label = LANG['onapplbmaxnodamount'] 
    var max_nodes_html   = '<input value="'+ maxNodeAmount +'" class="lb" type="text" name="lb[config][max_node_amount]" />'
// END Create Nodes Element //
/////////////////////////////

// Create Memory Element //
//////////////////////////
    
    var memory_label = LANG['onapplbmemory'];
    var memory_html  = '<input name="lb[node_attributes][memory]" type="text" size="5" value="'+ nodeMemory +'" /> MB'
    var memory_slider = create_slider_html(memory_html, 12288, 256, 4, 'lb[node_attributes][memory]');
    
// End Create Memory Element //
//////////////////////////////

// Create Cpus Element //
////////////////////////
    
    var cpus_label = LANG['onapplbcpus'];
    var cpus_html  = '<input name="lb[node_attributes][cpus]" type="text" size="5" value="'+ nodeCpus +'" />'
    var cpus_slider = create_slider_html(cpus_html, 16, 1, 1, 'lb[node_attributes][cpus]');
    
// End Create Cpus Element //
////////////////////////////

// Create Cpus Element //
////////////////////////
    
    var cpu_shares_label  = LANG['onapplbcpuguarantee'];
    var cpu_shares_html   = '<input name="lb[node_attributes][cpu_shares]" type="text" size="5" value="'+ cpuShares +'" /> %'
    var cpu_shares_slider = create_slider_html(cpu_shares_html, 100, 1, 1, 'lb[node_attributes][cpu_shares]');
    
// End Create Cpus Element //
////////////////////////////

// Create Port Speed Element //
//////////////////////////////
    
    var rate_limit_label = LANG['onapplbratelimit'];
    var rate_limit_html  = '<input name="lb[node_attributes][rate_limit]" type="text" size="5" value="'+ nodeRateLimit +'" /> Mbps ( Unlimited if not set )'
    var rate_limit_slider = create_slider_html(rate_limit_html, 1000, 0, 1, 'lb[node_attributes][rate_limit]'); 
    
// End Port Speed Elements //
////////////////////////////

// Remove spare elements
    $('table>tbody').eq(5).children().eq(0).remove()
    
// The second table //
    var secondTable = $('table>tbody').eq(5)
    var secondTableAfter = $('table').eq(5)
    
    secondTable.append( row_title( LANG['onapplbclusterconfiguration'], 2 )  )
    
    secondTable.append( row_html( port_label, port_html ) )
    
    secondTable.append( row_title( LANG['onapplbinstance'], 2 ) )
    
    secondTable.append( row_html( hv_zones_label, hv_zones_html ) )
    secondTable.append( row_html( hv_label, hv_html ) )
    secondTable.append( row_html( nz_label, nz_html ) )
    secondTable.append( row_html( port_speed_label, port_speed_slider ) )
    
    secondTable.append( row_title( LANG['onapplbtype'] , 2 ))
    
    secondTable.append( row_html( type_cluster_label, type_cluster_html) )
    secondTable.append( row_html( type_autoscaleout_label, type_autoscaleout_html  ) )
    
    
// Layouts depends on cluster type
    var table_html = '<br /><table width="100%" style="display:none;" class="form" id="autoscaling_type" border="0" sellspacing="2" cellpadding="3"><tbody></tbody></table>';


// AutoScale Staff //
////////////////////
    var outCpuUsageLabels = [ LANG['onapplbifcpuusage'], LANG['onapplbisabove'], '%', LANG['onapplbfor'], LANG['onapplbminutes'], LANG['onapplbadd'], LANG['onapplbmorevms'] ]
    var outCpuUsageNames  = ['lb[auto_scaling_out_cpu_attributes][enabled]','lb[auto_scaling_out_cpu_attributes][value]', 'lb[auto_scaling_out_cpu_attributes][for_minutes]', 'lb[auto_scaling_out_cpu_attributes][units]']

    var outFreeRamLabels = [ LANG['onapplbiffreeram'], LANG['onapplbislessthen'], 'MB', LANG['onapplbfor'], LANG['onapplbminutes'], LANG['onapplbadd'], LANG['onapplbmorevms'] ]
    var outFreeRamNames  = ['lb[auto_scaling_out_memory_attributes][enabled]','lb[auto_scaling_out_memory_attributes][value]', 'lb[auto_scaling_out_memory_attributes][for_minutes]', 'lb[auto_scaling_out_memory_attributes][units]']

    var inCpuUsageLabels = [ LANG['onapplbifcpuusage'], LANG['onapplbislessthen'], '%', LANG['onapplbfor'], LANG['onapplbminutes'], LANG['onapplbremove'], LANG['onapplbmorevms'] ]
    var inCpuUsageNames  = ['lb[auto_scaling_in_cpu_attributes][enabled]','lb[auto_scaling_in_cpu_attributes][value]', 'lb[auto_scaling_in_cpu_attributes][for_minutes]', 'lb[auto_scaling_in_cpu_attributes][units]']

    var inFreeRamLabels = [ LANG['onapplbiffreeram'], LANG['onapplbmorethen'], 'MB', LANG['onapplbfor'], LANG['onapplbminutes'], LANG['onapplbremove'], LANG['onapplbmorevms'] ]
    var inFreeRamNames  = ['lb[auto_scaling_in_memory_attributes][enabled]','lb[auto_scaling_in_memory_attributes][value]', 'lb[auto_scaling_in_memory_attributes]][for_minutes]', 'lb[auto_scaling_in_memory_attributes][units]']
// END AutoScale Staff //
////////////////////////

// Drow Configs Tables //
////////////////////////
    secondTableAfter.after( table_html )
    secondTableAfter.after( table_html )
    
    var thirdTable  = $('table>tbody').eq(6)
    var forthTable  = $('table#autoscaling_type').eq(1)

    thirdTable.append( row_title( LANG['onapplbclusternodetemplates'], 2 ) )
    thirdTable.append( row_html( tpl_label, tpl_html) )
    thirdTable.append( row_html( min_nodes_label, min_nodes_html) )
    thirdTable.append( row_html( max_nodes_label, max_nodes_html) )
    thirdTable.append( row_title ( LANG['onapplbclusternodeparameters'], 2 ))
    thirdTable.append( row_html( memory_label, memory_slider) )
    thirdTable.append( row_html( cpus_label, cpus_slider) )
    thirdTable.append( row_html( cpu_shares_label, cpu_shares_slider) )
    thirdTable.append( row_html( rate_limit_label, rate_limit_slider) )
    
    forthTable.append( row_title( LANG['onapplbautoscaleoutparams'], 8 ) )
    forthTable.append( autoscaling_row_html( outCpuUsageLabels, outCpuUsageNames ) )
    forthTable.append( autoscaling_row_html( outFreeRamLabels, outFreeRamNames ) )
    forthTable.append( row_title( LANG['onapplbautoscaleinparams'], 8 ) )
    forthTable.append( autoscaling_row_html( inCpuUsageLabels, inCpuUsageNames ) )
    forthTable.append( autoscaling_row_html( inFreeRamLabels, inFreeRamNames ) )
// End Drow Config Tables //
///////////////////////////
 
// show / hide Autoscaling panel 
    $("input[name='lb[cluster_type]']").change(function(){
        var type = $('input[name="lb[cluster_type]"]:checked').val()
        
        if ( type == 'cluster' ) {
            $('table#autoscaling_type').hide()
        } else {
           $('table#autoscaling_type').show()
        }
    });
    
// set default cluster type    
    if ( lbType == 'cluster' ) {
        $('input#settings_cluster').attr('checked', 'checked').change()
    } else {
        $('input#settings_autoscaleout').attr('checked', 'checked').change()
    }    

// Deal Hypervisor Zone / Hypervisor //
//////////////////////////////////////
    hvZonesSelect = $('select[name="lb[hv_zone]"]')
    hvSelect      = $('select[name="lb[hv]"]')
    
    hvSelectHtml  = hvSelect.html()
    hvHtml  = hvSelect.html()
    
    hvZonesSelect.change( function () {
        deal_hvs()
    })
    deal_hvs()
    
// disable hypervisor zones options with no hypervisors
    hvZonesSelect.children().each( function () {
        
        if ( in_array( $(this).val(), hvZonesRel ) === false &&
            $(this).val() != 0 && hvZones[$(this).val()] != 'autoselect' ) {
            
            $(this).attr('disabled', 'disabled')
        }
    })     
// End Deal Hypervisor Zone / Hypervisor //
//////////////////////////////////////////
    
// Manage Autoscale Parameters //
////////////////////////////////
// disable
    $('input[name^="lb[auto_scaling_"], select[name^="lb[auto_scaling_"]').each(function(){
        if ( $(this).attr('name').indexOf('[enabled]') == -1 ) {
            $(this).attr('disabled', 'disabled')
        }
    })
// selects
    onChangeCheckboxes( 'out_cpu' )
    onChangeCheckboxes( 'out_memory')
    onChangeCheckboxes( 'in_cpu')
    onChangeCheckboxes( 'in_memory')
// checkboxes
    if ( outCpuEnabled )   $('input[name="lb[auto_scaling_out_cpu_attributes][enabled]"]').attr('checked', 'checked').change()
    if ( inCpuEnabled )    $('input[name="lb[auto_scaling_in_cpu_attributes][enabled]"]').attr('checked', 'checked').change()
    if ( outMemoryEnabled )$('input[name="lb[auto_scaling_out_memory_attributes][enabled]"]').attr('checked', 'checked').change()
    if ( inMemoryEnabled ) $('input[name="lb[auto_scaling_in_memory_attributes][enabled]"]').attr('checked', 'checked').change()
    // inputs and selects
    if ( outCpuValue )     $('input[name="lb[auto_scaling_out_cpu_attributes][value]"]').val( outCpuValue )
    if ( outCpuMinutes )   $('select[name="lb[auto_scaling_out_cpu_attributes][for_minutes]"]').val( outCpuMinutes )
    if ( outCpuUnits )     $('input[name="lb[auto_scaling_out_cpu_attributes][units]"]').val( outCpuUnits )
    if ( outMemoryValue )  $('input[name="lb[auto_scaling_out_memory_attributes][value]"]').val( outMemoryValue )
    if ( outMemoryMinutes )$('select[name="lb[auto_scaling_out_memory_attributes][for_minutes]"]').val( outMemoryMinutes )
    if ( outMemoryUnits )  $('input[name="lb[auto_scaling_out_memory_attributes][units]"]').val( outMemoryUnits )
    if ( inCpuValue )      $('input[name="lb[auto_scaling_in_cpu_attributes][value]"]').val( inCpuValue )
    if ( inCpuMinutes )    $('select[name="lb[auto_scaling_in_cpu_attributes][for_minutes]"]').val( inCpuMinutes )
    if ( inCpuUnits )      $('input[name="lb[auto_scaling_in_cpu_attributes][units]"]').val( inCpuUnits )
    if ( inMemoryValue )   $('input[name="lb[auto_scaling_in_memory_attributes][value]"]').val( inMemoryValue )
    if ( inMemoryMinutes ) $('select[name="lb[auto_scaling_in_memory_attributes]][for_minutes]"]').val( inMemoryMinutes )
    if ( inMemoryUnits )   $('input[name="lb[auto_scaling_in_memory_attributes][units]"]').val( inMemoryUnits )
// End Manage Autoscale Parameters //
////////////////////////////////////

// form submit action
    var form = $("form[name$='packagefrm']");
    
    form.submit(function() {
        prepare_config_json()
//        return false
    });
    
});

// Functions //
//////////////
function deal_hvs () {
    var zone =  hvZonesSelect.val()
        hvSelect.html(hvSelectHtml)
        if ( zone != '0' ) {
            hvSelect.removeAttr('disabled')

            if ( hvZonesSelect.val() != 'no_zone' ) {
                hvSelect.children().each( function () {
                    if ( zone != $(this).attr('zone') && hv[$(this).val()] != 'autoselect' ) {
                        $(this).remove()
                    }
                })
            }
        }
        else {
            hvSelect.val('0')
            hvSelect.attr('disabled', 'disabled');
        }
}

function in_array(needle, haystack){
    for( var i in haystack){
        if(needle == haystack[i]){
            return true;
        }
    }
    return false;
}

function create_slider_html(input_html, max, min, step, name) { 
    return '<div class="input-with-slider">'+
                 input_html+
            '    <div class="slider" style="float:left; margin:5px 15px 0 5px; width:200px;" max="'+max+'" min="'+min+'" step="'+step+'" target="'+name+'" width="200"></div>'+
            '</div>';
}

function prepare_config_json () {
    var form = $("form[name$='packagefrm']");
    var f = []
    
    f.port               = $("input[name='lb[port]']").val()
    f.hv_zone            = $("select[name='lb[hv_zone]']").val()
    f.hv                 = $("select[name='lb[hv]']").val()
    f.n_zone             = $("select[name='lb[nz]']").val()
    f.port_speed         = $("input[name='lb[rate_limit]']").val()
    f.cluster_type       = $('input[name="lb[cluster_type]"]:checked').val()
    f.node_template      = $("select[name='lb[image_template_id]']").val()
    f.min_node_amount    = $("input[name='lb[config][min_node_amount]']").val()
    f.max_node_amount    = $("input[name='lb[config][max_node_amount]']").val()
    f.node_memory        = $("input[name='lb[node_attributes][memory]']").val()
    f.node_cpus          = $("input[name='lb[node_attributes][cpus]']").val()
    f.node_cpu_shares    = $("input[name='lb[node_attributes][cpu_shares]']").val()
    f.node_rate_limit    = $("input[name='lb[node_attributes][rate_limit]']").val()
    f.out_cpu_enabled    = $('input[name="lb[auto_scaling_out_cpu_attributes][enabled]"]:checked').val() ? 1 : ''
    f.out_cpu_value      = $("input[name='lb[auto_scaling_out_cpu_attributes][value]']").val()
    f.out_cpu_minutes    = $("select[name='lb[auto_scaling_out_cpu_attributes][for_minutes]']").val()
    f.out_cpu_units      = $("input[name='lb[auto_scaling_out_cpu_attributes][units]']").val()
    f.out_memory_enabled = $("input[name='lb[auto_scaling_out_memory_attributes][enabled]']:checked").val() ? 1 : ''
    f.out_memory_value   = $("input[name='lb[auto_scaling_out_memory_attributes][value]']").val()
    f.out_memory_minutes = $("select[name='lb[auto_scaling_out_memory_attributes][for_minutes]']").val()
    f.out_memory_units   = $("input[name='lb[auto_scaling_out_memory_attributes][units]']").val()
    f.in_cpu_enabled     = $("input[name='lb[auto_scaling_in_cpu_attributes][enabled]']:checked").val() ? 1 : ''
    f.in_cpu_value       = $("input[name='lb[auto_scaling_in_cpu_attributes][value]']").val()
    f.in_cpu_minutes     = $("select[name='lb[auto_scaling_in_cpu_attributes][for_minutes]']").val()
    f.in_cpu_units       = $("input[name='lb[auto_scaling_in_cpu_attributes][units]']").val()
    f.in_memory_enabled  = $("input[name='lb[auto_scaling_in_memory_attributes][enabled]']:checked").val() ? 1 : ''
    f.in_memory_value    = $("input[name='lb[auto_scaling_in_memory_attributes][value]']").val()
    f.in_memory_minutes  = $("select[name='lb[auto_scaling_in_memory_attributes]][for_minutes]']").val()
    f.in_memory_units    = $("input[name='lb[auto_scaling_in_memory_attributes][units]']").val()
    
    var configurations = '{'
    
    for ( var i in f ) {
        configurations += '"'+i+'":"'+f[i]+'", '
    }
    
    configurations = configurations.replace(/,\s$/, '}');
    
    var html =
        "<input type='hidden' value='"+ configurations +"' name='packageconfigoption[2]'/>"

    form.append(html)
}

function onChangeCheckboxes ( attr ) {
    $('input[name="lb[auto_scaling_'+ attr + '_attributes][enabled]"]').change( function () {
        if ( this.checked ) {
            $('input[name^="lb[auto_scaling_'+ attr +'"], select[name^="lb[auto_scaling_'+ attr + '"]').removeAttr('disabled')
        } else {
            $('input[name^="lb[auto_scaling_'+ attr + '"], select[name^="lb[auto_scaling_'+ attr + '"]').not('input[name*="enabled"]').attr('disabled', 'disabled')
        }
    })
}
// END Functions //
//////////////////

