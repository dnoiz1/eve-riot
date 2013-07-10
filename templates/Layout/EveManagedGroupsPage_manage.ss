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


    <p>
        <h3>Pending Applications</h3>

        <table class="table data-table">
            <thead>
                <th>Name</th>
                <th>Alliance</th>
                <th>Corp</th>
                <th>Standing</th>
                <th>Note</th>
                <th>Submitted</th>
                <th></th>
            </thead>
            <tbody>
                <% if $Group.PendingApplications %>
                   <% loop $Group.PendingApplications %>
                        <tr>
                            <td>$Member.FirstName</td>
                            <td>$Member.AllianceTicker</td>
                            <td>$Member.Ticker</td>
                            <td>$Member.Standing</td>
                            <td>$Message</td>
                            <td>$Created.Nice</td>
                            <td>
                                <span class="pull-right">
                                   <a href="javascript:void(0)" class="btn btn-primary btn-mini group-approve-deny" data-group-action="approve" data-member-name="$Member.FirstName.XML" data-member-id="$MemberID" data-group-id="$Up.ID" data-group-title="$Up.Title.XML">Approve</a>
                                   <a href="javascript:void(0)" class="btn btn-danger btn-mini group-approve-deny" data-group-action="deny" data-member-name="$Member.FirstName.XML" data-member-id="$MemberID" data-group-id="$Up.ID" data-group-title="$Up.Title.XML">Deny</a>
                                </span>
                            </td>
                        </tr>
                    <% end_loop %>
                 <% else %>
                    <tr>
                        <td colspan="7"><em>No Pending Applications</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>
    </p>

    <p>
        <h3>Active Members</h3>

        <table class="table data-table">
            <thead>
                <th>Name</th>
                <th>Alliance</th>
                <th>Corp</th>
                <th>Standing</th>    
                <th></th>
            </thead>
            <tbody>
                <% if $Group.Group.Members %>
                   <% loop $Group.Group.Members %>
                        <tr>
                            <td>$FirstName</td>
                            <td>$AllianceTicker</td>
                            <td>$Ticker</td>
                            <td>$Standing</td>
                            <td>
                               <a href="javascript:void(0)" class="btn btn-danger btn-mini pull-right group-approve-deny" data-group-action="kick" data-member-name="$FirstName.XML" data-member-id="$ID" data-group-id="$Up.Up.ID" data-group-title="$Up.Title.XML">&times;</a>
                            </td>
                        </tr>
                    <% end_loop %>
                 <% else %>
                    <tr>
                        <td colspan="5"><em>No Active Members</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>
    </p>

    <p>
        <h3>Standing Members</h3>
        <em>These are members who have been approved, but do not currently meet the minimum requirements to join the group</em>

        <table class="table data-table">
            <thead>
                <th>Name</th>
                <th>Alliance</th>
                <th>Corp</th>
                <th>Standing</th>    
                <th></th>
            </thead>
            <tbody>
                <% if $Group.StandingMembers %>
                   <% loop $Group.StandingMembers %>
                        <tr>
                            <td>$FirstName</td>
                            <td>$AllianceTicker</td>
                            <td>$Ticker</td>
                            <td>$Standing</td>
                            <td>
                                <a href="javascript:void(0)" class="btn btn-danger btn-mini pull-right group-approve-deny" data-group-action="deny-kick" data-member-name="$FirstName.XML" data-member-id="$ID" data-group-id="$Up.ID" data-group-title="$Up.Title.XML">&times;</a>
                            </td>
                        </tr>
                    <% end_loop %>
                 <% else %>
                    <tr>
                        <td colspan="5"><em>No Standing Members</em></td>
                    </tr>
                <% end_if %>
            </tbody>
        </table>
    </p>

    <div class="hidden">
        $ManageForm
    </div>
</div>
