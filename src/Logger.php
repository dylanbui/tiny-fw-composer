<?php

namespace TinyFw;

use TinyFw\Support\Config as ConfigSupport;

class Logger
{
    /**
     *
     * @Constructor is set to private to stop instantiation
     *
     */
    private function __construct()
    {
    }

    /**
     * to the logfile
     * @access public
     * @param	string	$function The name of the function called
     * @param 	array	$args	The array of args passed
     * @return	int	The number of bytes written, false other wise
     * @throws  \Exception
     */
    public static function __callStatic($function, $args)
    {
        // args[0] constains the error message
        // args[1] contains the log level
        // args[2] constains the filename
        // args[3] constains the line number
        $config = ConfigSupport::get('logging');
        if( $args[1] >= $config['log_level'] )
        {
            $line = array(
                'log_function'	=> $function,
                'log_message' 	=> $args[0],
                'log_level'	=> $args[1],
                'log_file'	=> $args[2],
                'log_line'	=> $args[3]);

            switch($config['log_handler'])
            {
                case 'file':
                    // set the log date/time
                    $line['log_time'] = date( DATE_ISO8601 );
                    // encode the line
                    $message = self::convertMesg($line);

                    $log_file = site_path(rtrim($config['log_dir'], '/'));
                    $log_file .= '/log-'.date('Y-m-d').'.log';

                    if ($handle = fopen($log_file, "a+"))
                    {
                        if( !fwrite($handle, $message) )
                        {
                            throw new \Exception("Unable to write to log file");
                        }
                        fclose($handle);
                    }
                    break;

                case 'database':
                    /* Xu ly insert log vao database*/
                    break;

                default:
                    throw new \Exception("Invalid Log Option");
            }
        }
    }

    private static function convertMesg($line)
    {
        $message = '['.date('Y-m-d H:i:s').'] - ';
        $message .= '['.$line['log_function'].'] - ';
        $message .= $line['log_message']."\n";
        return $message;

        // -- Encode the line to json for write and save other --
//        return json_encode($line)."\n";
    }

    /**
     *
     * Clone is set to private to stop cloning
     *
     */
    private function __clone()
    {
    }

} // end of log class

?>
