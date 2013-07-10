<div class="row">
    <div class="span12 content" role="main">
        <h1>$Title</h1>

        <% if CurrentMember.AllowedJabber %>
            $Content
            $JabberPingForm
        <% else %>
            You do not meet the current requirements to access Jabber
        <% end_if %>

    </div>

</div>
