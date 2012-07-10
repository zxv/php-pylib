<?php
require_once("lib/helpers.php");

function zip($a, $b) {
    $args = func_get_args();

    // Ensure that both arguments are iterable
    foreach ($args as $ind => $arg) {
        if (!is_array($arg)) {
            throw new Exception("zip argument #$ind must support iteration");
        }
    }

    $iter_len = min(count($a), count($b));
    $final = array();

    for ($i=0; $i<$iter_len; $i++) {
        $final[] = array($a[$i], $b[$i]); 
    }

    return $final;
}

function set_trace() {
    // Nothing equivalent that can be placed in here yet.
    // For the moment, two steps must be executed:
    // 1. At the begining of your project: set_error_handler("set_trace_error");
    // 2. At the location where you'd like to set trace: trigger_error("", 1024);
}

function len($var) {
    if (is_array($var)) {
        return count($var);
    } 
    if (is_string($var)) {
        return strlen($var);
    }

    $type = gettype($var);
    throw new Exception("Error: variable of type '$type' has no len()");
}

function repr($var) {
    if (is_int($var)) {
        return $var;
    }

    if (is_null($var)) {
        return null;
    }

    if (is_bool($var)) {
        return ($var ? "true" : "false");
    }

    if (is_string($var)) {
        return "'" . addcslashes($var, "\0..\37\177..\377")  . "'";
    } 

    if (is_object($var)) {
        $cls = get_class($var);
        return "<$cls object>";
    }

    if (!is_null($var)) {
        $var = (array) $var;
        // If array is non-associative, return values
        if (array_values($var) === $var) {
            $items = array_values($var);
            $items = array_map(function($item) {return repr($item); }, $items);

            $join_char = ', ';
            $outer_chars = array("[", "]");
        } else {
            $items = array();
            $var_export = var_export($var, true);
            $var_export = explode("\n", $var_export);

            $last = len($var_export) - 1;
            for ($i=1; $i < $last; $i++) {
                $items[] = trim($var_export[$i]);
            }
            
            $last = last_key($items);
            $it = rtrim($items[$last], ",");
            $items[$last] = $it;

            $join_char = " ";
            $outer_chars = array("array(", ")");
        }

        return $outer_chars[0].join($join_char, $items).$outer_chars[1];
    }
}

function type($thing)  {
    return gettype($thing);
}

function dig($thing=null, $show_privates=true) {
    // Oh PHP. Why did you have to define dir()?
    // What a pathetic namespace conflict :(
    if (is_object($thing)) {
        $inspect = inspect_object($thing, $show_privates);
        // XXX: Extra echo
        echo repr($inspect);
        return $inspect;
    }

    
    #if ($thing_type == "");
    if ($thing == null) {
        return array_merge(array_keys($GLOBALS));
    } else {
        return array_keys($thing);
    }
}

?>
