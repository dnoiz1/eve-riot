<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <p>
                <label>Active Groups:</label>

                <% if $CurrentMember.Groups %>
                    <% loop $CurrentMember.Groups %>
                        $Title<% if last %><% else %>, <% end_if %>
                    <% end_loop %>
                <% end_if %>
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
                                <td><% if $isValid = 1 %><div class="message good">Yes</div><% else %><% loop $APIErrors %><div class="message bad">$Reason</div><% end_loop %><% end_if %></td>
                                <td class="action"><a href="/profile/api-keys/delete/$ID"><img src="/cms/images/delete.gif" /></a></td>
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

            <a href="javascript:void(0)" class="btn" id="showApiForm">Add API Key</a>

        </div>
	</article>
</div>

<% include SideBar %>
