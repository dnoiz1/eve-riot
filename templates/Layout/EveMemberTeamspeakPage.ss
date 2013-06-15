<div class="row">
    <% include SideBar %>

    <div class="span8 content" role="main">
        <h1>$Title</h1>
        <% if $CurrentMember.AllowedTeamspeak %>
            $TeamspeakForm
        <% else %>
            You do not meet the current Requirements to access Teamspeak
        <% end_if %>
    </div>
</div>
