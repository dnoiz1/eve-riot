<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <p>
                <label>Active Groups:</label>

                <% if CurrentMember.Groups %>
                    <% control CurrentMember.Groups %>
                        $Title<% if last %><% else %>, <% end_if %>
                    <% end_control %>
                <% end_if %>
            </p>
           <table class="full tborder">
                <thead>
                    <th>Key ID</th>
                    <th>vCode</th>
                    <th>Valid?</th>
                    <th></th>
                </thead>
                <tbody>
                    <% if ApiKeys %>
                        <% control ApiKeys %>
                            <tr>
                                <td>$KeyID</td>
                                <td>$vCode</td>
                                <td><% if isValid = 1 %><div class="message good">Yes</div><% else %><% control isValid %><div class="message bad">$Reason</div><% end_control %><% end_if %></td>
                                <td class="action"><a href="/profile/api-keys/delete/$ID"><img src="/cms/images/delete.gif" /></a></td>
                            </tr>
                        <% end_control %>
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
