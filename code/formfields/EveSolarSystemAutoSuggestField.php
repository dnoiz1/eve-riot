<?php

/* this is a field type that stores int solarsystemid
 * by displaying a textfield with autosuggest
 */

class EveSolarSystemAutoSuggestField extends TextField
{
    function __construct($name, $title = null, $value = '', $maxLength = null, $form = null)
    {
        return parent::__construct($name, $title, $value = '', $maxLength = null, $form = null);
    }

    function Field($properties = array())
    {
        Requirements::javascript('eacc/thirdparty/autosuggest/js/bsn.AutoSuggest_2.1.3.js');
        Requirements::css('eacc/thirdparty/autosuggest/css/autosuggest_inquisitor.css');

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

        $solarsystem = mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->Value())));
        $text_attributes['value'] = ($solarsystem) ? $solarsystem->solarSystemName : '';

        $text_attributes['type'] = 'text';
        $text_attributes['class'] .= ' text';

        $text_tag = $this->createTag('input', $text_attributes);

        return $hidden_tag . $text_tag;
    }

    function FieldHolder($properties = array())
    {
        $id = $this->id();

        $h = parent::FieldHolder($properties);
        $h .= <<<JS
            <script type="text/javascript">
            if(typeof EveSolarSystemAutoSuggestLoad !== 'object') {
                var EveSolarSystemAutoSuggestLoad = [];
            }
            EveSolarSystemAutoSuggestLoad.push(function(){
                var options_{$id} = {
                    script: function() { return '/eveStaticData/solarSystems/' + jQuery('#{$id}_text').val(); },
                    json: true,
                    maxentries: 6,
                    callback: function(o) { jQuery('#{$id}').val(o.id); },
                    timeout: 10000000,
                    offsety: -13
                }

                var as_{$id} = new bsn.AutoSuggest('{$id}_text', options_{$id});
            });
            //.. this loads before jquery, need a better fix -noiz
            window.onload = function(){ jQuery(EveSolarSystemAutoSuggestLoad).each(function(i,f){ f(); }); }
            </script>
JS;
        return $h;
    }
}
