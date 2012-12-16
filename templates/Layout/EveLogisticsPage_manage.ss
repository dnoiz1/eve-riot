<div class="content-container">	
	<article>
        <div class="feature-right">
            <a class="bbtn" href="{$Link}/orders">My Orders</a>
            <a class="bbtn" href="$Link">Cart</a>
        </div>
        <div class="feature-left">
    		<h1>$Title</h1>
        </div>
		<div class="content">
            $Content
            
            <h3>All Outstanding Orders</h3>

            <table class="tborder orders">
                <thead>
                    <tr>
                        <th colspan="5">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Order #</td>
                        <td class="tcat">Date</td>
                        <td class="tcat">Total Value</td>
                        <td class="tcat">Current Status</td>
                        <td class="tcat">Owner</td>
                    </tr>
                    <% if OutstandingOrders %>
                        <% control OutstandingOrders %>
                            <tr class="rowlink" rel="{$Top.Link}manage/{$ID}">
                                <td>{$Top.ID}{$ID}</td>
                                <td>$Date.Nice</td>
                                <td>$Price</td>
                                <td>$Status</td>
                                <td>$Member.NickName</td>
                            </tr>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="5" class="message">
                                No Outstanding Orders
                            </td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

            <h3>All Compeleted Orders</h3>

            <table class="tborder orders">
                <thead>
                    <tr>
                        <th colspan="5">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Order #</td>
                        <td class="tcat">Date</td>
                        <td class="tcat">Total Value</td>
                        <td class="tcat">Current Status</td>
                        <td class="tcat">Owner</td>
                    </tr>
                    <% if CompletedOrders %>
                        <% control CompletedOrders %>
                            <tr class="rowlink" rel="{$Top.Link}manage/{$ID}">
                                <td>{$Top.ID}{$ID}</td>
                                <td>$Date.Nice</td>
                                <td>$Price</td>
                                <td>$Status</td>
                                <td>$Member.NickName</td>
                            </tr>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="5" class="message">
                                No have any Completed Orders
                            </td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>


        </div>
	</article>
</div>

<% include EveLogisticsSideBar %>
