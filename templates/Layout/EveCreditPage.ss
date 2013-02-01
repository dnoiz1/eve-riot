<div class="content-container">	
	<article>
		<h1>$Title</h1>
		<div class="content">
            $Content

            <h3>Overview</h3>

            <table class="tborder credit">
                <thead>
                    <tr>
                        <th colspan="2">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Provider</td>
                        <td class="tcat">Balance</td>
                    </tr>
                    <% control CreditProviders %>
                        <tr <% if canView %>class="rowlink" rel="{$Top.Link}{$ID}"<% end_if %>>
                            <td>$Name</td>
                            <td>$MemberBalance ISK</td>
                        </tr>
                    <% end_control %>
                </tbody>
            </table>

            <% control CreditProviders %>

            <h3>Transaction History: $Name</h3>

                To deposit ISK to this provider, transfer ISK to the <span class="important">$Type "$Target"</span>

                <table class="tborder credit">
                    <thead>
                        <tr>
                            <th colspan="4">Credit Provider: $Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tcat">Reference ID</td>
                            <td class="tcat">Date</td>
                            <td class="tcat">Character</td>
                            <td class="tcat">Amount</td>
                        </tr>
                        <% if MemberTransactionHistory %>
                            <% control MemberTransactionHistory %>
                                <tr>
                                    <td>$RefID</td>
                                    <td>$Date.Nice</td>
                                    <td>$Character.Name</td>
                                    <td>$Amount ISK</td>
                                </tr>
                            <% end_control %>
                        <% else %>
                            <tr>
                                <td colspan="4">No Transaction History</td>
                            </tr>
                        <% end_if %>
                    </tbody>
                </table>

            <% end_control %>

        </div>
	</article>
</div>

<% include SideBar %>
