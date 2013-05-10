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

            <h4>Upcoming Timers</h4>

            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th colspan="2">Timer</th>
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

            <h4>Past Timers in 48H</h4>
            <table class="table table-striped table-bordered table-condensed">
                <thead>
                    <tr>
                        <th colspan="2">Timer</th>
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
