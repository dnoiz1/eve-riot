<div class="content-container typography">
	<article>
		<h1>$Title</h1>
           $Content

           <table class="tborder doctrine-table">
               <thead>
                   <tr>
                       <td class="tcat"></td>
                       <td class="tcat">Hull</td>
                       <!-- td class="tcat">Estimated Price</td //-->
                       <td class="tcat">Reimbursment</td>
                       <td class="tcat">Can Fly?</td>
                   </tr>
                </thead>
                <tbody>
                   <% control Doctrine.EveDoctrineShip %>
                        <tr class="rowlink" rel="$Link">
                            <td><img src="//image.eveonline.com/Render/{$Fitting.ShipID}_128.png" /></td>
                            <td><a href="$Link">$Name.XML: $TechLevel.XML $Fitting.ShipName.XML</a></td>
                            <!-- td>$Price ISK</td //-->
                            <td>$Reimbursment.Nice ISK</td>
                            <td><span class="message <% if Fitting.CanFly %>good">Yes<% else %>bad">No<% end_if %></span></td>
                        </tr>
                   <% end_control %>
                </tbody>
            </table>
	</article>
</div>
<% include SideBar %>
