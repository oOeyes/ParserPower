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
          case "\\l":    $output .= "<";         break;
          case "\\g":    $output .= ">";         break;
          case "\\e":    $output .= "=";         break;
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
  
  /**
   * Replaces all appropriate characters with escape sequences.
   * @param string $input The string to escape.
   * @return string The escaped string.
   */
  static public function escape( $input ) {
    $output = '';
    for ( $i = 0; $i < strlen( $input ); ++$i ) {
      $char = substr( $input, $i, 1 );
      switch ( $char ) {
        case "\\":
          $sequence = substr( $input, $i, 2 );
          switch( $sequence ) {
            case "\\n":    $output .= "\\\\n";      $i += 1;   break;
            case "\\_":    $output .= "\\\\_";      $i += 1;   break;
            case "\\\\":   $output .= "\\\\\\\\";   $i += 1;   break;
            case "\\{":    $output .= "\\\\{";      $i += 1;   break;
            case "\\}":    $output .= "\\\\}";      $i += 1;   break;
            case "\\(":    $output .= "\\\\(";      $i += 1;   break;
            case "\\)":    $output .= "\\\\)";      $i += 1;   break;
            case "\\l":    $output .= "\\\\l";      $i += 1;   break;
            case "\\g":    $output .= "\\\\g";      $i += 1;   break;
            case "\\e":    $output .= "\\\\e";      $i += 1;   break;
            case "\\!":    $output .= "\\\\!";      $i += 1;   break;
            case "\\0":    $output .= "\\\\0";      $i += 1;   break;
            default:       $output .= "\\\\";                  break;
          }
          break;
        case "\n":   $output .= "\\n";   break;
        case " ":    $output .= "\\_";   break;
        case "{":    $output .= "\\{";   break;
        case "}":    $output .= "\\}";   break;
        case "[":    $output .= "\\(";   break;
        case "]":    $output .= "\\)";   break;
        case "<":    $output .= "\\l";   break;
        case ">":    $output .= "\\g";   break;
        case "=":    $output .= "\\e";   break;
        case "|":    $output .= "\\!";   break;
        default:     $output .= $char;   break;
      }
    }
    
    return $output;
  }
  
  /**
   * Replaces the indicated token in the pattern with the input value.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters.
   * @param String $token The token to replace.
   * @param String $pattern Pattern containing token to be replaced with the input value.
   * @return The result of the token replacement within the pattern.
   */
  static public function applyPattern( $parser, $frame, $inValue, $token, $pattern ) {
    return self::applyPatternWithIndex( $parser, $frame, $inValue, '', 0, $token, $pattern );
  }
  
  /**
   * Replaces the indicated index token in the pattern with the given index and the token in the pattern with the input
   * value.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters.
   * @param int $indexToken The token to replace with the index, or a null or empty value to skip index replacement.
   * @param int $index The numeric index of this value.
   * @param String $token The token to replace.
   * @param String $pattern Pattern containing token to be replaced with the input value.
   * @return The result of the token replacement within the pattern.
   */
  static public function applyPatternWithIndex( $parser, $frame, $inValue, $indexToken, $index, $token, $pattern ) {
    $inValue = trim( $inValue );
    if ( trim( $pattern ) !== '' ) { 
      $outValue = $frame->expand( $pattern, PPFrame::NO_ARGS || PPFrame::NO_TEMPLATES );
      if ( $indexToken !== null && $indexToken !== "" ) {
        $outValue = str_replace( $indexToken, strval( $index ), $outValue );
      }
      if ( $token !== null && $token !== '' ) {
        $outValue = str_replace( $token, $inValue, $outValue );
      }
    } else {
      $outValue = $inValue;
    }
    $outValue = $parser->preprocessToDom( $outValue, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
    return ParserPower::unescape( trim( $frame->expand( $outValue ) ) );
  }
  
  /**
   * Breaks the input value into fields and then replaces the indicated tokens in the pattern with those field values.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters
   * @param String $fieldSep The delimiter separating the fields in the value.
   * @param Array $tokens The list of tokens to replace.
   * @param int $tokenCount The number of tokens.
   * @param String $pattern Pattern containing tokens to be replaced by field values.
   * @return The result of the token replacement within the pattern.
   */
  static public function applyFieldPattern( $parser, 
                                            $frame, 
                                            $inValue,
                                            $fieldSep,
                                            $tokens, 
                                            $tokenCount, 
                                            $pattern 
                                          ) {
    return self::applyFieldPatternWithIndex( $parser, 
                                             $frame, 
                                             $inValue, 
                                             $fieldSep, 
                                             '', 
                                             0,
                                             $tokens, 
                                             $tokenCount, 
                                             $pattern
                                           );
  }
  
  /**
   * Replaces the index token with the given index, and then breaks the input value into fields and then replaces the 
   * indicated tokens in the pattern with those field values.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters
   * @param String $fieldSep The delimiter separating the fields in the value.
   * @param int $indexToken The token to replace with the index, or a null or empty value to skip index replacement.
   * @param int $index The numeric index of this value.
   * @param Array $tokens The list of tokens to replace.
   * @param int $tokenCount The number of tokens.
   * @param String $pattern Pattern containing tokens to be replaced by field values.
   * @return The result of the token replacement within the pattern.
   */
  static public function applyFieldPatternWithIndex( $parser, 
                                                     $frame, 
                                                     $inValue,
                                                     $fieldSep,
                                                     $indexToken,
                                                     $index,
                                                     $tokens, 
                                                     $tokenCount, 
                                                     $pattern 
                                                   ) {
    $inValue = trim( $inValue );
    if ( trim( $pattern ) !== '' ) { 
      $outValue = $frame->expand( $pattern, PPFrame::NO_ARGS || PPFrame::NO_TEMPLATES );
      if ( $indexToken !== null && $indexToken !== "" ) {
        $outValue = str_replace( $indexToken, strval( $index ), $outValue );
      }
      $fields = explode( $fieldSep, $inValue, $tokenCount );
      $fieldCount = count( $fields );
      for ( $i = 0; $i < $tokenCount; $i++ ) {
        $outValue = str_replace( $tokens[$i], ( $i < $fieldCount ) ? $fields[$i] : '', $outValue );
      }
    } else {
      $outValue = $inValue;
    }
    $outValue = $parser->preprocessToDom( $outValue, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
    return ParserPower::unescape( trim( $frame->expand( $outValue ) ) );
  }
}