<div class="row">
    <div class="span<% if $Children %>9<% else %>12<% end_if %> pull-right">
        <h1>$Title</h1>
    </div>
</div>

<div class="row">
    <% if $Children %>
        <% include EveManageGroupsSideBar %>
        <div class="span9 content" role="main">
    <% else %>
        <div class="span12 content" role="main">
    <% end_if %>
        $Content

        <% if $Message %>
            <% with $Message %>
                <div class="alert alert-{$Style}">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <% if $Title %>
                        <strong>$Title</strong>
                    <% end_if %>
                    <% if $Message %>
                        $Message
                    <% end_if %>
                </div>
            <% end_with %>
        <% end_if %>

        <div class="well">
            <h1 class="text-center">Your standing is $CurrentMember.Standing</h1>
        </div>
        <em class="pull-right">This is derived from the characters on your API keys</em>

        <div class="clearfix"><!-- //--></div>

        <p>
            <% with $CurrentMember %>
                <h3>Active Groups</h3>

                <table class="table">
                    <thead>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Min Standing</th>
                        <th></th>
                    </thead>
                    <tbody>
                        <% if $Groups %>
                            <% loop $Groups %>
                                <tr>
                                    <td>
                                        <% if $EveManagedGroup.canManage %>
                                            <a href="$Top.Link(/manage/){$EveManagedGroup.ID}">
                                                $Title
                                                <% if $EveManagedGroup.PendingApplications.Count %>
                                                    <span class="badge badge-important">$EveManagedGroup.PendingApplications.Count</span>
                                                <% end_if %>
                                            </a>
                                        <% else %>
                                            $Title
                                        <% end_if %>
                                    </td>
                                    <td>$EveManagedGroup.Description</td>
                                    <td>$EveManagedGroup.MinStanding</td>
                                    <td>
                                        <% if $EveManagedGroup %>
                                            <a href="javascript:void(0)" class="btn btn-danger pull-right btn-mini group-join-part" data-group-id="$EveManagedGroup.ID" data-group-title="$Title.XML" data-group-action="part">&times;</a>
                                        <% end_if %>
                                    </td>
                                </tr>
                            <% end_loop %>
                        <% else %>
                            <tr>
                                <td colspan="4"><em>No Active Groups</em></td>
                            </tr>
                        <% end_if %>
                    </tbody>
                </table>

            <% end_with %>
        </p>

        <p>
            <h3>Pending Applications</h3>

            <table class="table">
                <thead>
                    <th>Group</th>
                    <th>Description</th>
                    <th>Min Standing</th>
                    <th>Application Date</th>
                </thead>
                <tbody>
                    <% if $PendingApplications %>
                       <% loop $PendingApplications %>
                            <tr>
                                <td>$EveManagedGroup.Title</td>
                                <td>$EveManagedGroup.Description</td>
                                <td>$EveManagedGroup.MinStanding</td>
                                <td>$Created.Nice</td>
                                <td>
                                    <a href="javascript:void(0)" class="btn btn-danger btn-mini pull-right group-join-part" data-group-action="withdraw" data-group-id="$ID" data-group-title="$EveManagedGroup.Title.XML">&times;</a>
                                </td>
                            </tr>
                        <% end_loop %>
                    <% else %>
                        <tr>
                            <td colspan="4"><em>No Pending Applications</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>

            <% if CompletedApplications %>
                <div class="row">
                     <a href="javascript:void(0)" id="ToggleCompletedApplications" class="btn pull-right">See Previous Applications</a>
                </div>

                <div id="CompletedApplications" class="hide">

                    <table class="table data-table">
                        <thead>
                            <th>Group</th>
                            <th>Description</th>
                            <th>Min Standing</th>
                            <th>Submitted</th>
                            <th>Completed</th>
                            <th>Approved?</th>
                        </thead>
                        <tbody>
                           <% loop $CompletedApplications %>
                                <tr>
                                    <td>$EveManagedGroup.Title</td>
                                    <td>$EveManagedGroup.Description</td>
                                    <td>$EveManagedGroup.MinStanding</td>
                                    <td>$Created.Nice</td>
                                    <td>$CompletedDate.Nice</td>
                                    <td>
                                        <% if $Approved %>Yes<% else %>No<% end_if %>
                                    </td>
                                </tr>
                            <% end_loop %>
                        </tbody>
                    </table>

                </div>
            <% end_if %>
        </p>

        <div class="clearfix"><!-- //--></div>

        <p>
            <h3>Available Groups</h3>
            <table class="table">
                <thead>
                    <th>Group</th>
                    <th>Description</th>
                    <th>Min Standing</th>
                    <th></th>
                </thead>
                <tbody>
                    <% if $AvailableGroups %>
                        <% loop $AvailableGroups %>
                            <tr>
                                <td>$Title</td>
                                <td>$Description</td>
                                <td>$MinStanding</td>
                                <td>
                                    <% if $RequiresApproval %>
                                        <a href="javascript:void(0)" data-group-title="$Title.XML" data-group-description="$Description.XML" data-group-id="$ID" class="btn pull-right btn-mini group-application">Apply</a>
                                    <% else %>
                                        <a href="javascript:void(0)" data-group-action="join" data-group-title="$Title.XML" data-group-id="$ID" class="btn btn-mini pull-right group-join-part">Join</a>
                                    <% end_if %>
                                </td>
                            </tr>
                        <% end_loop %>
                    <% else %>
                        <tr>
                            <td colspan="4"><em>You are not elegible to join any groups</em></td>
                        </tr>
                    <% end_if %>
                </tbody>
            </table>
        </p>

    <% with $ApplicationForm %>
        <div id="ApplicationFormModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ApplicationFormModalLabel" aria-hidden="true">
            <form $AttributesHTML>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 id="ApplicationFormModalLabel">Application Form</h3>
                </div>
                <div class="modal-body">
                    <fieldset>
                        <% loop $Fields %>
                            $FieldHolder
                        <% end_loop %>
                        <div class="clearfix"><!-- //--></div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    <% loop $Actions %>
                        $Field
                    <% end_loop %>
                </div>
            </form>
        </div>
    <% end_with %>

    <% with $JoinPartForm %>
        <form $AttributesHTML>
            <fieldset>
                <% loop $Fields %>
                    $Field
                <% end_loop %>
            </fieldset>
            <% loop $Actions %>
                $Field
            <% end_loop %>
        </form>
    <% end_with %>
</div>
