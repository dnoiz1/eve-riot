<div class="row">
    <div class="span12 content" role="main">
        <h1>$Title</h1>
        $Content

        <% if Regions %>
            <p>
                Displaying Timers for Regions:
                <% loop Regions %>
                    $regionName<% if Last %><% else %>,<% end_if %>
                <% end_loop %>
            </p>
        <% end_if %>

        <% if HasPerm('EVE_TIMERSNOT') %>
                <div id="AddTimerFormWrapper" class="hide">
                    $AddTimerForm
                </div>
                <a id="showAddTimerForm" href="javascript:void(0)" class="btn btn-primary">Add</a>
        <% end_if %>

        <table class="table table-striped table-bordered table-condensed timers">
            <thead>
                <tr>
                    <th>Timer</th>
                    <th>Eve Time</th>
                    <th>Region</th>
                    <th>Solar System</th>
                    <th>Planet - Moon</th>
                    <th>Type</th>
                    <th>Defended</th>
                    <th>Timer</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                <% if Timers %>
                    <% loop Timers %>
                        <tr class="<% if $Friendly = No %>error<% else %>info<% end_if %><% if $Timer = Final %> strong<% end_if %>">
                            <td class="countdown">$TimerEnds.Format(U)</td>
                            <td>$TimerEnds.Format(H:i d/m/Y)</td>
                            <td>$TargetSystem.Region.regionName</td>
                            <td>$TargetSystem.solarSystemName</td>
                            <td>
                                <% if $Planet %>$Planet<% if $Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                            </td>
                            <td>
                                <% if $Type = 'Tower' %>
                                    <% if $Size != 'N/A' %>$Size<% end_if %>
                                    <% if $Faction != 'N/A' %>$Faction<% end_if %>
                                <% end_if %>
                                $Type
                                <% if $FurtherInfo %>
                                    <a href="#info-modal-{$ID}" role="button" data-toggle="modal">
                                        <i class="icon-info-sign"></i>
                                    </a>
                                    <div id="info-modal-{$ID}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="info-modal-{$ID}-label" aria-hidden="true">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h3 id="info-modal-{$ID}-label">
                                                <% if $Type = 'Tower' %>
                                                    <% if $Size != 'N/A' %>$Size<% end_if %>
                                                    <% if $Faction != 'N/A' %>$Faction<% end_if %>
                                                <% end_if %>
                                                $Type in
                                                $TargetSystem.solarSystemName at
                                                <% if $Planet %>$Planet<% if $Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                                            </h3>
                                        </div>
                                        <div class="modal-body">
                                            <p>$FurtherInfo</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                        </div>
                                    </div>
                                <% end_if %>
                            </td>
                            <td>$Defended</td>
                            <td>$Timer</td>
                            <td>$Owner</td>
                        </tr>
                    <% end_loop %>
                <% else %>
                    <tr>
                        <td colspan="9"><em>No Upcoming Timers</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>

        <h4>Past Timers in 48H</h4>
        <table class="table table-striped table-bordered table-condensed timers">
            <thead>
                <tr>
                    <th>Timer</th>
                    <th>Eve Time</th>
                    <th>Region</th>
                    <th>Solar System</th>
                    <th>Planet - Moon</th>
                    <th>Type</th>
                    <th>Defended</th>
                    <th>Timer</th>
                    <th>Owner</th>
                </tr>
            </thead>
            <tbody>
                <% if PastTimers %>
                    <% loop PastTimers %>
                        <tr class="<% if $Friendly = No %>error<% else %>info<% end_if %><% if $Timer = Final %> strong<% end_if %>">
                            <td class="countdown">$TimerEnds.Format(U)</td>
                            <td>$TimerEnds.Format(H:i d/m/Y)</td>
                            <td>$TargetSystem.Region.regionName</td>
                            <td>$TargetSystem.solarSystemName</td>
                            <td>
                                <% if Planet %>$Planet<% if Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                            </td>
                            <td>
                                <% if $Type = 'Tower' %>
                                    <% if $Size != 'N/A' %>$Size<% end_if %>
                                    <% if $Faction != 'N/A' %>$Faction<% end_if %>
                                <% end_if %>
                                $Type
                                <% if $FurtherInfo %>
                                    <a href="#info-modal-{$ID}" role="button" data-toggle="modal">
                                        <i class="icon-info-sign"></i>
                                    </a>
                                    <div id="info-modal-{$ID}" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="info-modal-{$ID}-label" aria-hidden="true">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h3 id="info-modal-{$ID}-label">
                                                <% if $Type = 'Tower' %>
                                                    <% if $Size != 'N/A' %>$Size<% end_if %>
                                                    <% if $Faction != 'N/A' %>$Faction<% end_if %>
                                                <% end_if %>
                                                $Type in
                                                $TargetSystem.solarSystemName at
                                                <% if $Planet %>$Planet<% if $Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                                            </h3>
                                        </div>
                                        <div class="modal-body">
                                            <p>$FurtherInfo</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                        </div>
                                    </div>
                                <% end_if %>
                            </td>
                            <td>$Defended</td>
                            <td>$Timer</td>
                            <td>$Owner</td>
                          </tr>
                    <% end_loop %>
                <% else %>
                    <tr>
                        <td colspan="9"><em>No Past Timers</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>

        $Form

    </div>
</div>
