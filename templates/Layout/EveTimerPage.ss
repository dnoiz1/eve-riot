<div class="content-container">
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <% if Regions %>
                <p>
                    Displaying Timers for Regions:
                    <% loop Regions %>
                        $regionName<% if Last %><% else %>,<% end_if %>
                    <% end_loop %>
                </p>
            <% end_if %>

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th colspan="8">Upcoming Timers</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">Timer</td>
                        <td>Solar System</td>
                        <td>Planet - Moon</td>
                        <td>Type</td>
                        <td>Defended</td>
                        <td>Timer</td>
                        <td>Owner</td>
                    </tr>
                    <% if Timers %>
                        <% loop Timers %>
                            <tr>
                                <td class="countdown">$TimerEnds.Format(U)</td>
                                <td>$TimerEnds.Format(H:i d/m/Y)</td>
                                <td>$TargetSystem.solarSystemName<div class="region">$TargetRegion.regionName</div></td>
                                <td>
                                    <% if Planet %>$Planet<% if Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                                </td>
                                <td>$Type</td>
                                <td>$Defended</td>
                                <td>$Timer</td>
                                <td class="<% if Friendly = No %>not-<% end_if %>friendly">$Owner</td>
                            </tr>
                        <% end_loop %>
                    <% else %>
                        <tr>
                            <td colspan="8"><em>No Upcoming Timers</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th colspan="8">Past Timers in 48H</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2">Timer</td>
                        <td>Solar System</td>
                        <td>Planet - Moon</td>
                        <td>Type</td>
                        <td>Defended</td>
                        <td>Timer</td>
                        <td>Owner</td>
                    </tr>
                    <% if PastTimers %>
                        <% loop PastTimers %>
                            <tr>
                                <td class="countdown">$TimerEnds.Format(U)</td>
                                <td>$TimerEnds.Format(H:i d/m/Y)</td>
                                <td>$TargetSystem.solarSystemName<div class="region">$TargetRegion.regionName</div></td>
                                <td>
                                    <% if Planet %>$Planet<% if Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                                </td>
                                <td>$Type</td>
                                <td>$Defended</td>
                                <td>$Timer</td>
                                <td class="<% if Friendly = No %>not-<% end_if %>friendly">$Owner</td>
                            </tr>
                        <% end_loop %>
                    <% else %>
                        <tr>
                            <td colspan="8"><em>No Past Timers</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>
             
        </div>
	</article>
</div>

<% include SideBar %>
