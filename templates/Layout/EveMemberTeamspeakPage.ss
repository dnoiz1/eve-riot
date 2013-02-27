<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            <% if CurrentMember.AllowedTeamspeak %>
                <p>
                    Connect to Teamspeak with your name as: <span class="important">{$CurrentMember.NickName}</span>
                </p>
                $Content
                $TeamspeakForm
            <% else %>
                You do not meet the current Requirements to access Teamspeak
            <% end_if %>
        </div>
	</article>
</div>

<% include SideBar %>
