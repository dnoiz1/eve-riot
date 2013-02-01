<div class="content-container">	
	<article>
		<h1>$Title: $CreditProvider.Name</h1>
		<div class="content">
            <h2>Current Balance: $CreditProvider.Balance</h2>
            <h2>Unknown Character Deposit Balance: $CreditProvider.NonMemberBalance</h2>

            <table class="tborder credit">
                <thead>
                    <tr>
                        <th colspan="2">Balances Overview</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Pilot</td>
                        <td class="tcat">Balance</td>
                    </tr>
                    <% control CreditProvider.MemberBalances %>
                        <tr <% if Top.CreditProvider.canView %>rel="{$Top.Link}{$Top.CreditProvider.ID}/{$MemberID}" class="rowlink"<% end_if %>>
                            <td>$Member</td>
                            <td>$Balance</td>
                        </tr>
                    <% end_control %>
                </tbody>
            </table>

        </div>
	</article>
</div>

<% include SideBar %>
