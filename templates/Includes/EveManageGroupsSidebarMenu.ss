<div class="well sidebar-nav">
	<ul class="nav nav-list">
	    <li class="nav-header">
	    <% loop $Level(1) %>
	        <a href="$Link" title="$Title.XML">$MenuTitle.XML</a>
	    <% end_loop %>
	    </li>
		<% loop $Menu(2) %>
		<li class="$LinkingMode">
	        <li<% if $LinkOrCurrent = current %> class="active"<% end_if %>><a href="$Link" title="$Title.XML">$MenuTitle.XML <% if $PendingApplications.Count %><span class="badge badge-important">$PendingApplications.Count</span><% end_if %></a></li>
			<% if $Children %>
				<ul>
				<% loop $Children %>
	        		<li<% if $LinkOrCurrent = current %> class="active"<% end_if %>><a href="$Link" title="$Title.XML">$MenuTitle.XML</a></li>
				<% end_loop %>
				</ul>
			<% end_if %>
		</li>
		<% end_loop %>
	</ul>
</div>
