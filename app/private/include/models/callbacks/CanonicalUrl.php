<?php

namespace App\Callback;

class CanonicalUrl extends \GestyMVC\Model\Callback
{
    protected function registerEvents()
    {
        $this->registerEvent('beforeSave', function (&$element, &$values, &$options) {
            // Remove retrocompatibility of "none" value
            foreach (['seo_description', 'seo_title'] as $field) {
                if ($this->currentValue($field, $element, $values) === 'none') {
                    $values[$field] = null;
                }
            }

            return true;
        });
    }
}
