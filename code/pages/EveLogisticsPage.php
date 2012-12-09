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

    function getCMSFields()
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
}

class EveLogisticsPage_controller extends Page_controller
{
    function EveLogisticsForm()
    {
        $freight_fee = $this->IskPerM3;

        Requirements::CustomScript(<<<JS
            var ls_order_items = localStorage.getItem('order_items');
            if(ls_order_items) {
                var order_items = JSON.parse(ls_order_items);
            } else {
                var order_items = [];
            }

            var freight_fee = {$freight_fee};
            var item_search_text;

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

            updateOrderTable = function() {
                if(order_items.length == 0) {
                    jQuery('#OrderTable .no-data').fadeIn();
                } else {
                    jQuery('#OrderTable .no-data').hide();
                }
                jQuery('#OrderTable tbody tr.item').remove();

                total_volume = 0;
                total_cost   = 0;

                jQuery(order_items).each(function(i,e){
                    total_volume += parseInt(e.volume * e.qty);
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
                final_rows = '<tr class="item freight">' +
                    '<td colspan="2">Freight Costs</td>' +
                    '<td>' + freight_fee + '</td>' +
                    '<td>' + total_volume.toFixed(2) + '</td>' +
                    '<td>' + iskFormat(total_volume * freight_fee) + '</td>' +
                '</tr>' +
                '<tr class="item total">' +
                    '<td colspan="4">Total</td>' +
                    '<td>' + iskFormat(total_cost) + '</td>' +
                '</tr>';

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
            };

            jQuery(function(){
                jQuery(window).unload(function(){
                    localStorage.setItem('order_items', JSON.stringify(order_items));
                });

                jQuery('#Form_EveLogisticsForm_Qty').keyup(updateQty);

                jQuery('#ClearOrder').click(function(){
                    if(confirm('Are You sure you want to clear this order?')) {
                        order_items = [];
                        updateOrderTable();
                    }
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

                jQuery('#Form_EveLogisticsForm_action_submitOrder').click(function(){
                });

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
        var_dump($data);
    }

    public function priceCheck($request)
    {

        $id = (int)$request->param('ID');
        if(!$id) return '{}';

        $type = invTypes::get_one('invTypes', sprintf("typeID = %d", $id));
        if(!$type) return '{}';

        /* really should extend RestfulService to create an eve central object, or figureout Ale or something */
        // cached for 12 hours
        $eveCentral = new RestfulService("http://api.eve-central.com", 12 * 3600);

        $params = array('typeid' => $id);
        if($this->PriceCheckSolarSystemID) {
            $params['usesystem'] = $this->PriceCheckSolarSystemID;
        }
        $eveCentral->httpHeader('Accept: application/xml');
        $eveCentral->httpHeader('Content-Type: application/xml');

        try {
            /* pfft GET ignores $data in RestfulService::request() */
            $req = $eveCentral->request('/api/marketstat?' . http_build_query($params));
            $median = $req->xpath(sprintf('marketstat/type[@id=%d]/sell/median', $type->typeID));
            $median = (string)$median[0];

            $price = ($this->Markup) ? ($median + ($median / 100 * (int)$this->Markup)) : $median;

            /* dont round here, wait till after * qty */
            //$price = round($price, 2);

            $volume = eveStaticData::packagedSizes(preg_replace('/[^a-zA-Z0-9]/', '', $type->Group()->groupName));
            if(!$volume) $volume = $type->volume;

            return Convert::array2json(array(
                'id'     => $id,
                'name'   => $type->typeName,
                'price'  => $price,
                'volume' => $volume
            ));

        } catch(Exception $e) {
            //return $this->httpError(404);
            //throw $e;
            // really need better handling
            return '{}';
        }
        return '{}';
    }
}
