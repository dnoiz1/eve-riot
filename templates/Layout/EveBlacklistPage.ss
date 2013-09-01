<div class="row">
    <div class="span<% if $Children %>9<% else %>12<% end_if %> pull-right">
        <h1>$Title</h1>
    </div>
</div>

<div class="row">
    <% if $Children %>
        <% include EveManageGroupsSideBar %>
        <div class="span9 content" role="main">
    <% else %>
        <div class="span12 content" role="main">
    <% end_if %>

        $Content

        <table class="table data-table">
            <thead>
                <th>Type</th>
                <th>Value</th>
                <th>Source</th>
                <th>Reason</th>
                <th>Added</th>
                <th>By</th>
            </thead>
            <tbody>
                <% if $BlackList %>
                    <% loop $BlackList %>
                        <tr>
                            <td>$Type</td>
                            <td>$Value</td>
                            <td>$Source</td>
                            <td>$Reason</td>
                            <td>$Added.Nice</td>
                            <td>$AddedBy.FirstName</td>
                        </tr>
                    <% end_loop %>
                <% else %>
                    <tr>
                        <td colspan="6"><em>No Pilots currently on the Blacklist</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>


    </div>
</div>
