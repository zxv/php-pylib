<?php

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
    require("lib/phpa.php");
    echo "\n";
    __phpa__interactive($GLOBALS);
}

function repr($var) {
    //echo gettype($var);

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

    if (!is_null($var)) {
        $var = (array) $var;
        // If array is non-associative, return values
        if (array_values($var) === $var) {
            $keys = array_values($var);
        } else {
            $keys = array_keys($var);
        }

        $keys = array_map(function($key) {return repr($key); }, $keys);
        return '['.join(', ', $keys).']';
    }
}

function type($thing)  {
    return gettype($thing);
}

function dire($thing=null) {
    // Oh PHP. Why did you have to define dir()?
    // What a pathetic namespace conflict :(
    
    #if ($thing_type == "");
    if ($thing == null) {
        return repr($GLOBALS);
    } else {
        return $thing;
    }
}

?>
