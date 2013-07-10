<div class="row">
    <div class="span9 pull-right">
        <h1>$Title</h1>
    </div>
</div>

<div class="row">
    <% include SideBar %>
    <div class="span9 content" role="main">
        <% if $CurrentMember.AllowedTeamspeak %>
            $TeamspeakForm
        <% else %>
            You do not meet the current Requirements to access Teamspeak
        <% end_if %>
    </div>
</div>
