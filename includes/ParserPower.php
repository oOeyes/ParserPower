<?php

/**
 * 
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2013 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ParserPower {
  /**
   * Calls all parser function registrations functions.
   * @param Parser $parser The parser object being initialized.
   * @return bool true to indicate no problems.
   */
  static public function setup( &$parser ) {
    ParserPowerSimple::setup( $parser );
    ParserPowerLists::setup( $parser );
    return true;
  }
  
  /**
   * This function converts the parameters to the parser function into an array form with all parameter values
   * trimmed, as per longstanding MediaWiki conventions.
   * @param PPFrame $frame The parser frame object.
   * @param Array $unexpandedParams The parameters and values together, not yet exploded or trimmed.
   * @return Array The parameter values associated with the appropriate named or numbered keys
   */
  static public function arrangeParams( $frame, $unexpandedParams ) {
    $params = Array();
    foreach ( $unexpandedParams as $unexpandedParam ) {
      $param = explode( '=', trim( $frame->expand( $unexpandedParam ) ), 2 );
      if ( count( $param ) == 2 ) {
        $params[$param[0]] = $param[1];
      } else {
        $params[] = $param[0];
      }
    }
    
    return $params;
  }
  
  /**
   * The function returns tests a value to see that isn't null or an empty string.
   * @param String $value The value to check.
   * @return bool true for a value that is not null or an empty string. 
   */
  static public function isEmpty( $value ) {    
    return $value === null || $value === '';
  }
  
  /**
   * Replaces all escape sequences with the appropriate characters. It should be calling *after* trimming strings to
   * protect any leading or trailing whitespace that was escaped.
   * @param string $input The string to escape.
   * @return string The string with all escape sequences replaced.
   */
  static public function unescape( $input ) {
    $output = '';
    for ( $i = 0; $i < strlen( $input ); ++$i ) {
      $char = substr( $input, $i, 1 );
      if ( $char === "\\" ) {
        $sequence = substr( $input, $i, 2 );
        switch( $sequence ) {
          case "\\n":    $output .= "\n";        break;
          case "\\_":    $output .= " ";         break;
          case "\\\\":   $output .= "\\";        break;
          case "\\{":    $output .= "{";         break;
          case "\\}":    $output .= "}";         break;
          case "\\(":    $output .= "[";         break;
          case "\\)":    $output .= "]";         break;
          case "\\!":    $output .= "|";         break;
          case "\\0":    $output .= "";          break;
          default:       $output .= $sequence;   break;
        }
        $i += 1;
      } else {
        $output .= $char;
      }
    }
    
    return $output;
  }
}

?>

