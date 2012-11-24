<?php

/* this is a field type that stores int solarsystemid
 * by displaying a textfield with autosuggest
 */

class EveSolarSystemAutoSuggestField extends TextField
{
    function __construct($name, $title = null)
    {
        return parent::__construct($name, $title);
    }

    function Field()
    {
        Requirements::javascript('mysite/thirdparty/autosuggest/js/bsn.AutoSuggest_2.1.3.js');
        Requirements::css('mysite/thirdparty/autosuggest/css/autosuggest_inquisitor.css');

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
        $text_attributes['value'] = mapSolarSystems::get_one('mapSolarSystems', sprintf("solarSystemID = %d", Convert::raw2sql($this->Value())))->solarSystemName;
        $text_attributes['type'] = 'text';
        $text_attributes['class'] .= ' text';

        $text_tag = $this->createTag('input', $text_attributes);

        return $hidden_tag . $text_tag;
    }

    function FieldHolder()
    {
        $id = $this->id();

        $h = parent::FieldHolder();
        $h .= <<<JS
            <script type="text/javascript">
            jQuery(function(){
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
            </script>
JS;
        return $h;
    }
}
