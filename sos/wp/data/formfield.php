<?php
namespace SOSIDEE_MFA_PHOTOEDITOR\SOS\WP\DATA;
defined( 'SOSIDEE' ) or die( 'you were not supposed to be here' );

class FormField
{
    public $type;
    public $name;
    public $value;

    public function __construct($type, $name, $value)
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }

    private function getKeys( $parameters ) {
        $defaults = [
             'class' => null
            ,'style' => null
            ,'maxlength' => null
            ,'min' => null
            ,'max' => null
            ,'step' => null
            ,'width' => null
            ,'height' => null
            ,'cols' => null
            ,'rows' => null

            ,'onclick' => null
            ,'onchange' => null

            ,'label' => null
            ,'options' => null
        ];
        return array_merge( $defaults, $parameters );
    }

    private function getStyle($parameter, $default = null) {
        if ( !is_null($default) ) {
            if ( !is_null($parameter) ) {
                $results = [];
                $defs = explode(';', $default);
                for ($n=0; $n<count($defs); $n++) {
                    $kvs = explode(':', $defs[$n]);
                    if (count($kvs) == 2) {
                        $results[$kvs[0]] = rtrim( $kvs[1], ';');
                    }
                }
                $pars = explode(';', $parameter);
                for ($n=0; $n<count($pars); $n++) {
                    $kvs = explode(':', $pars[$n]);
                    if (count($kvs) == 2) {
                        $results[$kvs[0]] = rtrim( $kvs[1], ';');
                    }
                }
                /*
                $results = array_combine(
                    array_merge( array_keys($defaults), array_keys($parameters) ),
                    array_merge( array_values($defaults), array_values($parameters) )
                );
                */
                $ret = "";
                foreach ($results as $key => $value ) {
                    $ret .= "$key:$value;";
                }
                return $ret;
            } else {
                return $default;
            }
        } else {
            return $parameter;
        }
    }

    public function html( $parameters = [] ) {
        $keys = $this->getKeys( $parameters );
        $name = esc_attr( $this->name );
        $value = esc_attr( $this->value );

        if ( $this->type == FormFieldType::TEXT ) {

            FormTag::html( 'input', [
                     'type' => 'text'
                    ,'id' => $name
                    ,'name' => $name
                    ,'value' => $value
                    ,'maxlength' => $keys['maxlength']
                    ,'onclick' => $keys['onclick']
                    ,'onchange' => $keys['onchange']
                ]
            );

        } else if ( $this->type == FormFieldType::CHECK ) {

            FormTag::html( 'input', [
                    'type' => 'checkbox'
                    ,'id' => $name
                    ,'name' => $name
                    ,'checked' => boolval($this->value)// ? 'checked' : null
            ]);

            $label = $keys['label'];
            if ( !is_null($label) ) {
                FormTag::html( 'label', [
                     'for' => $name
                    ,'content' => $label
                ]);
            }

        } else if ( $this->type == FormFieldType::TEXTAREA ) {

            FormTag::html( 'textarea', [
                 'id' => $name
                ,'name' => $name
                ,'content' => $this->value
                ,'maxlength' => $keys['maxlength']
                ,'cols' => $keys['cols']
                ,'rows' => $keys['rows']
            ]);

        } else if ( $this->type == FormFieldType::SELECT ) {

            $html = '';
            $options = $keys['options'];
            if ( !is_null($options) ) {
                $counter = 0;
                foreach ($options as $_value => $_text) {
                    $html .= FormTag::get('option',[
                         'id' => "{$name}_$counter"
                        ,'name' => "{$name}_$counter"
                        ,'value' => $_value
                        ,'selected' => strcasecmp($_value, $value) == 0
                        ,'content' => $_text
                    ]);
                    $counter++;
                }

            }

            FormTag::html( 'select', [
                 'id' => $name
                ,'name' => $name
                ,'html' => $html
                ,'onchange' => $keys['onchange']
            ]);

        } else if ( $this->type == FormFieldType::NUMBER ) {

            FormTag::html( 'input', [
                     'type' => 'number'
                    ,'id' => $name
                    ,'name' => $name
                    ,'value' => $value
                    ,'min' => $keys['min']
                    ,'max' => $keys['max']
                    ,'step' => $keys['step']
                    ,'onclick' => $keys['onclick']
                    ,'onchange' => $keys['onchange']
                ]
            );

        } else if ( $this->type == FormFieldType::DATE ) {

            FormTag::html( 'input', [
                     'type' => 'date'
                    ,'id' => $name
                    ,'name' => $name
                    ,'value' => $value
                    ,'min' => $keys['min']
                    ,'max' => $keys['max']
                    ,'step' => $keys['step']
                    ,'onclick' => $keys['onclick']
                    ,'onchange' => $keys['onchange']
                    ,'style' => $this->getStyle( $keys['style'], 'cursor:pointer;' )
                ]
            );

        } else if ( $this->type == FormFieldType::HIDDEN ) {

            FormTag::html( 'input', [
                     'type' => 'hidden'
                    ,'id' => $name
                    ,'name' => $name
                    ,'value' => $value
                ]
            );

        }

    }

    public function getValueAsDate( $end_of_day = false ) {
        if ($this->type != FormFieldType::DATE) {
            return null;
        }
        $value = trim( $this->value );
        if ( empty($value) ) {
            return null;
        }
        $value = !$end_of_day ? "$value 00:00:00" : "$value 23:59:59";
        $ret = WpColumn::getDatetimeFromString($value);
        if ( $ret !== false )
        {
            return $ret;
        } else {
            return null;
        }
    }
    public function setValueFromDate( $value, $format = 'Y-m-d' ) {
        if ( $value instanceof \DateTime ) {
            $this->value = $value->format($format);
        } else {
            $this->value = $value;
        }
    }


}