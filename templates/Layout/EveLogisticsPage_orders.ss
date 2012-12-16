<div class="content-container">	
	<article>
        <div class="feature-right">
            <% if canManage %>
                <a class="bbtn" href="{$Link}manage">Manage</a>
            <% end_if %>
            <a class="bbtn" href="$Link">Cart</a>
        </div>
        <div class="feature-left">
    		<h1>$Title</h1>
        </div>
		<div class="content">
            $Content
            
            <h3>Outstanding Orders</h3>

            <table class="tborder orders">
                <thead>
                    <tr>
                        <th colspan="4">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Order #</td>
                        <td class="tcat">Date</td>
                        <td class="tcat">Total Value</td>
                        <td class="tcat">Current Status</td>
                    </tr>
                    <% if OutstandingOrders %>
                        <% control OutstandingOrders %>
                            <tr class="rowlink" rel="{$Top.Link}orders/{$ID}">
                                <td>{$Top.ID}{$ID}</td>
                                <td>$Date.Nice</td>
                                <td>$Price</td>
                                <td>$Status</td>
                            </tr>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="4" class="message">
                                You do not have any Orders in Progress
                            </td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

            <h3>Compeleted Orders</h3>

            <table class="tborder orders">
                <thead>
                    <tr>
                        <th colspan="4">Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tcat">Order #</td>
                        <td class="tcat">Date</td>
                        <td class="tcat">Total Value</td>
                        <td class="tcat">Current Status</td>
                    </tr>
                    <% if CompletedOrders %>
                        <% control CompletedOrders %>
                            <tr class="rowlink" rel="{$Top.Link}orders/{$ID}">
                                <td>{$Top.ID}{$ID}</td>
                                <td>$Date.Nice</td>
                                <td>$Price</td>
                                <td>$Status</td>
                            </tr>
                        <% end_control %>
                    <% else %>
                        <tr>
                            <td colspan="4" class="message">
                                You do not have any Completed Orders
                            </td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>


        </div>
	</article>
</div>

<% include EveLogisticsSideBar %>
