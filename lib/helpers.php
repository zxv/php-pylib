<?php
function last_key($arr) { return key(array_slice($arr, -1, 1, TRUE)); }

function getRefNames($refObjects) {
    $output = array();

    foreach($refObjects as $key => $value) {
        $output[] = $value->name;
    }

    return $output;
}

function inspect_object($var, $show_privates=true) {
    $class = get_class($var);
    $ref = new ReflectionClass($class);
    $methods = $ref->getMethods();
    $properties = $ref->getProperties();

    $complete = array_merge($methods, $properties);
    
    if ($show_privates == false) {
        $final = array();
        foreach( $complete as $ref ) {
            if ($ref->isPublic()) {
                $final[] = $ref->name;
            }
        }

        return $final;
    }

    #$check_public = zip($complete, array_map(function($x) { echo gettype($x); die();/*return $x->isPublic();*/ }, $complete_names));
    #$matches = array_map(function ($x) { if ($x[1] == true) { return $x[0]; } }, $check_public);
    return getRefNames($complete);
}

function set_trace_error($code, $msg, $file, $line, $context) {
    $stdout = fopen('php://stdout', 'w');

    if ($code == 1024) {
        $backtrace = debug_backtrace();

        // hack to access last active object
        foreach ($backtrace as $key => $debugItem) {
            if ($debugItem['function'] == "trigger_error") {
                $next = $backtrace[$key + 1];
                if (array_key_exists("object", $next)) {
                    // Assign the discovered object's instance to "$that"
                    $context["that"] = $next['object'];
                }
                break;
            }
        }
        $context["__debug"] = $backtrace;
        set_trace_run($context);
        return true;
    }

    //fwrite($stdout, error_get_last());
    // If the error is one that we didn't mean to catch, throw an
    // exception.
    throw new ErrorException($msg, 0, $code, $file, $line);

}

function set_trace_run($vars=null) {
    require("phpa.php");
    echo "\n";
    __phpa__interactive($vars);
}

?>

