<div class="content-container typography">
	<article>
		<h1>$Title</h1>
		<div class="content">
           $Content

            <% control Doctrines %>
                <a href="$Link"><h2>$Title.XML</h2></a>

                $Description.FirstParagraph(0) 

                <a href="$Link">Read More</a>

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
                        <% control EveDoctrineShipsAugmented %>
                        <tr class="rowlink" rel="$Link">
                            <td><img src="http://image.eveonline.com/Render/{$Fitting.ShipID}_128.png" /></td>
                            <td><a href="$Link">$Fitting.ShipName $TechLevel</a></td>
                            <!-- td>$Price ISK</td //-->
                            <td>$Reimbursment.Nice ISK</td>
                            <td><span class="message <% if Fitting.CanFly %>good">Yes<% else %>bad">No<% end_if %></span></td>
                        </tr>
                        <% end_control %>
                    </tbody>
                </table>
                <br />
            <% end_control %>
        </div>
	</article>
</div>
<% include SideBar %>
