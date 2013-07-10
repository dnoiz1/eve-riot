<div class="row">
    <div class="span9 pull-right">
        <h1>$Title</h1>
    </div>
</div>

<div class="row">
    <% include SideBar %>
    <div class="span9 content" role="main">
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

