<?php
function last_key($arr) { return key(array_slice($arr, -1, 1, TRUE)); }

function getRefNames($refObjects) {
    $output = array();

    foreach($refObjects as $key => $value) {
        $output[] = $value->name;
    }

    return $output;
}

function inspect_object($var) {
    $class = get_class($var);
    $ref = new ReflectionClass($class);
    $methods = $ref->getMethods();
    $properties = $ref->getProperties();
    return array_merge(getRefNames($methods), getRefNames($properties));
}

function set_trace_error($code, $msg, $file, $line, $context) {
    if ($code == 1024) {
        //$bt = debug_bactrace();
        $err = array($code, $msg, $file, $line);
        $context["err"] = $err;
        set_trace_run($context);
        return true;
    }
    
    echo error_get_last();

}

function set_trace_run($vars=null) {
    require("phpa.php");
    echo "\n";
    __phpa__interactive($vars);
}

?>

