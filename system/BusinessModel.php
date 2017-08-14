<?php

class BusinessModel {

    public function __construct() {

    }

    public function extend($data) {

        // covert to array if need be
        $data = (is_array($data)) ? $data : json_decode(json_encode($data), true);

        $variable_list  = array();
        $reflect        = new ReflectionClass($this);
        $reflect_list   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach($reflect_list as $reflect_) {
            array_push($variable_list, $reflect_->name);
        }

        foreach($variable_list as $variable) {

            // if (isset($this->$variable) && isset($data[$variable])) {
            if (isset($data[$variable])) {
                $this->$variable = $data[$variable];
            }
        }
    }
}
