<div class="row-fluid">
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

        <h4>Upcoming Timers</h4>

        <table id="new-timers" class="table table-striped table-bordered table-condensed">
              <thead>
                  <tr>
                      <th colspan="2">Timer</th>
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
                                  <% if Planet %>$Planet<% if Moon %> - $Moon<% end_if %><% else %>Station<% end_if %>
                              </td>
                              <td>$Type</td>
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
        <table id="old-timers" class="table table-striped table-bordered table-condensed">
              <thead>
                  <tr>
                      <th colspan="2">Timer</th>
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
                              <td>$Type</td>
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
    <% include SideBar %>
</div>
