<?php

class EveStandingsPage extends Page
{
    public function Alliances()
    {
        return EveAlliance::get()->sort(array(
            'Standing' => 'DESC',
            'AllianceName' => 'ASC'
        ));
    }
}
class EveStandingsPage_controller extends Page_controller
{
    function init()
    {
        Requirements::CSS('eacc/thirdparty/datatables/datatables.css');
        Requirements::CustomCSS(<<<CSS
            .table td { vertical-align: middle !important; }
            .table th:first-child { width: 33px !important; }
CSS
        );
        Requirements::JavaScript('eacc/thirdparty/datatables/jquery.dataTables.min.js');
        Requirements::CustomScript(<<<JS
            $(document).ready(function() {
                $('.dataTable').dataTable({
                    "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
                    "bPaginate": false,
                    "aoColumnDefs": [{
                        "bSortable": false,
                        "aTargets": [ 0 ]
                    }]
                });
            });
JS
        );
        return parent::init();
    }
}
