<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <table class="full tborder memberlist">
                <thead>
                    <tr>
                        <th colspan="8">Memberlist</th>
                    </tr>
                </thead>
                <tbody>
                    <% if MembersInCorp %>
                        <% control MembersInCorp %>
                            <tr>
                                <td class="tcat">$NickName</td>
                                <td class="tcat"></td>
                            </tr>
                            <% if EveMemberCharacterCache %>
                                <% control EveMemberCharacterCache %>
                                    <tr>
                                        <td>$CharacterName</td>
                                        <td>$TimerEnds.Format(H:i d/m/Y)</td>
                                    </tr>
                                <% end_control %>
                            <% end_if %>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="8"><em>No Upcoming Timers</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

        </div>
	</article>
</div>

<% include SideBar %>
