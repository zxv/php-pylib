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
    require("phpa-lib.php");
    echo "\n";
    __phpa__interactive($GLOBALS);
}

function dire($thing) {
    $thing_type = gettype($this);

    #if ($thing_type == "");
}

?>
