<div class="content-container">	
	<article>
		<h1>$Title: Transation History for $Member.NickName.XML</h1>
		<div class="content">
            <h3>Credit Provider: $CreditProvider.Name.XML</h3>
            <h3>Current Balance: $MemberBalance ISK</h3>

             <table class="tborder credit">
                    <thead>
                        <tr>
                            <th colspan="4">Credit Provider: $CreditProvider.Name.XML</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tcat">Reference ID</td>
                            <td class="tcat">Date</td>
                            <td class="tcat">Character</td>
                            <td class="tcat">Amount</td>
                        </tr>
                        <% if TransactionHistory %>
                            <% control TransactionHistory %>
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

        </div>
	</article>
</div>

<% include SideBar %>
