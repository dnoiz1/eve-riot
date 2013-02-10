<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <table class="full tborder timers">
                <thead>
                    <tr>
                        <th colspan="6">Blacklisted Accounts</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Type</td>
                        <td class="tcat">Value</td>
                        <td class="tcat">Source</td>
                        <td class="tcat">Reason</td>
                        <td class="tcat">Added</td>
                        <td class="tcat"> By</td>                        
                    </tr>
                    <% if Blacklist %>
                        <% control Blacklist  %>
                            <tr>
                                <td>$Type.XML</td>
                                <td>$Value.XML</td>
                                <td>$Source.XML</td>
                                <td>$Reason.XML</td>
                                <td>$Created.Nice</td>
                                <td>$AddedBy.XML</td>
                            </tr>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="6"><em>No Pilots on the blacklist (yet)</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

        </div>
	</article>
</div>

<% include SideBar %>
