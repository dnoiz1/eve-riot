<?php

class EveBlacklistPage extends Page {}
class EveBlacklistPage_controller extends Page_controller
{
    public function Blacklist()
    {
        return EveBlacklist::get();
    }

    public function init()
    {
        parent::init();
        if($this->Children()->Count() > 0) {
            $left_grid  = 4;
            $right_grid = 5;
        } else {
            $left_grid  = 6;
            $right_grid = 6;
        }

        Requirements::CSS('eacc/thirdparty/datatables/datatables.css');
        Requirements::JavaScript('eacc/thirdparty/datatables/jquery.dataTables.min.js');
        Requirements::CustomScript(<<<JS
            $('.data-table').each(function(i,e){
                if($(e).find('tr td').length > 1) {
                    $(e).dataTable({
                        "sDom": "<'row'<'span{$left_grid}'><'span{$right_grid}'f>r>t<'row'<'span{$left_grid}'i><'span{$right_grid}'p>>",
                        "sWrapper": "dataTables_wrapper form-inline",
                        "aaSorting": []
                    });
                }
            });

JS
        );
    }
}
