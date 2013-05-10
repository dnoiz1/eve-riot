<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            <% if CurrentMember.AllowedJabber %>
                <p>
                    Your username is: <span class="important">{$CurrentMember.JabberUser}@coalition.evetroll.com</span>
                </p>
                $Content
                <%-- $JabberForm --%>
            <% else %>
                You do not meet the current requirements to access Jabber
            <% end_if %>
        </div>
	</article>
</div>

<% include SideBar %>
