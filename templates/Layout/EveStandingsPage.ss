<div class="row">
    <div class="span12">
        <h1>$Title</h1>
        $Content

            <table class="table table-striped table-bordered table-condensed dataTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Alliance Name</th>
                        <th>Ticker</th>
                        <th>Standing</th>
                        <% if HasPerm('ADMIN') %>
                            <th>Registered Members</th>
                        <% end_if %>
                    </tr>
                </thead>
                <tbody>
                    <% if $Alliances %>
                        <% loop $Alliances %>
                            <tr>
                                <td><img src="//image.eveonline.com/alliance/{$AllianceID}_32.png" /></td>
                                <td>$AllianceName.XML</td>
                                <td>$Ticker.XML</td>
                                <td>$Standing</td>
                                <% if HasPerm('ADMIN') %>
                                    <td>$Group.Members.Count</td>
                                <% end_if %>
                            </tr>
                        <% end_loop %>
                    <% else %>
                        <tr>
                            <td colspan="4"><em>No Standings Set</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>
             
    </div>
</div>
