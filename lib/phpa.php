<?php
    require_once(dirname(dirname(__file__))."/py.php");
    define("__PHPA_PROMPT", ">>> ");

    // TODO: 
    // Add function/method/class repr
    //
    // Do some token checking to ensure that certain annoying
    // situations are avoided. For instance:
    // - Cannot access protected properties
    // - Cannot redeclare class
    // - Call to undefined method
    // - Missing argument 1 for Classname
    // - __construct() expects at least 1 parameter, 0 given
    //
    // XXX:
    // Segmentation fault: 
    // 1. php -S localhost:8080
    // 2. Start trace, ^D
    // 3. Refresh in browser
    //
    function __phpa__interactive($__phpa__globals=null)
    {
        $globals_original = $GLOBALS;
        $stdout = fopen('php://stdout', 'w');

        // Import passed in vars to local scope
        if ($__phpa__globals != null) {
            __phpa__import_globals($__phpa__globals);
            extract($__phpa__globals);
        }

        for (;;)
        {
            // Tab Completion
            readline_completion_function("__phpa__rl_complete");

            // User input
            $__phpa__line = readline(__PHPA_PROMPT);

            // Blank input
            if ($__phpa__line === false)
            {
                echo "\n";
                break;
            }
            if (strlen($__phpa__line) == 0)
                continue;

            // Add line to history
            if ((!isset($__phpa__hist)) || (($__phpa__line != $__phpa__hist)))
            {
                readline_add_history($__phpa__line);
                $__phpa__hist = $__phpa__line;
            }

            //if (strstr("repr\(", $__phpa__line)) {
            // Remove the repr function call
            //$__phpa__line = preg_replace('/repr\(/', '', $__phpa__line, 1);

            // Remove only one ocurrence of a ')'
            //$__phpa__line = preg_replace('/\)/', '', $__phpa__line, 1);
            //}
            
            // The following if statement is ugly and must die
            // Please refactor me!
            if (__phpa__is__special($__phpa__line)) {
                ob_start();
                $ret = $__phpa__line;
                echo repr($ret);
            } else {
                if (__phpa__is_immediate($__phpa__line)) {
                    $__phpa__line = "return ($__phpa__line)";
                }

                //XXX: dig() calls get quotes on the end
                ob_start();

                $ret = eval("unset(\$__phpa__line); $__phpa__line;");
                if (ob_get_length() == 0) {
                   echo repr($ret);
                }
            }
            unset($ret);

            $out = ob_get_contents();
            ob_end_clean();

            if ((strlen($out) > 0) && (substr($out, -1) != "\n"))
                $out .= "\n";
            fwrite($stdout, $out);
            unset($out);

            // Assign all defined variables to the global scope.
            // This is used (currently) to check if queried class
            // methods actually exist.
            // XXX: Global pollution?
            $vars = get_defined_vars();
            foreach($vars as $key => $var) {
                $GLOBALS[$key] = $var;
            }
            unset($vars); unset($key); unset($keys);
        }
    }

    function __phpa__import_globals($glb) {
        if ($glb != null) {
            $vars = array();

            foreach($glb as $key => $val) {
                $vars[] = "$".$key;
            }

            eval("global ".join(",", $vars).";");
            
        }

        return false;
    }

    function __phpa__is__special($var) {

        // Function call
        if (strstr($var, "(")) {
            return false;
        }

        // Function name
        if (function_exists($var)) {
            return true;
        }

        // Method
        $objsplit = __phpa__split__object($var);
        if ($objsplit != false)  {
            return method_exists($objsplit['object'], $objsplit['method']);
        }

        // Class
        if (in_array($var, get_declared_classes())) {
            return true;
        }

        return false;
    }

    function __phpa__split__object($query) {
        // Given a string joined by -> ops, return
        // an object and a method
        
        $parts = explode("->", $query);
        if ((count($parts) > 1)) { 
            $method = array_pop($parts);
            $object = ltrim(implode("->", $parts), "$");
            global $$object;

            // Bring the object from the repl scope
            // to here, for evaluation
            return array("object" => $$object, "method" => $method);
        }

        return false;
    }

    function __phpa__rl_complete($line, $pos, $cursor)
    {
        $const = array_keys(get_defined_constants());
        $var = array_keys($GLOBALS);

        $func = get_defined_functions();
        $s = "__phpa__";
        foreach ($func["user"] as $i)
            if (substr($i, 0, strlen($s)) != $s)
                $func["internal"][] = $i;
        $func = $func["internal"];

        return array_merge($const, $var, $func);
    }

    function __phpa__is_immediate($line)
    {
        $skip = array("class", "declare", "die", "echo", "exit", "for",
                      "foreach", "function", "global", "if", "include",
                      "include_once", "print", "require", "require_once",
                      "return", "static", "switch", "unset", "while");
        $okeq = array("===", "!==", "==", "!=", "<=", ">=");
        $code = "";
        $sq = false;
        $dq = false;
        for ($i = 0; $i < strlen($line); $i++)
        {
            $c = $line{$i};
            if ($c == "'")
                $sq = !$sq;
            else if ($c == '"')
                $dq = !$dq;
            else if (($sq) || ($dq))
            {
                if ($c == "\\")
                    $i++;
            }
            else
                $code .= $c;
        }
        $code = str_replace($okeq, "", $code);
        if (strcspn($code, ";{=") != strlen($code))
            return false;
        $kw = mb_split("[^A-Za-z0-9_]", $code);
        foreach ($kw as $i)
            if (in_array($i, $skip))
                return false;
        return true;
    }

    function __phpa__print_info()
    {
        $ver = phpversion();
        $sapi = php_sapi_name();
        $date = __phpa__build_date();
        $os = PHP_OS;
        echo "PHP $ver ($sapi) ($date) [$os]\n";
    }

    function __phpa__build_date()
    {
        ob_start();
        phpinfo(INFO_GENERAL);
        $x = ob_get_contents();
        ob_end_clean();
        $x = strip_tags($x);
        $x = explode("\n", $x);
        $s = array("Build Date => ", "Build Date ");
        foreach ($x as $i)
            foreach ($s as $j)
                if (substr($i, 0, strlen($j)) == $j)
                    return trim(substr($i, strlen($j)));
        return "???";
    }

    function __phpa__setup()
    {
        if (version_compare(phpversion(), "4.3.0", "<"))
        {
            echo "PHP 4.3.0 or above is required.\n";
            exit(111);
        }
        if (!extension_loaded("readline"))
        {
            $prefix = (PHP_SHLIB_SUFFIX == "dll") ? "php_" : "";
            if (!@dl($prefix . "readline." . PHP_SHLIB_SUFFIX))
            {
                echo "The 'readline' module is required.\n";
                exit(111);
            }
        }
        error_reporting(E_ALL | E_STRICT);
        ini_set("error_log", NULL);
        ini_set("log_errors", 1);
        ini_set("html_errors", 0);
        ini_set("display_errors", 0);
        while (ob_get_level())
            ob_end_clean();
        ob_implicit_flush(true);
    }

    //function __phpa__persist()
    //{
    //    set_exit_overload(function() { __phpa__interactive(); });
    //}

?>
