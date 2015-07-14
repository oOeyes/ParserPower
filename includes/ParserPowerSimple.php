<?php

/**
 * 
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2013 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ParserPowerSimple {  
  /**
   * Registers the simple, generic parser functions with the parser.
   * @param Parser $parser The parser object being initialized.
   */
  static public function setup( &$parser ) {
    $parser->setFunctionHook( 'trim', 
                              'ParserPowerSimple::trimRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'uesc', 
                              'ParserPowerSimple::uescRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'trimuesc', 
                              'ParserPowerSimple::trimuescRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionTagHook( 'linkpage', 
                                 'ParserPowerSimple::linkpageRender',
                                 0
                               );
    $parser->setFunctionTagHook( 'linktext', 
                                 'ParserPowerSimple::linktextRender',
                                 0
                               );
    $parser->setFunctionTagHook( 'esc', 
                                 'ParserPowerSimple::escRender',
                                 0
                               );
    for ( $i = 1; $i < 10; ++$i ) {
      $parser->setFunctionTagHook( 'esc' . $i, 
                                   'ParserPowerSimple::escRender',
                                   0
                                 );
    }
  }

  /**
   * This function performs the trim operation for the trim parser function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function trimRender( $parser, $frame, $params ) {    
    return Array( isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '', 
                  'noparse' => false,
                );
  }
  
  /**
   * This function performs the unescape operation for the uesc parser function. This trims the value first, leaving
   * whitespace intact if it's there after escape sequences are replaced.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param string $text The text within the tag function.
   * @param Array $attribs Attributes values of the tag function.
   * @return Array The function output along with relevant parser options.
   */
  static public function uescRender( $parser, $frame, $params ) {    
    return Array( isset( $params[0] ) ? ParserPower::unescape( trim( $frame->expand( $params[0] ) ) ) : '', 
                  'noparse' => false,
                );
  }
  
  /**
   * This function performs the unescape operation for the trimuesc parser function. This trims the value after
   * replacement, so any leading or trailing whitespace is trimmed no matter how it got there.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function trimuescRender( $parser, $frame, $params ) {    
    return Array( isset( $params[0] ) ? trim( ParserPower::unescape( $frame->expand( $params[0] ) ) ) : '', 
                  'noparse' => false,
                );
  }
  
  /**
   * This function performs the delinking operation for the linktext parser function. This removes internal links from,
   * the given wikicode, replacing them with the name of the page they would have linked to.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param string $text The text within the tag function.
   * @param Array $attribs Attributes values of the tag function.
   * @return Array The function output along with relevent parser options.
   */
  static public function linkpageRender( &$parser, $frame, $text, $attribs ) {
    $text = $parser->replaceVariables( $text, $frame );
    
    if ( $text ) {
      return array( preg_replace_callback( '/\[\[(.*?)\]\]/', "self::linkpageReplace", $text ),
                    'noparse' => false
                  );
    } else {
      return '';
    }
  }
  
  /**
   * This function replaces the links found by linkpageRender and replaces them with the name of the page they link to.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $unexpandedParams The parameters and values together, not yet exploded or trimmed.
   * @return Array The function output along with relevent parser options.
   */
  static public function linkpageReplace( $matches ) {
    $parts = explode( '|', $matches[1], 2 );
    return $parts[0];
  }
  
  /**
   * This function performs the delinking operation for the linktext parser function. This removes internal links from,
   * the given wikicode, replacing them with the text that any links would return.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $unexpandedParams The parameters and values together, not yet exploded or trimmed.
   * @return Array The function output along with relevent parser options.
   */
  static public function linktextRender( &$parser, $frame, $text, $attribs ) {
    $text = $parser->replaceVariables( $text, $frame );
    
    if ( $text ) {
      return array( preg_replace_callback( '/\[\[(.*?)\]\]/', "self::linktextReplace", $text ),
                    'noparse' => false
                  );
    } else {
      return '';
    }
  }
  
  /**
   * This function replaces the links found by linktextRender and replaces them with their appropriate link text.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $unexpandedParams The parameters and values together, not yet exploded or trimmed.
   * @return Array The function output along with relevent parser options.
   */
  static public function linktextReplace( $matches ) {
    $parts = explode( '|', $matches[1], 2 );
    if ( count( $parts ) == 2 ) {
      return $parts[1];
    } else {
      return $parts[0];
    }
  }
  
  /**
   * This function escapes all appropriate characters in the given text and returns the result.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param string $text The text within the tag function.
   * @param Array $attribs Attributes values of the tag function.
   * @return Array The function output along with relevent parser options.
   */
  static public function escRender( &$parser, $frame, $text, $attribs ) {
    $text = ParserPower::escape( $text );
    
    $text = $parser->replaceVariables( $text, $frame );
    
    return Array( $text, 'noparse' => false );
  }
}

?>