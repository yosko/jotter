<?php

/**
 * Utility class to easily and beautifully dump PHP variables
 * the functions d() and dd() where inspired Kint
 * 
 * @author      Yosko <contact@yosko.net>
 * @version     0.5
 * @copyright   none: free and opensource
 * @link        https://github.com/yosko/easydump
 */
class EasyDump {
    //display configurattion
    public static $config = array(
        'showVarNames'  => true,    //true to show names of the given variables
        'showSource'    => false,    //true to show the code of the PHP call to EasyDump
        'color'         => array(   //default theme based on Earthsong by daylerees
            'text'          => '#EBD1B7',
            'border'        => '#7A7267',
            'background'    => '#36312c',
            'name'          => '#F8BB39',
            'type'          => '#DB784D',
            'value'         => '#95CC5E'
        )
    );

    /**
     * For debug purpose only
     * @param  misc    $variables any number of variables of any type
     */
    public static function debug() {
        $trace = debug_backtrace();
        $call = self::readCall($trace);

        echo '<pre style="border: 0.5em solid '.self::$config['color']['border'].'; color: '.self::$config['color']['text'].'; background-color: '.self::$config['color']['background'].'; margin: 0; padding: 0.5em; white-space: pre-wrap;font-family:\'DejaVu Sans Mono\',monospace;font-size:11px;">';
        
        //show file and line
        self::showCall($call);

        //show PHP source of the call
        if(self::$config['showSource'])
            self::showSource($call);

        //get the variable names (if available)
        if(self::$config['showVarNames'])
            $varNames = self::guessVarName($trace, $call);

        //show the values (with variable names if available)
        foreach ( $trace[0]['args'] as $k => $v ) {
            EasyDump::showVar((self::$config['showVarNames']?$varNames[$k]:$k), $v);
        }

        echo '</pre>';
    }

    /**
     * For debug purpose only. Exits after dump
     * @param  misc    $variable the variable to dump
     */
    public static function debugExit() {
        call_user_func_array( array( __CLASS__, 'debug' ), func_get_args() );
        exit;
    }

    /**
     * For debug purpose only, used by debug()
     * Recursive (for arrays) function to display variable in a nice formated way
     * 
     * @param  string  $name           name/value of the variable's index
     * @param  misc    $value          value to display
     * @param  integer $level          for indentation purpose, used in recursion
     * @param  boolean $serializeArray force array serialization
     */
    protected static function showVar($name, $value, $level = 0, $dumpArray = false) {
        $indent = "    ";
        for($lvl = 0; $lvl < $level; $lvl++) { echo $indent; }
        echo '<span style="color:'.self::$config['color']['name'].';">'.($level == 0?$name:(is_string($name)?'"'.$name.'"':'['.$name.']'))." </span>";
        echo '<span style="color:'.self::$config['color']['type'].';">('.gettype($value).")</span>\t= ";
        if(is_array($value) && !$dumpArray && $level <= 5) {
            echo '{';
            if(!empty($value)) {
                echo "\r\n";
                foreach($value as $k => $v) {
                    self::showVar($k, $v, $level+1);
                }
            }
            for($lvl = 0; $lvl < $level; $lvl++) { echo $indent; }
            echo "}\r\n";
        } else {
            echo '<span style="color:'.self::$config['color']['value'].';">';
            if(is_object($value) || is_resource($value)) {
                ob_start();
                var_dump($value);
                $result = ob_get_clean();
                //trim the var_dump because EasyDump already handle the newline after dump
                echo trim($result);
            } elseif(is_array($value)) {
                echo serialize($value);
            } elseif(is_string($value)) {
                echo '"'.htmlentities($value).'"';
            } elseif(is_bool($value)) {
                echo $value?'true':'false';
            } elseif(is_null($value)) {
                echo 'NULL';
            } elseif(is_numeric($value)) {
                echo $value;
            } else {
                echo 'N/A';
            }
            echo "</span>\r\n";
        }
    }

    /**
     * Display the filename and line number where EasyDump was called
     * @param  array $call informations about the call
     */
    protected static function showCall($call) {
        echo "<span style=\"color:".self::$config['color']['type'].";\">File \"".$call['file']."\" line ".$call['line'].":</span>\r\n";
    }

    /**
     * Display the PHP code where EasyDump was called
     * useful for tracking lots of different calls with values/functions as parameters
     * @param  array $call informations about the call
     */
    protected static function showSource($call) {
        echo $call['formatedCode']
        ."\r\n"
        ."<span style=\"color:".self::$config['color']['type'].";\">Results:</span>"
        ."\r\n";
    }

    /**
     * Get the variable names used in the function call
     * 
     * @param  array  $trace trace of nested calls
     * @return array         list of variable names (if available)
     */
    protected static function guessVarName($trace, $call) {
        $varNames = array();
        
        $results = self::parse($call['code']);

        foreach($results as $k => $v) {
            $processString = trim($v);
            if(preg_match('/^\$/', $processString)) {
                $varNames[] = $processString;
            } elseif(is_numeric($processString)
                    || substr($processString, 0, 1) == "'"
                    || substr($processString, 0, 1) == '"'
                    || substr($processString, 0, 5) == 'array'
            ) {
                //TODO: not working for empty string
                $varNames[] = '[value]';
            } elseif(preg_match('([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)', $processString, $matches)) {
                $varNames[] = $processString;
            } else {
                $varNames[] = '[unknown]';
            }
        }

        return $varNames;
    }

    /**
     * Pars PHP code to extract comma separated elements into an array
     * @param  string $code PHP code
     * @return array        list of elements
     */
    protected static function parse($code) {
        $names = array();
        $currentName = '';

        $depth = 0;
        $escapeNext = false;
        $delimiter = array(
            '(' => ')',
            '[' => ']',
            '{' => '}',
        );
        $inQuotes = '';
        $inDelimiter = '';
        for($i = 0; $i < strlen($code); $i++) {
            $stackChar = true;
            if(!$escapeNext) {
                //escape char inside a string between single/double quotes
                if(!empty($inQuotes) && $code[$i] == '\\') {
                    $escapeNext = true;
                //leaving a quoted string
                } elseif(!empty($inQuotes) && $code[$i] == $inQuotes) {
                    $inQuotes = '';
                //entering a quoted string
                } elseif(empty($inQuotes) && ($code[$i] == '\'' || $code[$i] == '"')) {
                    $inQuotes = $code[$i];
                //recursive use of delimiter, add a level
                } elseif(!empty($inDelimiter) && $code[$i] == $inDelimiter) {
                    $depth++;
                //recursive use of delimiter, remove a level
                } elseif(!empty($inDelimiter) && $code[$i] == $delimiter[$inDelimiter]) {
                    $depth--;
                    //leaving the parent delimiter
                    if($depth == 0) {
                        $inDelimiter = '';
                    }
                //entering a parent delimiter
                } elseif(empty($inDelimiter) && array_key_exists($code[$i], $delimiter)) {
                    $inDelimiter = $code[$i];
                    $depth++;
                //a root, breaking comma
                } elseif(empty($inDelimiter) && empty($inQuotes) && $code[$i] == ',') {
                    $names[] = $currentName;
                    $currentName = '';
                    $stackChar = false;
                }
            } else {
                $escapeNext = false;
            }

            //add the char to the currently processed name
            if($stackChar) {
                $currentName .= $code[$i];
            }
        }

        //add the last name to the array
        $names[] = $currentName;
        return $names;
    }

    /**
     * Read informations from the backtrace and the PHP file about the call to EasyDump
     * This function uses SplFileObject, only available on PHP 5.1.0+
     * 
     * @param  array $trace backtrace executed PHP code
     * @return array        informations about the call
     */
    protected static function readCall($trace) {
        //called de()
        if(count($trace) >= 5
                && $trace[2]['function'] == 'debugExit'
                && $trace[4]['function'] == 'de'
        ) {
            $rank = 4;

        //called EasyDump::debugExit() or d()
        } elseif(count($trace) >= 3
                && ($trace[2]['function'] == 'debugExit'
                || $trace[2]['function'] == 'd')
        ) {
            $rank = 2;

        //called EasyDump::debug()
        } else {
            $rank = 0;
        }
        
        $line = --$trace[$rank]['line'];
        $file = new SplFileObject( $trace[$rank]['file'] );
        $file->seek( $line );
        $call = trim( $file->current() );
        $callMultiline = $file->current();

        //read the PHP file backward to the begining of the call
        $regex = '/'.$trace[$rank]['function'].'\((.*)\);$/';
        while( !preg_match($regex, $call, $match) ) {
            $file->seek( --$line );
            $call = trim( $file->current() ) . $call;
            $callMultiline = $file->current() . $callMultiline;
        }
        $call = $match[1];

        return array(
            'code' => $call,
            'formatedCode' => $callMultiline,
            'rank' => $rank,
            'line' => $line + 1,
            'file' => $trace[$rank]['file']
        );
    }
}

/**
 * Dump variable
 * Alias of EasyDump::debug()
 */
if ( !function_exists( 'd' ) ) {
    function d() {
        call_user_func_array( array( 'EasyDump', 'debug' ), func_get_args() );
    }
}

/**
 * Dump variable, then exit script
 * Alias of EasyDump::debugExit()
 */
if ( !function_exists( 'de' ) ) {
    function de() {
        call_user_func_array( array( 'EasyDump', 'debugExit' ), func_get_args() );
    }
}

?>