$(document).ready(function(){

// getting server configoption
    var serverId  =  $('input[name="packageconfigoption[1]"]').val()
    
// getting  other configs
    configs = new Object()
    configs = jQuery.parseJSON( $('input[name="packageconfigoption[2]"]').val() )
    
// Assign config values , 0 if while first config //
///////////////////////////////////////////////////
    
    hvZoneId         =  ( configs ) ?  configs.hv_zone            :    0
    hvId             =  ( configs ) ?  configs.hv                 :    0
    nzId             =  ( configs ) ?  configs.n_zone             :    0
    lbType           =  ( configs ) ?  configs.cluster_type       :    'cluster'
    lbPort           =  ( configs ) ?  configs.port               :    ''
    portSpeed        =  ( configs ) ?  configs.port_speed         :    0
    tplId            =  ( configs ) ?  configs.node_template      :    0
    minNodeAmount    =  ( configs ) ?  configs.min_node_amount    :    ''
    maxNodeAmount    =  ( configs ) ?  configs.max_node_amount    :    ''
    nodeMemory       =  ( configs ) ?  configs.node_memory        :    ''
    nodeCpus         =  ( configs ) ?  configs.node_cpus          :    ''
    cpuShares        =  ( configs ) ?  configs.node_cpu_shares    :    ''
    nodeRateLimit    =  ( configs ) ?  configs.node_rate_limit    :    ''
    outCpuEnabled    =  ( configs ) ?  configs.out_cpu_enabled    :    ''
    outMemoryEnabled =  ( configs ) ?  configs.out_memory_enabled :    ''
    inMemoryEnabled  =  ( configs ) ?  configs.in_memory_enabled  :    ''
    inCpuEnabled     =  ( configs ) ?  configs.in_cpu_enabled     :    ''
///////////////////////////////////////////////////////////////////////////////    
    outCpuValue      =  ( configs ) ?  configs.out_cpu_value      :    ''
    outCpuMinutes    =  ( configs ) ?  configs.out_cpu_minutes    :    ''
    outCpuUnits      =  ( configs ) ?  configs.out_cpu_units      :    ''
    
    outMemoryValue   =  ( configs ) ?  configs.out_memory_value   :    ''
    outMemoryMinutes =  ( configs ) ?  configs.out_memory_minutes :    ''
    outMemoryUnits   =  ( configs ) ?  configs.out_memory_units   :    ''
    
    inCpuValue       =  ( configs ) ?  configs.in_cpu_value       :    ''
    inCpuMinutes     =  ( configs ) ?  configs.in_cpu_minutes     :    ''
    inCpuUnits       =  ( configs ) ?  configs.in_cpu_units       :    ''
    
    inMemoryValue    =  ( configs ) ?  configs.in_memory_value    :    ''
    inMemoryMinutes  =  ( configs ) ?  configs.in_memory_minutes  :    ''
    inMemoryUnits   =   ( configs ) ?  configs.in_memory_units    :    ''
    
console.log( configs )


/// END assign config values , 0 if while first config ///
/////////////////////////////////////////////////////////  

// remove spare elements
    $('input[name^="packageconf"]').remove()

// Init null option for selects
    var nullOption = '<option value = "0">'+LANG['onapplbservernotdefined']+'</option>'
  
/// Create Servers Sellect element ///
/////////////////////////////////////
    var servers_label = LANG['onapplbservers']
    var servers_html = '<select name="packageconfigoption[1]">'+  nullOption
        
    for ( var option in servers ) {
        var selected = ( option == serverId ) ? ' selected="selected"' : ''

        servers_html +=
            '<option value="'+option+'"'+selected+'>'+
                ''+ servers[option].name +
            '</option>';
    }

    servers_html += '</select>'
/// END Create Servers sellect element ///
/////////////////////////////////////////

/// Append HTML ///
//////////////////

// The first table
    var firstTable = $('table>tbody').eq(4);
    firstTable.append( row_html( servers_label, servers_html ))

/// ONCHANGE ACTIONS ///
///////////////////////

// assign server select onChange action
    var serverSelect = $("select[name='packageconfigoption[1]']");
    var form = $("form[name$='packagefrm']");

    serverSelect.change( function () {
        form.submit();
    })

// assign servergroup select onChange action
    var serverGroupSelect = $("select[name='servergroup']")

    serverGroupSelect.change( function () {
        serverSelect.val('')
        form.submit()
    })

// Remove spare elements
    $('table>tbody').eq(5).children().eq(0).remove()
    
/// END ONCHANGE ACTIONS ///
///////////////////////////


});

// Functions
function row_html(label, html) {
    return '<tr><td class="fieldlabel">'+label+'</td><td class="fieldarea">'+html+'</td></tr>';
}

function row_title( title, colspan ) {
    return '<tr><td class="fieldlabel" colspan="' + colspan + '"><b>' + title +'</b></td></tr>';
}

function autoscaling_row_html( labels, names ) {
    var minutes = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60]
    
    if ( names[0].indexOf('_in_') != -1 ) {
        minutes.splice( 0, 3 )
    }
    
    var minutesSelect = '<select class="some" style="float:left; width:50px;" name="'+ names[2] +'" >'
    for ( var option in minutes ) {
        minutesSelect +=
            '<option value="'+minutes[option]+'">'+
                ''+ minutes[option] +
            '</option>';
    }
    
    minutesSelect += '</select>'    
    
    return     '<tr>\n\
                    <td class="fieldlabel">' + labels[0] +'</td>\n\
                    <td class="fieldarea"><input name="'+ names[0] +'" type="checkbox" /></td>\n\
                    <td class="fieldlabel">' + labels[1] +' </td>\n\
                    <td class="fieldarea"><input name="'+ names[1] +'" style="width:30px;" type="text" />'+ labels[2] +'</td>\n\
                    <td class="fieldlabel">'+labels[3]+'</td>\n\
                    <td class="fieldarea">' + minutesSelect + ' <div style="float:left; padding:5px">'+ labels[4] +'</div></td>\n\
                    <td  class="fieldlabel">'+ labels[5] +'</td> \n\
                    <td colspan="6"class="fieldarea"><input style="width:30px" name="'+ names[3] +'" type="text" />' +labels[6]+ '</td>\n\
                </tr>'
}