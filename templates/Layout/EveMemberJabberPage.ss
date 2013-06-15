<div class="row">
    <% include SideBar %>

    <div class="span8 content" role="main">
        <h1>$Title</h1>
        <% if CurrentMember.AllowedJabber %>
            <p>
                Your username is: <span class="strong">{$CurrentMember.JabberUser}@coalition.evetroll.com</span>
            </p>
            $Content
            <%-- $JabberForm --%>
        <% else %>
            You do not meet the current requirements to access Jabber
        <% end_if %>
    </div>
</div>

