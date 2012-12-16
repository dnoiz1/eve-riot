<div class="content-container">	
	<article>
        <div class="feature-right">
            <% if canManage %>
                <a class="bbtn" href="{$Link}manage">Manage</a>
            <% end_if %>
            <a class="bbtn" href="{$Link}orders">Orders</a>
            <a class="bbtn" href="$Link">Cart</a>
        </div>
        <div class="feature-left">
    		<h1>$Title</h1>
        </div>
		<div class="content">
            $Content
            
            <% control Order %>

                <h3>
                    <% if Top.canManage %>
                        <% if Top.StatusForm %>
                            $Top.StatusForm
                        <% else %>
                            Status: $Status
                        <% end_if %>
                    <% else %>
                    Status: $Status
                    <% end_if %>
                </h3>
                <p>
                    Total:  $Price ISK <br />
                    Shipping to: $Member.NickName
                </p>

                <table class="tborder orders">
                    <thead>
                        <tr>
                            <th colspan="4">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tcat">Item #</td>
                            <td class="tcat">Qty</td>
                            <td class="tcat">Name</td>
                            <td class="tcat">Price</td>
                        </tr>
                        <% if EveLogisticsItems %>
                            <% control EveLogisticsItems %>
                                <tr>
                                    <td>$invTypesID</td>
                                    <td>$Qty</td>
                                    <td>$Name</td>
                                    <td>$Price ISK</td>
                                </tr>
                            <% end_control %>
                        <% else %>
                            <tr>
                                <td colspan="4" class="message">
                                    This Order doesnt have any Items! Something is probably wrong.
                                </td>
                            </tr>
                        <% end_if %>
                    </tbody>
                </table>

                <h3>Order History</h3>

                <table class="tborder orders">
                    <thead>
                        <tr>
                            <th colspan="4">Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tcat">Event #</td>
                            <td class="tcat">Date</td>
                            <td class="tcat">Status</td>
                            <td class="tcat">Updated By</td>
                        </tr>
                        <% if EveLogisticsOrderHistory %>
                            <% control EveLogisticsOrderHistory %>
                                <tr>
                                    <td>$ID</td>
                                    <td>$Created.Nice</td>
                                    <td>$Status</td>
                                    <td>$Member.NickName</td>
                                </tr>
                            <% end_control %>
                        <% else %>
                            <tr>
                                <td colspan="4" class="message">
                                    This Order doesnt have any History! Something is probably wrong.
                                </td>
                            </tr>
                        <% end_if %>
                    </tbody>
                </table>

            <% end_control %>

        </div>
	</article>
</div>

<% include EveLogisticsSideBar %>
