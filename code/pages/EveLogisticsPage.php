<?php

class EveLogisticsPage extends Page
{
    static $db = array(
        'PriceCheckSolarSystemID' => 'Int',
        'Markup'                  => 'Int',
        'IskPerM3'                => 'Int'
    );

    static $has_one = array(
        'EveCreditProvider' => 'EveCreditProvider'
    );

    public function getCMSFields()
    {
        $f = parent::getCMSFields();

        $f->findOrMakeTab('Root.Content.Logistics', 'Logistics Configuration');
        $f->addFieldsToTab('Root.Content.Logistics',
            new FieldSet(
                new EveSolarSystemAutoSuggestField('PriceCheckSolarSystemID', 'Price Check System'),
                new NumericField('Markup', 'Mark Up (%)'),
                new NumericField('IskPerM3', 'ISK Per'),
                new DropDownField('EveCreditProviderID', 'Credit Provider', EveCreditProvider::get('EveCreditProvider')->map('ID', 'Name'))
            )
        );

        return $f;
    }

    function canEdit()
    {
        return Permission::check('EVE_LOGISTICS_CONFIGURE');
    }

    function canManage()
    {
        return Permission::check('EVE_LOGISTICS_ADMIN');
    }
}

class EveLogisticsPage_controller extends Page_controller
{
    public function init()
    {
        Requirements::JavaScript('mysite/javascript/jquery.confirm.js');
        return parent::init();
    }

    public function EveLogisticsForm()
    {
        $freight_fee = $this->IskPerM3;
        $current_credit = ($this->EveCreditProvider()) ? $this->EveCreditProvider()->MemberBalance() : 0;

        $clear_order = (Session::get('clear_order_' . $this->ID)) ? 'true' : 'false';
        Session::clear('clear_order_' . $this-ID);

        /* need to make local storage key unique to page id
         * will do it later, will make it more effort for other pages
         * to add to cart, so lazy for now
         */

        Requirements::CustomScript(<<<JS
            var clear_order = {$clear_order};

            var ls_order_items = localStorage.getItem('order_items');
            if(ls_order_items) {
                var order_items = JSON.parse(ls_order_items);
            } else {
                var order_items = [];
            }

            var freight_fee = {$freight_fee};
            var current_credit = {$current_credit};
            var order_total = 0;
            var item_search_text;
            var doSubmit = false;

            if(clear_order) {
                order_items = [];
            }

            var iskFormat = function(num) {
                return (num.toFixed(2)+'').replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            },

            price_check_loadQ = 0,
            price_check_loading = function(s) {
                if(typeof s == 'undefined') s = true;
                var l = jQuery('#PriceCheck');
                if(s === true) {
                    q = price_check_loadQ++;
                    price_check_loadQ = q;
                    l.addClass('loading');
                    return q;
                } else {
                    price_check_loadQ = (price_check_loadQ > 0) ? price_check_loadQ-- : 0;
                    if (price_check_loadQ == 0) {
                        l.removeClass('loading');
                        return true;
                    }
                }
                return false;
            },

            current_item = false,

            invTypeCallback = function(o) {
                jQuery('#Form_EveLogisticsForm_Item_text').attr('disabled', 'disabled');
                jQuery('#Form_EveLogisticsForm_Item').val(o.id);
                price_check_loading(true);
                jQuery.ajax({
                    url: window.location.pathname + 'priceCheck/' + o.id,
                    success: function(data) {
                        price_check_loading(false);
                        if(data.id == o.id) {
                            data.volume = parseFloat(data.volume);
                            qty = parseInt(jQuery('#Form_EveLogisticsForm_Qty').val());
                            jQuery('#PriceCheck span.qty').html(qty);
                            jQuery('#PriceCheck span.item-name').html(data.name);
                            jQuery('#PriceCheck span.price-per-unit').html(iskFormat(data.price));

                            if(data.price == 0) {
                                jQuery('#PriceCheck span.price-total').html('None Available');
                                current_item = false;
                            } else {
                                price_total = iskFormat(qty * data.price);
                                jQuery('#PriceCheck span.price-total').html(price_total);
                                current_item = data;
                            }
                            jQuery('#Form_EveLogisticsForm_Item_text').removeAttr('disabled');
                            if(current_item || data.price == 0) {
                                jQuery('#PriceCheck > span').show();
                            } else {
                                jQuery('#PriceCheck > span').hide();
                            }
                        }
                    },
                    error: function(){ price_check_loading(false); jQuery('#Form_EveLogisticsForm_Item_text').removeAttr('disabled'); alert('failed, sorry, try again'); },
                    dataType: 'json'
                });
            },

            updateOrderPrices = function() {
                jQuery(order_items).each(function(i,e){
                    price_check_loading(true);
                    jQuery.ajax({
                        url: window.location.pathname + 'priceCheck/' + e.id,
                        success: function(data) {
                            if(order_items[i].id == e.id) {
                                data.volume += 0;
                                data.qty = e.qty;
                                order_items[i] = data;
                            }
                        },
                        complete: function() { price_check_loading(false); },
                        dataType: 'json',
                        aync: false
                    });
                });
            },

            updateOrderTable = function() {
                if(order_items.length == 0) {
                    jQuery('#OrderTable .no-data').fadeIn();
                } else {
                    jQuery('#OrderTable .no-data').hide();
                }
                jQuery('#OrderTable tbody tr.item').remove();

                total_volume = 0;
                total_cost   = 0;
                freight_cost = 0;

                jQuery(order_items).each(function(i,e){
                    total_volume += e.volume * e.qty;
                    total_cost += (e.qty * e.price);
                    row = '<tr class="item">' +
                        '<td>' + e.qty + '</td>' +
                        '<td>' + e.name + '</td>' +
                        '<td>' + iskFormat(e.price) + '</td>' +
                        '<td>' +  e.volume + '</td>' +
                        '<td class="action"><a href="javascript:removeFromOrder(' + i + ')"><img src="/cms/images/delete.gif"></a></td>' +
                    '</tr>';
                    if(jQuery('#OrderTable tr.item:last').length !== 0) {
                        jQuery('#OrderTable tr.item:last').after(row);
                    } else {
                        jQuery('#OrderTable tbody').append(row);
                    }
                });

                freight_cost = total_volume * freight_fee;
                total_cost  += freight_cost;

                final_rows = '<tr class="item freight">' +
                    '<td colspan="2">Freight Costs</td>' +
                    '<td>' + freight_fee + '</td>' +
                    '<td>' + total_volume.toFixed(2) + '</td>' +
                    '<td>' + iskFormat(freight_cost) + '</td>' +
                '</tr>' +
                '<tr class="item total">' +
                    '<td colspan="4">Total</td>' +
                    '<td>' + iskFormat(total_cost) + '</td>' +
                '</tr>';

                order_total = total_cost;

                if(jQuery('#OrderTable tr.item:last').length !== 0) {
                    jQuery('#OrderTable tr.item:last').after(final_rows);
                } else {
                    jQuery('#OrderTable tbody').append(final_rows);
                }
            },

            removeFromOrder = function(item) {
                if(typeof item == 'undefined') return;
                order_items.splice(item, 1);
                updateOrderTable();
            },

            updateQty = function(){
                q = parseInt(jQuery('#Form_EveLogisticsForm_Qty').val());
                if(!isNaN(q) && current_item && q > 0) {
                    jQuery('#PriceCheck span.qty').html(q);
                    jQuery('#PriceCheck span.price-total').html(iskFormat(q * current_item.price));
                 }
            },

            orderSummary = function() {
                ret = '';
                total_cost = 0;
                total_volume = 0;

                jQuery(order_items).each(function(i,e){
                    total_cost += e.qty * e.price;
                    total_volume += e.qty * e.volume;
                    ret += [
                        e.qty,'x ', e.name, ' @ ', iskFormat(e.price), ' ISK ea  (', iskFormat(e.qty * e.price), ' ISK)<br />'
                    ].join('');
                });
                freight_cost = total_volume * freight_fee;
                order_total = total_cost + freight_cost;
                ret += ['Freight Cost: ', iskFormat(freight_cost), ' ISK<br />',
                          '<p><strong>Submitting this order will remove ', iskFormat(order_total), ' from your available credit',
                          '</strong></p>'].join('');
                return ret;
            },

            confirmSubmitOrder = function() {
                if(doSubmit) return true;

                updateOrderPrices();
                updateOrderTable();
                if(order_items.length == 0) {
                    jQuery.confirm({
                        title: 'No Items',
                        message: 'Add some items to your order!',
                        buttons: {
                            'Go Back': {
                                class: 'pbtn'
                            }
                        }
                    });
                } else if(order_total > current_credit) {
                    jQuery.confirm({
                        title: 'Too Poor',
                        message: 'You only have ' + current_credit + ' credit available, this order requires ' + order_total + ' credit',
                        buttons: {
                            'Go Back': {
                                class: 'pbtn'
                            }
                        }
                    });
                } else {
                    jQuery.confirm({
                        title: 'Confirm Order',
                        message: 'Please review the order: <p>' + orderSummary() + '</p>',
                        buttons: {
                            'Cancel': {
                                class: 'bbtn'
                            },
                            'Confirm': {
                                class: 'btn',
                                action: function(){
                                    doSubmit = true;
                                    jQuery('#OrderItems').val(JSON.stringify(order_items));
                                    jQuery('#Form_EveLogisticsForm').submit();
                                }
                            }
                        }
                    });
                }

                // dont submit the form, bitch
                return false;
            };

            jQuery(function(){
                jQuery(window).unload(function(){
                    localStorage.setItem('order_items', JSON.stringify(order_items));
                });

                jQuery('#Form_EveLogisticsForm_Qty').keyup(updateQty);

                jQuery('#ClearOrder').click(function(){
                    jQuery.confirm({
                        title: 'Clear Order',
                        message: 'Confirm you wish to clear this order',
                        buttons: {
                            'Cancel': {
                                class: 'bbtn'
                            },
                            'Clear': {
                                class: 'btn',
                                action: function() {
                                    order_items = [];
                                    updateOrderTable();
                                }
                            }
                        }
                    });
                });

                /* //doesnt quite work with bsn.autosuggest
                jQuery('#Form_EveLogisticsForm_Item_text').focus(function(){
                    e = jQuery(this);
                    item_search_text = e.val();
                    e.val('');
                });
                jQuery('#Form_EveLogisticsForm_Item_text').blur(function(){
                    e = jQuery(this);
                    if(e.val().strip() == '') e.val(item_search_text);
                });
                */

                jQuery('#AddToOrder').click(function(){
                    q = parseInt(jQuery('#Form_EveLogisticsForm_Qty').val());
                    if(!isNaN(q) && current_item && q > 0) {
                        var item = current_item;

                        added = false;
                        jQuery(order_items).each(function(i,e){
                            if(e.id == item.id) {
                                e.qty += q;
                                added = true;
                            }
                        });
                        if(!added) {
                            item.qty = q;
                            order_items.push(item);
                        }
                    }
                    jQuery('#Form_EveLogisticsForm_Qty').val('1');
                    updateQty();
                    updateOrderTable();
                });

                jQuery('#AddFromEft').click(function(){
                    jQuery.confirm({
                        title: 'Add from EFT Textblock',
                        message: [
                                'Paste an EFT block below to add the fit to your order, be sure your fitting is upto date with the correct item names',
                                '<p><textarea id="EFTTextblock"></textarea></p>'
                                ].join(''),
                        buttons: {
                            'Cancel': {
                                class: 'bbtn'
                            },
                            'Add': {
                                class: 'btn',
                                action: function() {
                                    price_check_loading(true);
                                    jQuery.ajax({
                                        url: window.location.pathname + 'fromEft/',
                                        type: 'POST',
                                        data: { 'eft': jQuery('#EFTTextblock').val()},
                                        success: function(data) {
                                            jQuery(data).each(function(i,e){
                                                added = false;
                                                if(typeof e.qty == 'undefined') e.qty = 1;
                                                e.volume = parseFloat(e.volume);
                                                jQuery(order_items).each(function(k,v){
                                                    if(v.id == e.id) {
                                                        v.qty = v.qty + e.qty;
                                                        added = true;
                                                    }
                                                });
                                                if(!added) {
                                                    order_items.push(e);
                                                }
                                            });
                                            updateOrderTable();
                                        },
                                        complete: function() { price_check_loading(false); },
                                        dataType: 'json',
                                        aync: false
                                    });
                                }
                            }
                        }
                    });
                });

                // jQuery('#Form_EveLogisticsForm_action_submitOrder').click();

                jQuery('#Form_EveLogisticsForm').submit(confirmSubmitOrder);

                updateOrderPrices();
                updateOrderTable();
            });
JS
        );

        $invTypes = new EveinvTypesAutoSuggestField('Item', 'Item Lookup');
        $invTypes->OnChangeCallback('invTypeCallback');

        $f = new FieldSet(
            $invTypes,
            new NumericField('Qty', 'Quantity', 1)
        );
        $a = new FieldSet(
            new FormAction('submitOrder', 'Submit Order')
        );

        return new Form($this, 'EveLogisticsForm', $f, $a);
    }

    function submitOrder($data, $form)
    {
        if(!$m = Member::CurrentUser()) return Director::redirectBack();
        if(!$m->CharacterID) return Director::redirectBack();

        $input_items = json_decode($data['OrderItems']);
        if(!$input_items) {
            return Director::redirectBack();
        }

        $total_cost   = 0;
        $total_volume = 0;

        $order = new EveLogisticsOrder();
        $order->MemberID            = $m->ID;
        $order->EveLogisticsPageID  = $this->ID;
        $order->Date                 = Date('Y-m-d H:i:s');
        $order->write();

        $dos = new DataObjectSet();

        foreach($input_items as $i) {
            if($i->qty < 1) continue;

            $type = invTypes::get_one('invTypes', sprintf("typeID = %d", $i->id));
            if(!$type) continue;

            $price = $type->PriceCheck($this->PriceCheckSolarSystemID);
            if(!$price) continue;

            $price = $price * $i->qty;

            if($this->Markup) {
                $price = $price + ($price / 100 * (int) $this->Markup);
            }

            $total_volume += ($type->realVolume() * $i->qty);
            $total_cost   += $price;

            $item = new EveLogisticsItem();
            $item->invTypesID = $type->ID;
            $item->Price      = $price;
            $item->Name       = $type->typeName;
            $item->Qty        = $i->qty;
            $item->EveLogisticsOrderID = $order->ID;

            $dos->push($item);
        }

        $freight = new EveLogisticsItem();
        $freight->Price = ($total_volume * $this->IskPerM3);
        $freight->Name  = 'Freight Cost';
        $freight->Qty   = 1;
        $freight->EveLogisticsOrderID = $order->ID;

        $dos->push($freight);

        $total_cost += ($total_volume * $this->IskPerM3);

        if($total_cost > $this->EveCreditProvider()->MemberBalance()) {
            $order->delete();
            return Director::redirectBack();
        }

        foreach($dos as $do) {
            $do->write();
        }

        $credit = new EveCreditRecord();
        $credit->Amount =  0 - $total_cost;
        $credit->CharacterID = $m->CharacterID;
        $credit->RefID = $this->ID . $order->ID;
        $credit->EveCreditProviderID = $this->EveCreditProvider()->ID;
        $credit->Date = Date('Y-m-d H:i:s');
        $credit->write();

        $order->Status = 'Paid';

        $order->write();

        Session::set('clear_order_' . $this->ID, true);

        return Director::redirect(sprintf("%s%s/%d", $this->AbsoluteLink(), 'orders', $order->ID));
    }

    public function orders($request)
    {
        if(!$m = Member::CurrentUser()) return $this->httpError(404);
        $id = (int)$request->param('ID');

        if(!$id) {
            $completed_orders   = EveLogisticsOrder::get('EveLogisticsOrder', sprintf("EveLogisticsPageID = %d AND MemberID = %d AND Status = 'Complete'", $this->ID, $m->ID));
            $outstanding_orders = EveLogisticsOrder::get('EveLogisticsOrder', sprintf("EveLogisticsPageID = %d AND MemberID = %d AND Status <> 'Complete'", $this->ID, $m->ID));

            return $this->renderWith(array('EveLogisticsPage_orders', 'Page'), array(
                'Title' => $this->Title. ': Orders',
                'CompletedOrders' => $completed_orders,
                'OutstandingOrders' => $outstanding_orders
            ));
        } else {
            $order = EveLogisticsOrder::get_one('EveLogisticsOrder', sprintf("ID = %d AND MemberID = %d", $id, $m->ID));

            if(!$order) return $this->httpError(404);

            return $this->renderWith(array('EveLogisticsPage_order', 'Page'), array(
                'Title' => sprintf("%s: Order #%d%s", $this->Title, $this->ID, $order->ID),
                'Order' => $order,
            ));
        }
    }

    public function manage($request)
    {
        if(!$m = Member::CurrentUser() || !Permission::check('EVE_LOGISTICS_ADMIN')) return $this->httpError(404);
        $id = (int)$request->param('ID');

        if(!$id) {
            $completed_orders   = EveLogisticsOrder::get('EveLogisticsOrder', sprintf("EveLogisticsPageID = %d AND Status = 'Complete'", $this->ID));
            $outstanding_orders = EveLogisticsOrder::get('EveLogisticsOrder', sprintf("EveLogisticsPageID = %d AND Status <> 'Complete'", $this->ID));

            return $this->renderWith(array('EveLogisticsPage_manage', 'Page'), array(
                'Title' => $this->Title. ': Manage Orders',
                'CompletedOrders' => $completed_orders,
                'OutstandingOrders' => $outstanding_orders
            ));
        } else {
            $order = EveLogisticsOrder::get_one('EveLogisticsOrder', sprintf("ID = %d", $id));
            if(!$order) return $this->httpError(404);

            Requirements::CustomScript(<<<JS
                jQuery(function(){
                    jQuery('#Form_updateStatus_Status').change(function(){
                        jQuery('#Form_updateStatus').submit();
                    });
                });
JS
            );

            $s = array(
                'Pending' => 'Pending',
                'Paid' => 'Paid',
                'Purchased' => 'Purchased',
                'Shipped' => 'Shipped',
                'Complete' => 'Complete'
            );

            $sfield = new DropDownField('Status', 'Status', $s, $order->Status);
            $idfield = new HiddenField('ID', 'ID', $order->ID);
            $status_form = new Form($this, 'updateStatus', new FieldSet($sfield, $idfield), new FieldSet());

            return $this->renderWith(array('EveLogisticsPage_order', 'Page'), array(
                'Title' => sprintf("%s: Order #%d%s", $this->Title, $this->ID, $order->ID),
                'Order' => $order,
                'StatusForm' => $status_form
            ));
        }
    }

    public function updateStatus($request)
    {
        if(!$m = Member::CurrentUser() || !Permission::check('EVE_LOGISTICS_ADMIN')) return $this->httpError(404);
        $vars = $request->postVars();
        foreach(array('Status', 'ID', 'SecurityID') as $key) {
            if(!array_key_exists($key, $vars)) return Director::redirectBack();
        }

        //if(!SecurityToken::check($vars['SecurityID'])) return Director::redirectBack();
        $order = EveLogisticsOrder::get_by_id('EveLogisticsOrder', (int)$vars['ID']);
        if(!$order) return Director::redirectBack();

        $order->Status = $vars['Status'];
        $order->write();

        return Director::redirectBack();
    }

    public function priceCheck($request)
    {
        $id = (int)$request->param('ID');
        if(!$id) return '{}';

        $type = invTypes::get_one('invTypes', sprintf("typeID = %d", $id));
        if(!$type) return '{}';

        $price = $type->PriceCheck($this->PriceCheckSolarSystemID);
        if(!$price) return '{}';

        if($this->Markup) {
            $price = $price + ($price / 100 * (int) $this->Markup);
        }

        /* dont round here, wait till after * qty */
        //$price = round($price, 2);

        return json_encode(array(
            'id'     => $type->typeID,
            'name'   => $type->typeName,
            'price'  => $price,
            'volume' => $type->realVolume()
        ));

        return '{}';
    }

    public function fromEft($request)
    {
        $vars = $request->postVars();
        if(!array_key_exists('eft', $vars)) return '{}';

        $vars['eft'] = trim($vars['eft']);

        $ret = array();
        try {
            $fit = new EveEFTFitting($vars['eft']);

            if($s = $fit->_ship()) {
                $price = $s->PriceCheck($this->PriceCheckSolarSystemID);
                if($price && $price != 0) {

                    if($this->Markup) {
                        $price = $price + ($price / 100 * (int) $this->Markup);
                    }

                    $ret[] = array(
                        'id'     => $s->typeID,
                        'name'   => $s->typeName,
                        'price'  => $price,
                        'volume' => $s->realVolume()
                    );
                }
            }

            foreach(array('hi', 'med', 'lo', 'sub', 'rig', 'drone') as $slot) {
                foreach($fit->_slots($slot) as $m) {
                    $p = $m->PriceCheck($this->PriceCheckSolarSystemID);
                    if(!$p || $p == 0) continue;

                    if($this->Markup) {
                        $p = $p + ($p / 100 * (int) $this->Markup);
                    }

                    if($c = $m->Charge) {
                        $cp = $c->PriceCheck($this->PriceCheckSolarSystemID);
                        if($cp && $cp != 0) {
                            if($this->Markup) {
                                $cp = $cp + ($cp / 100 * (int) $this->Markup);
                            }

                            $ret[] = array(
                                'id'     => $c->typeID,
                                'name'   => $c->typeName,
                                'price'  => $cp,
                                'volume' => $c->realVolume(),
                                'qty'    => 500,
                            );
                        }
                    }

                    $r = array(
                        'id'     => $m->typeID,
                        'name'   => $m->typeName,
                        'price'  => $p,
                        'volume' => $m->realVolume()
                    );
                    if($m->Qty) {
                        $r['qty'] = $m->Qty;
                    }
                    $ret[] = $r;
                }
            }

            return json_encode($ret);
        } catch (Exception $e) {
            //throw $e;
            return '{}';
        }
    }
}
