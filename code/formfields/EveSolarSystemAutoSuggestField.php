<?php

/* this is a field type that stores int solarsystemid
 * by displaying a textfield with autosuggest
 * TODO: generic js for initializing typeheads
 */

class EveSolarSystemAutoSuggestField extends TextField
{
    function Field($properties = array())
    {
        Requirements::javascript('eacc/thirdparty/hogan.min.js');
        Requirements::javascript('eacc/thirdparty/typeahead/typeahead.min.js');
        Requirements::css('eacc/thirdparty/typeahead/typeahead-bootstrap.css');

        $attributes = array(
            'class' => sprintf("%s%s", $this->class, ($this->extraClass()) ? ' '. $this->extraClass() : '')
        );

        $hidden_attributes = $attributes;
        $hidden_attributes['id'] = $this->id();
        $hidden_attributes['value'] = $this->Value();
        $hidden_attributes['name'] = $this->name;
        $hidden_attributes['type'] = 'hidden';
        $hidden_attributes['class'] .= ' hidden';

        $hidden_tag = $this->createTag('input', $hidden_attributes);

        $text_attributes = $attributes;
        $text_attributes['id'] = sprintf("%s_text", $this->id());
        $text_attributes['name'] = sprintf("%s_text", $this->name);

        $solarsystem = mapSolarSystems::get()->byID(Convert::raw2sql($this->Value()));
        $text_attributes['value'] = ($solarsystem) ? $solarsystem->solarSystemName : '';

        $text_attributes['type'] = 'text';
        $text_attributes['class'] .= ' text';

        $text_tag = $this->createTag('input', $text_attributes);

        return $hidden_tag . $text_tag;
    }

    function FieldHolder($properties = array())
    {
        $h = parent::FieldHolder($properties);
        $id = $this->id();
        $js = <<<JS
                $('input#{$id}_text').typeahead({
                    remote: '/eveStaticData/solarSystems/%QUERY',
                    template: [
                        '<p class="tt-system-value">{{value}}</p>',
                        '<p class="tt-system-region">{{region}}</p>'
                    ].join(''),
                    engine: Hogan
                });
                $('input#{$id}_text').on('typeahead:selected typeahead:autocompleted', function(o, data){
                    $('input#{$id}').val(data.id);
                });
                $('input#{$id}_text').on('blur', function(e){
                    $.get('/eveStaticData/solarSystems/' + $('input#{$id}_text').val(), function(data){
                        if(data.length == 0) {
                            $('input#{$id}_text').val('');
                            $('input#{$id}').val('');
                        } else {
                            $('input#{$id}_text').val(data[0].value);
                            $('input#{$id}').val(data[0].id);
                        }
                    }, 'json');
                });
JS;

        if(Controller::Curr() instanceof ModelAdmin && Director::is_ajax()) {
            $h .= <<<JS
                <script type="text/javascript">
                    (function($){{$js}})(jQuery);
                </script>
JS;
        } else {
            Requirements::CustomScript('jQuery(function($){' . $js . '});');
        }

        return $h;
    }
}
