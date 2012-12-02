<div class="content-container">	
	<article>
        <div class="feature-right">
            <a class="bbtn">Past Orders</a>
        </div>
        <div class="feature-left">
    		<h1>$Title</h1>
        </div>
		<div class="content">
            $Content

           <% control OrderForm %>

                <form $FormAttributes>

                    <fieldset>
                        $dataFieldByName(Item).FieldHolder
                        $dataFieldByName(Qty).FieldHolder

                        <div class="field literal">
                            <label class="left" for="Form_EveLogisticsForm_Add">Add To Order</label>
                            <div class="middleColumn">
                                <a id="AddToOrder" class="bbtn" href="javascript:void(0)">+</a>

                                <span id="PriceCheck">
                                    <span>
                                        <span class="qty"></span>x <span class="item-name"></span> @ <span class="price-per-unit"></span> ea. <span class="price-total"></span>
                                    </span>
                                </span>
                            </div>
                        </div>
 
                        <table id="OrderTable" class="full tborder">
                            <thead>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Price Per Unit</th>
                                <th>m<sup>3</sup> Per Unit</th>
                                <th></th>
                            </thead>
                            <tbody>
                                <tr class="no-data">
                                    <td colspan="5" style="text-align: center">Lookup items above to start adding to this order</td>
                                </tr>
                            </tbody>
                        </table>

                        $dataFieldByName(SecurityID)
                    </fieldset>

                    <% if Actions %>
                        <div class="Actions">
                            <input type="button" id="ClearOrder" class="bbtn" value="Clear Order">
                            <% control Actions %>$Field<% end_control %>
                        </div>
                    <% end_if %>

                </form>
            <% end_control %>
        </div>
	</article>
</div>

<% include SideBar %>
