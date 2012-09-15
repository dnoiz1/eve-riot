<p>If anything is missing below, you might have an old XML file or using an old version of EFT.<br />
Your fitting XML must contain up to date module names.</p>
<pre>
[ $EveFitting.shipName ]
High Slots
<% control EveFitting.HighSlots %>$typeName
<% end_control %>
Med Slots
<% control EveFitting.MedSlots %>$typeName
<% end_control %>
Low Slots
<% control EveFitting.LowSlots %>$typeName
<% end_control %>
Rigs
<% control EveFitting.RigSlots %>$typeName
<% end_control %>
<% if EveFitting.SubSystems %>Subsystems<% control EveFitting.SubSystems %>$typeName
<% end_control %>
<% end_if %>
</pre>
