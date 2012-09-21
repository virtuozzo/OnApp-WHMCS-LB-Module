<table width="100%" cellspacing="2" cellpadding="3" border="0" class="form">
    {if isset($NoServers)}
        <tr>
            <td class="fieldarea" colspan="2">
				<b>{$NoServers}</b>
			</td>
        </tr>
	{else}
		{foreach from=$serversData.Servers key=id item=server}
			{if isset($serversData.Group[$server.GID])}
				{assign var='selectedHVZ' value=$serversData.Group[$server.GID].SelectedHVZ[$id]}
				{assign var='selectedHV' value=$serversData.Group[$server.GID].SelectedHV[$id]}
				{assign var='selectedNZ' value=$serversData.Group[$server.GID].SelectedNZ[$id]}
				{assign var='selectedPortSpeed' value=$serversData.Group[$server.GID].SelectedPortSpeed[$id]}
				{assign var='selectedTypeCluster' value=$serversData.Group[$server.GID].SelectedType[$id][0]}
				{assign var='selectedTypeAutoScaling' value=$serversData.Group[$server.GID].SelectedType[$id][1]}
			{else}
				{assign var='selectedHVZ' value=0}
				{assign var='selectedHV' value=0}
				{assign var='selectedNZ' value=0}
				{assign var='selectedPortSpeed' value=0}
				{assign var='selectedTypeCluster' value=0}
				{assign var='selectedTypeAutoScaling' value=0}
			{/if}
			<tr>
				<td class="fieldarea" colspan="2">
					<b>{$server.Name}</b>
				</td>
			</tr>
			<tr>
				<td class="fieldlabel">{$LANG.onapplbhvzone}</td>
				<td class="fieldarea">
					<select name="hvzones_packageconfigoption{$id}" style="min-width: 168px;">
					{foreach from=$server.HypervisorZones key=i item=hvz}
						{if $i eq $selectedHVZ}
                            <option value="{$id}:{$i}" selected="selected">{$hvz}</option>
						{else}
                            <option value="{$id}:{$i}">{$hvz}</option>
						{/if}
					{/foreach}
				</select></td>
			</tr>
			<tr>
				<td class="fieldlabel">{$LANG.onapplbhv}</td>
				<td class="fieldarea">
					<select name="hvs_packageconfigoption{$id}" style="min-width: 168px;">
					{foreach from=$server.Hypervisors key=i item=hv}
						{if $i eq $selectedHV}
                            <option value="{$id}:{$i}" selected="selected">{$hv.label}</option>
						{else}
                            <option value="{$id}:{$i}">{$hv.label}</option>
						{/if}
					{/foreach}
				</select></td>
			</tr>
			<tr>
				<td class="fieldlabel">{$LANG.onapplbnetworkzone}</td>
				<td class="fieldarea"><select name="nzs_packageconfigoption{$id}" style="min-width: 168px;">
					{foreach from=$server.NetworkZones key=i item=nz}
						{if $i eq $selectedNZ}
                            <option value="{$id}:{$i}" selected="selected">{$nz}</option>
						{else}
                            <option value="{$id}:{$i}">{$nz}</option>
						{/if}
					{/foreach}
				</select></td>
			</tr>
			<tr>
				<td class="fieldlabel">{$LANG.onapplbportspeed}</td>
				<td class="fieldarea">
                    <input name="ps_packageconfigoption{$id}" type="text" size="5" value="{$selectedPortSpeed}" class="sld" rel="{$id}"/> Mbps ( Unlimited if not set )
				</td>
			</tr>
            <tr>
                <td class="fieldlabel">{$LANG.onapplballowedtypes}</td>
                <td class="fieldarea">
					{if $selectedTypeCluster}
                        <input type="checkbox" rel="{$id}" name="lbtypes1" checked="checked"> {$LANG.onapplballowedtypecluster}
					{else}
                        <input type="checkbox" rel="{$id}" name="lbtypes1"> {$LANG.onapplballowedtypecluster}
					{/if}
					{if $selectedTypeAutoScaling}
                        <input type="checkbox" rel="{$id}" name="lbtypes2" checked="checked"> {$LANG.onapplballowedtypeautoscaling}
					{else}
                        <input type="checkbox" rel="{$id}" name="lbtypes2"> {$LANG.onapplballowedtypeautoscaling}
					{/if}
                </td>
            </tr>
		{/foreach}
    {/if}
</table>
<input type="hidden" name="packageconfigoption[1]" id="bp2s" value="" size="180">