<tr<% if Offline %> class="offline"<% end_if %>>
    <td><img src="http://imagetest.eveonline.com/InventoryType/{$typeID}_32.png" /></td>
    <td><a href="#">$typeName</a></td>
    <td><span class="message <% if CanUse %>good">Yes<% else %>bad">No<% end_if %></span></td>
</tr>
