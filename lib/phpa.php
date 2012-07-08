<?php
    function __phpa__interactive($__phpa_globals=null)
    {
        // Import passed in vars to local scope
        eval(__phpa__import_globals($__phpa_globals));

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

            // 
            if ((!isset($__phpa__hist)) || (($__phpa__line != $__phpa__hist)))
            {
                readline_add_history($__phpa__line);
                $__phpa__hist = $__phpa__line;
            }

            if (__phpa__is_immediate($__phpa__line))
                $__phpa__line = "return ($__phpa__line)";

            ob_start();
            $ret = eval("unset(\$__phpa__line); $__phpa__line;");
            if (ob_get_length() == 0)
            {
                if (is_bool($ret))
                    echo ($ret ? "true" : "false");
                else if (is_string($ret))
                    echo "'" . addcslashes($ret, "\0..\37\177..\377")  . "'";
                else if (!is_null($ret))
                    print_r($ret);
            }
            unset($ret);
            $out = ob_get_contents();
            ob_end_clean();
            if ((strlen($out) > 0) && (substr($out, -1) != "\n"))
                $out .= "\n";
            echo $out;
            unset($out);
        }
    }

    function __phpa__import_globals($glb) {
        if ($glb != null) {
            $vars = array();

            foreach($glb as $key => $val) {
                $vars[] = "$".$key;
            }

            return "global ".join(",", $vars).";";
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

?>
