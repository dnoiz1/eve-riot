<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <p>
                Displaying Timers for Regions:
                <% control Regions %>
                    $regionName<% if Last %><% else %>,<% end_if %>
                <% end_control %>
            </p>

            <table class="full tborder timers">
                <thead>
                    <tr>
                        <th colspan="8">Upcoming Timers</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat" colspan="2">Timer</td>
                        <td class="tcat">Solar System</td>
                        <td class="tcat">Planet - Moon</td>
                        <td class="tcat">Type</td>
                        <td class="tcat">Defended</td>
                        <td class="tcat">Timer</td>
                        <td class="tcat">Owner</td>
                    </tr>
                    <% if Timers %>
                        <% control Timers %>
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
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="8"><em>No Upcoming Timers</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

            <table class="full tborder timers">
                <thead>
                    <tr>
                        <th colspan="8">Past Timers in 48H</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat" colspan="2">Timer</td>
                        <td class="tcat">Solar System</td>
                        <td class="tcat">Planet - Moon</td>
                        <td class="tcat">Type</td>
                        <td class="tcat">Defended</td>
                        <td class="tcat">Timer</td>
                        <td class="tcat">Owner</td>
                    </tr>
                    <% if PastTimers %>
                        <% control PastTimers %>
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
                        <% end_control %>
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
