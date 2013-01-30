<div class="content-container character-sheet">
	<article>
        <div class="page-header">
            <div class="character-selector">
                <form>
                    $CharacterSelector
                </form>
            </div>
            <div class="featureLeft">
        		<h1>$Title</h1>
            </div>
        </div>
		<div class="content">
            $Content

            <div class="portraits">
                <img class="pilot-image" src="//image.eveonline.com/character/{$Character.ID}_256.jpg">
                <img class="corp-image" src="//image.eveonline.com/corporation/{$Character.CorporationID}_128.png">
                <% if AllianceID = 0 %><% else %>
                <img class="alliance-image" src="//image.eveonline.com/alliance/{$Character.AllianceID}_128.png">
                <% end_if %>
            </div>

            <div class="clear"><!-- //--></div>

            <table class="tborder">
                <tbody>
                    <tr>
                        <td class="tcat" colspan="2">General</td>
                    </tr>
                    <tr>
                        <td>Skill Points</td>
                        <td>$Character.SkillPoints.Formatted ($Character.CloneName $Character.CloneSP.Formatted kept)</td>
                    </tr>
                    <tr>
                        <td>Gender</td>
                        <td>$Character.Gender</td>
                    </tr>
                    <tr>
                        <td>Background</td>
                        <td>$Character.Race - $Character.BloodLine - $Character.Ancestry</td>
                    </tr>                   
                    <tr>
                        <td>Corporation</td>
                        <td>$Character.Corporation</td>
                    </tr>
                    <tr>
                        <td>Alliance</td>
                        <td>$Character.Alliance</td>
                    </tr>
                    <tr>
                        <td>DoB</td>
                        <td>$Character.DoB.Full ($Character.DoB.TimeDiff old)</td>
                    </tr>
                    <tr>
                        <td>Security Status</td>
                        <td>$Character.SecStatus</td>
                    </tr>
                </tbody>
            </table>

            <table class="tborder">
                <tbody>
                    <tr>
                        <td colspan="4" class="tcat">Attributes</td>
                    </tr>
                    <tr>
                        <td>Intelligence</td>
                        <td>$Character.Intelligence <% if Character.IntelligenceAugmentorName %>(<a href="#" title="$Character.IntelligenceAugmentorName">+$Character.IntelligenceAugmentorBonus</a>)<% end_if %></td>
                    </tr>
                    <tr>
                        <td>Perception</td>
                        <td>$Character.Perception <% if Character.PerceptionAugmentorName %>(<a href="#" title="$Character.PerceptionAugmentorName">+$Character.PerceptionAugmentorBonus</a>)<% end_if %></td>
                    </tr>
                    <tr>
                        <td>Charisma</td>
                        <td>$Character.Charisma <% if Character.CharismaAugmentorName %>(<a href="#" title="$Character.CharismaAugmentorName">+$Character.CharismaAugmentorBonus</a>)<% end_if %></td>
                    </tr>
                    <tr>
                        <td>Willpower</td>
                        <td>$Character.Willpower <% if Character.WillpowerAugmentorName %>(<a href="#" title="$Character.WillpowerAugmentorName">+$Character.WillpowerAugmentorBonus</a>)<% end_if %></td>
                    </tr>
                    <tr>
                        <td>Memory</td>
                        <td>$Character.Memory <% if Character.MemoryAugmentorName %>(<a href="#" title="$Character.MemoryAugmentorName">+$Character.MemoryAugmentorBonus</a>)<% end_if %></td>
                    </tr>
                </tbody>
            </table>

            <div class="controls">
                <label>Hide skills that have not been trained</label>
                <input type="checkbox" id="HideUntrained" checked="checked" />
            </div>

            <table class="tborder skill-tree">
                <thead>
                    <tr>
                        <th colspan="2">Skills</th>
                    </tr>
                </thead>
                <tbody>
                    <% control Character.SkillsInGroups %>
                    <tr>
                        <td class="tcat">$groupName</td>
                        <td class="tcat">$SkillPoints</td>
                    </tr>
                        <% if invTypes %>
                            <% control invTypes %>
                                 <tr class="skill <% if SkillPoints %><% else %>not-trained<% end_if %>">
                                    <td>$typeName</td>
                                    <td class="skilllevel"><img src="$ThemeDir/images/level<% if Level %>$Level<% else %>0<% end_if %>.gif" /></td>
                                </tr>
                            <% end_control %>
                        <% end_if %>
                    <% end_control %>
                </tbody>
            </table>

        </div>
    </article>
</div>



<% include SideBar %>
