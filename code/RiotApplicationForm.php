<?php

class RiotApplicationForm extends UserDefinedForm
{
}

class RiotApplicationForm_controller extends UserDefinedForm_controller
{
    function process($data, $form)
    {
        parent::process($data, $form);
        return $this->Parent()->Link('api-keys');
    }
}
