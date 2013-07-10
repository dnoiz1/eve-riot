
<% if $Menu(2) %>
    <div class="span3">
     	<% include EveManageGroupsSidebarMenu %>

        <% if $Group.canManage && $Group.Managers.Members %>
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    <li class="nav-header">Managed By: $Group.Managers.Title</li>
                    <% loop $Group.Managers.Members %>
                        <li>
                            <a href="#">
                                <img class="portrait" src="//image.eveonline.com/character/{$CharacterID}_32.jpg">
                                $FirstName
                            </a>
                        </li>
                    <% end_loop %>
                </ul>
            </div>
        <% end_if %>

    </div>
<% end_if %>

