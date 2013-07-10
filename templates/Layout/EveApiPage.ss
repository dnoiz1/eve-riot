<div class="row">
    <div class="span9 pull-right">
        <h1>$Title</h1>
    </div>
</div>

<div class="row">
    <% include SideBar %>

    <div class="span9 content" role="main">
        $Content
        <p>
            <strong>Active Groups:</strong>

            <% if $Groups %>
                <% loop $Groups %>
                    $Title<% if last %><% else %>, <% end_if %>
                <% end_loop %>
            <% end_if %>
        </p>
        <p>
            <strong>Coalition Standing:</strong> $CurrentMember.Standing
        </p>
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <th>Key ID</th>
                <th>vCode</th>
                <th>Valid?</th>
                <th></th>
            </thead>
            <tbody>
                <% if $ApiKeys %>
                    <% loop $ApiKeys %>
                        <tr>
                            <td>$KeyID</td>
                            <td>$vCode</td>
                            <td><% if $isValid = 1 %><div class="text-success">Yes</div><% else %><% loop $APIErrors %><div class="text-error">$Reason</div><% end_loop %><% end_if %></td>
                            <td class="action"><a class="btn btn-mini btn-danger pull-right" href="/profile/api-keys/delete/$ID">delete</a></td>
                        </tr>
                    <% end_loop %>
                <% else %>
                    <tr>
                        <td colspan="4" style="text-align: center">You have not added any API Keys</td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>

         <div id="APIFormWrapper" style="display:none; margin-top: 25px;">
            $APIForm
        </div>

        <a href="javascript:void(0)" class="btn btn-primary pull-right" id="showApiForm">Add API Key</a>
    </div>
</div>

