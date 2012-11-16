<div class="content-container typography">
	<article>
		<h1>$Name.XML: $TechLevel.XML $Fitting.ShipName, $Doctrine.Title.XML</h1>
         <p>
             $Description
         </p>
         <table class="tborder fitting-table">
            <thead>
                    <tr>
                    <th colspan="2">Module</th>
                    <th>Can Use?</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th class="tcat" colspan="3">High Power</th>
                </tr>
                <% control Fitting.HighSlots %>
                    <% include EveFittingRow %>
                <% end_control %>
                <tr>
                    <th class="tcat" colspan="3">Medium Power</th>
                </tr>
                <% control Fitting.MedSlots %>
                    <% include EveFittingRow %>
                <% end_control %>
                <tr>
                    <th class="tcat" colspan="3">Low Power</th>
                </tr>
                <% control Fitting.LowSlots %>
                    <% include EveFittingRow %>
                <% end_control %>
                <tr>
                    <th class="tcat" colspan="3">Rigs</th>
                </tr>
                <% control Fitting.RigSlots %>
                    <% include EveFittingRow %>
                <% end_control %>
                <% if Fitting.Subsystems %>
                    <tr>
                        <th class="tcat" colspan="3">Subsystems</th>
                    </tr>
                    <% control Fitting.SubSystems %>
                        <% include EveFittingRow %>
                    <% end_control %>
                <% end_if %>
                <% if Fitting.Drones %>
                    <tr>
                        <th class="tcat" colspan="3">Drones</th>
                    </tr>
                    <% control Fitting.Drones %>
                        <% include EveFittingRow %>
                    <% end_control %>
                <% end_if %>
            </tbody>
        </table>

    
        <h3>EFT Text Block</h3>
        <blockquote id="EFTTextBlock"><br />$EFTTextBlock</blockquote>

	</article>
</div>
<% include SideBar %>
