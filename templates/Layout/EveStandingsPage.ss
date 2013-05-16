<div class="content-container">
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <table class="table table-striped table-bordered table-condensed dataTable">
                <thead>
                    <tr>
                        <th></th>
                        <th>Alliance Name</th>
                        <th>Ticker</th>
                        <th>Standing</th>
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
	</article>
</div>

<% include SideBar %>
