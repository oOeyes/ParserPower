<?php

/**
 * 
 * 
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2013 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ParserPowerLists {
  /**
   * Flag for alphanumeric sorting. 0 as this is a default mode.
   */
  const SORT_ALPHA = 0;
  
  /**
   * Flag for numeric sorting.
   */
  const SORT_NUMERIC = 4;
  
  /**
   * Flag for case insensitive sorting. 0 as this is a default mode, and ignored in numeric sorts.
   */
  const SORT_NCS = 0;
  
  /**
   * Flag for case sensitive sorting. 0 as this is a default mode, and ignored in numeric sorts.
   */
  const SORT_CS = 2;
  
  /**
   * Flag for sorting in ascending order. 0 as this is a default mode.
   */
  const SORT_ASC = 0;
  
  /**
   * Flag for sorting in descending order.
   */
  const SORT_DESC = 1;
  
  /**
   * Flag for index search returning a positive index. 0 as this is a default mode.
   */
  const INDEX_POS = 0;
  
  /**
   * Flag for index search returning a negative index.
   */
  const INDEX_NEG = 4;
  
  /**
   * Flag for case insensitive index search. 0 as this is a default mode.
   */
  const INDEX_NCS = 0;
  
  /**
   * Flag for case sensitive index search.
   */
  const INDEX_CS = 2;
  
  /**
   * Flag for forward index search. 0 as this is a default mode.
   */
  const INDEX_ASC = 0;
  
  /**
   * Flag for reverse index search.
   */
  const INDEX_DESC = 1;
  
  /**
   * Flag for case insensitive item removal. 0 as this is a default mode.
   */
  const REMOVE_NCS = 0;
  
  /**
   * Flag for case sensitive item removal.
   */
  const REMOVE_CS = 1;
  
  /**
   * Registers the list handling parser functions with the parser.
   * @param Parser $parser The parser object being initialized.
   */
  static public function setup( &$parser ) {
    $parser->setFunctionHook( 'lstcnt', 
                              'ParserPowerLists::lstcntRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstsep', 
                              'ParserPowerLists::lstsepRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstelem', 
                              'ParserPowerLists::lstelemRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstsub', 
                              'ParserPowerLists::lstsubRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstfnd', 
                              'ParserPowerLists::lstfndRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstind', 
                              'ParserPowerLists::lstindRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstapp', 
                              'ParserPowerLists::lstappRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstprep', 
                              'ParserPowerLists::lstprepRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstjoin', 
                              'ParserPowerLists::lstjoinRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstcntuniq', 
                              'ParserPowerLists::lstcntuniqRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'listunique', 
                              'ParserPowerLists::listuniqueRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstuniq', 
                              'ParserPowerLists::lstuniqRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'listfilter', 
                              'ParserPowerLists::listfilterRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstfltr', 
                              'ParserPowerLists::lstfltrRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstrm', 
                              'ParserPowerLists::lstrmRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'listsort', 
                              'ParserPowerLists::listsortRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstsrt', 
                              'ParserPowerLists::lstsrtRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'listmap', 
                              'ParserPowerLists::listmapRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstmap', 
                              'ParserPowerLists::lstmapRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'lstmaptemp', 
                              'ParserPowerLists::lstmaptempRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'listmerge', 
                              'ParserPowerLists::listmergeRender', 
                              SFH_OBJECT_ARGS 
                            );
  }
  
  /**
   * This function splits a string of delimited values into an array by a given delimiter or default delimiters.
   * @param string $sep The delimiter used to separate the strings, or an empty string to use the default delimiters.
   * @param string $list The list in string format with values separated by the given or default delimiters.
   * @return Array The values in an array of strings.
   */
  static private function explodeList( $sep, $list ) {    
    if ( $sep === '' ) {
			$values = preg_split( '/(.)/u', $list, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values = explode( $sep, $list );
		}
    
    return $values;
  }
  
  /**
   * This function gets the specified element from the array after filtering out any empty values before it so that
   * the empty values are skipped in index counting. The returned element is unescaped.
   * @param Array $inIndex The 1-based index of the array element to get, or a negative value to start from the end.
   * @param Array $inValues The array to get the element from.
   * @return string The array element, trimmed and with character escapes replaced, or empty string if not found.
   */
  static private function arrayElementTrimUnescape( $inIndex, $inValues ) {
    if ( $inIndex > 0 ) {
      $curOutIndex = 1;
      for ( $curInIndex = 0; $curInIndex < count( $inValues ); ++$curInIndex ) {
        $trimmedValue = trim( $inValues[$curInIndex] );
        if ( !ParserPower::isEmpty( $trimmedValue ) ) {
          if ( $inIndex === $curOutIndex ) {
            return ParserPower::unescape( $trimmedValue );
          } else {
            ++$curOutIndex;
          }
        }
      }
    } else if ( $inIndex < 0 ) {
      $curOutIndex = -1;
      for ( $curInIndex = count( $inValues ) - 1; $curInIndex > -1; --$curInIndex ) {
        $trimmedValue = trim( $inValues[$curInIndex] );
        if ( !ParserPower::isEmpty( $trimmedValue ) ) {
          if ( $inIndex === $curOutIndex ) {
            return ParserPower::unescape( $trimmedValue );
          } else {
            --$curOutIndex;
          }
        }
      }
    }
    
    return "";
  }
  
  /**
   * This function trims whitespace each value while also filtering emoty values from the array, then slicing it 
   * according to specified offset and length. It also performs unescaping on each item. Note that values that are only 
   * empty after the unescape are preserved.
   * @param Array $
   * @param Array $inValues The array to trim, remove empty values from, slice, and unescape.
   * @return Array A new array with trimmed values, character escapes replaced, and empty values preunescape removed.
   */
  static private function arrayTrimSliceUnescape( $inOffset, $inLength, $inValues ) {
    $midValues = Array();
    
    foreach ( $inValues as $inValue ) {
      $trimmedValue = trim( $inValue );
      if ( !ParserPower::isEmpty( $trimmedValue ) ) {
        $midValues[] = $trimmedValue;
      }
    }
    
    if ( $inOffset > 0 ) {
      $offset = $inOffset - 1; 
    } else {
      $offset = $inOffset;
    }

    if ( $offset < 0 ) {
      $length = -$offset;
    } else {
      $length = count( $midValues ) - $offset;
    }
    if ( $inLength !== null ) {
      $length = intval( $inLength );
    }
    
    $midValues = array_slice( $midValues, $offset, $length );
    foreach ( $midValues as $midValue ) {
      $outValues[] = ParserPower::unescape( $midValue );
    }
    
    return $outValues;
  }
  
  /**
   * This function trims whitespace from the end of each value while also filter emoty values from the array. It also
   * performs unescaping on each item. Note that values that are only empty after the unescape are preserved.
   * @param Array $inValues The array to trim, unescape, and remove empty values from.
   * @return Array A new array with trimmed values, character escapes replaced, and empty values preunescape removed.
   */
  static private function arrayTrimUnescape( $inValues ) {
    $outValues = Array();
    
    foreach ( $inValues as $inValue ) {
      $trimmedValue = trim( $inValue );
      if ( !ParserPower::isEmpty( $trimmedValue ) ) {
        $outValues[] = ParserPower::unescape( $trimmedValue );
      }
    }
    
    return $outValues;
  }
  
  /**
   * This function directs the counting operation for the lstcnt function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstcntRender( $parser, $frame, $params ) {
    $list = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $list !== '' ) {
      $sep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      
      $sep = $parser->mStripState->unstripNoWiki( $sep );
      
      $count = count( self::arrayTrimUnescape( self::explodeList( $sep, $list ) ) );
      return Array( $count, 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the delimiter replacement operation for the lstsep function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstsepRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $outSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      return Array( implode( $outSep, $values ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the list element retrieval operation for the lstelem function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstelemRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $inIndex = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $value = "";
      if ( is_numeric( $inIndex ) ) {
        $value = self::arrayElementTrimUnescape( intval( $inIndex ), self::explodeList( $inSep, $inList ) );
      }
      
      return Array( $value, 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the list subdivision and delimiter replacement operation for the lstsub function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstsubRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $outSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
      $inOffset = isset( $params[3] ) ? ParserPower::unescape( trim( $frame->expand( $params[3] ) ) ) : '';
      $inLength = isset( $params[4] ) ? ParserPower::unescape( trim( $frame->expand( $params[4] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $offset = 0;
      if ( is_numeric( $inOffset ) ) {
        $offset = intval( $inOffset );
      }
      
      $length = null;
      if ( is_numeric( $inLength ) ) {
        $length = intval( $inLength );
      }
      
      $values = self::arrayTrimSliceUnescape( $offset, $length, self::explodeList( $inSep, $inList ) );
      
      if ( count( $values ) > 0 ) {
        return Array( implode( $outSep, $values ), 'noparse' => false );
      } else {
        return Array( '', 'noparse' => false );
      }
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the search operation for the lstfnd function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstfndRender( $parser, $frame, $params ) {
    $list = isset( $params[1] ) ? trim( $frame->expand( $params[1] ) ) : '';
    
    if ( $list !== '' ) {
      $item = isset( $params[0] ) ? ParserPower::unescape( trim( $frame->expand( $params[0] ) ) ) : '';
      $sep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : ',';
      $csOption = isset ( $params[3] ) ? 
                  strtolower( trim( $frame->expand( $params[3] ) ) ) : 'ncs';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $sep, $list ) );
      if ( $csOption === "cs" ) {
        foreach ( $values as $value ) {
          if ( $value === $item ) {
            return Array( $value, 'noparse' => false );
          }
        }
      } else {
        foreach ( $values as $value ) {
          if ( strtolower( $value ) === strtolower( $item ) ) {
            return Array( $value, 'noparse' => false );
          }
        }
      }
      return Array( '', 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the search operation for the lstind function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstindRender( $parser, $frame, $params ) {
    $list = isset( $params[1] ) ? trim( $frame->expand( $params[1] ) ) : '';
    
    if ( $list !== '' ) {
      $item = isset( $params[0] ) ? ParserPower::unescape( trim( $frame->expand( $params[0] ) ) ) : '';
      $sep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : ',';
      $inOptions = isset( $params[3] ) ? strtolower( trim( $frame->expand( $params[3] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      $options = self::indexOptionsFromParam( $inOptions );
      
      $values = self::arrayTrimUnescape( self::explodeList( $sep, $list ) );
      if ( $options & self::INDEX_DESC ) {
        if ( $options & self::INDEX_CS ) {
          for ( $index = count( $values ) - 1; $index > -1; --$index ) {
            if ( $values[$index] === $item ) {
              return Array( strval( ( $options & self::INDEX_NEG ) ? $index - count( $values )  : $index + 1 ), 
                            'noparse' => false 
                          );
            }
          }
        } else {
          for ( $index = count( $values ) - 1; $index > -1; --$index ) {
            if ( strtolower( $values[$index] ) === strtolower( $item ) ) {
              return Array( strval( ( $options & self::INDEX_NEG ) ? $index - count( $values )  : $index + 1 ), 
                            'noparse' => false 
                          );
            }
          }
        }
      } else {
        if ( $options & self::INDEX_CS ) {
          for ( $index = 0; $index < count( $values ); ++$index ) {
            if ( $values[$index] === $item ) {
              return Array( strval( ( $options & self::INDEX_NEG ) ? $index - count( $values )  : $index + 1 ), 
                            'noparse' => false 
                          );
            }
          }
        } else {
          for ( $index = 0; $index < count( $values ); ++$index ) {
            if ( strtolower( $values[$index] ) === strtolower( $item ) ) {
              return Array( strval( ( $options & self::INDEX_NEG ) ? $index - count( $values )  : $index + 1 ), 
                            'noparse' => false 
                          );
            }
          }
        }
      }
      return Array( '', 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function directs the append operation for the lstapp function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstappRender( $parser, $frame, $params ) {
    $list = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    $value = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
    
    if ( $list !== '' ) {
      $sep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      
      $sep = $parser->mStripState->unstripNoWiki( $sep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $sep, $list ) );
      if ( $value !== '' ) {
        $values[] = $value;
      }
      return Array( implode( $sep, $values ), 'noparse' => false );
      
    } else {
      return Array( $value, 'noparse' => false );
    }
  }
  
  /**
   * This function directs the prepend operation for the lstprep function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstprepRender( $parser, $frame, $params ) {
    $value = isset( $params[0] ) ? ParserPower::unescape( trim( $frame->expand( $params[0] ) ) ) : '';
    $list = isset( $params[2] ) ? trim( $frame->expand( $params[2] ) ) : '';
    
    if ( $list !== '' ) {
      $sep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      
      $sep = $parser->mStripState->unstripNoWiki( $sep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $sep, $list ) );
      if ( $value !== '' ) {
        array_unshift( $values, $value );
      }
      return Array( implode( $sep, $values ), 'noparse' => false );
      
    } else {
      return Array( $value, 'noparse' => false );
    }
  }
  
  /**
   * This function directs the joining operation for the lstjoin function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstjoinRender( $parser, $frame, $params ) {
    $inList1 = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    $inList2 = isset( $params[2] ) ? trim( $frame->expand( $params[2] ) ) : '';
    
    if ( $inList1 !== '' || $inList2 !== '' ) {
      if ( $inList1 !== '' ) {
        $inSep1 = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
        
        $inSep1 = $parser->mStripState->unstripNoWiki( $inSep1 );
        
        $values1 = self::arrayTrimUnescape( self::explodeList( $inSep1, $inList1 ) );
      } else {
        $values1 = array();
      }
      
      if ( $inList2 !== '' ) {
        $inSep2 = isset( $params[3] ) ? ParserPower::unescape( trim( $frame->expand( $params[3] ) ) ) : '';
        
        $inSep2 = $parser->mStripState->unstripNoWiki( $inSep2 );
        
        $values2 = self::arrayTrimUnescape( self::explodeList( $inSep2, $inList2 ) );
      } else {
        $values2 = array();
      }
      $outSep = isset( $params[4] ) ? ParserPower::unescape( trim( $frame->expand( $params[4] ) ) ) : '';
      
      return Array( implode( $outSep, array_merge( $values1, $values2 ) ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
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
  static private function applyPattern( $parser, $frame, $inValue, $token, $pattern ) {
    return ParserPower::applyPattern( $parser, $frame, $inValue, $token, $pattern );
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
  static private function applyPatternWithIndex( $parser, $frame, $inValue, $indexToken, $index, $token, $pattern ) {
    return ParserPower::applyPatternWithIndex( $parser, $frame, $inValue, $indexToken, $index, $token, $pattern );
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
  static private function applyFieldPattern( $parser, 
                                             $frame, 
                                             $inValue,
                                             $fieldSep,
                                             $tokens, 
                                             $tokenCount, 
                                             $pattern 
                                           ) {
    return ParserPower::applyFieldPattern( $parser, $frame, $inValue, $fieldSep, $tokens, $tokenCount, $pattern );
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
  static private function applyFieldPatternWithIndex( $parser, 
                                                      $frame, 
                                                      $inValue,
                                                      $fieldSep,
                                                      $indexToken,
                                                      $index,
                                                      $tokens, 
                                                      $tokenCount, 
                                                      $pattern 
                                                    ) {
    return ParserPower::applyFieldPatternWithIndex( $parser, 
                                                    $frame, 
                                                    $inValue, 
                                                    $fieldSep, 
                                                    $indexToken, 
                                                    $index, 
                                                    $tokens, 
                                                    $tokenCount, 
                                                    $pattern
                                                  );
  }
  
  /**
   * Wraps the given intro and outro around the given content after replacing a given count token in the intro or outro
   * with the given count.
   * @param string $intro The intro text.
   * @param string $content The inner content.
   * @param string $outro The outro test.
   * @param string $countToken The token to replace with count. Null or empty to skip.
   * @param int $count The count to replace the token with.
   * @return string The content wrapped by the intro and outro.
   */
  static private function applyIntroAndOutro( $intro, $content, $outro, $countToken, $count ) {
    if ( $countToken !== null && $countToken !== "" ) {
      $intro = str_replace( $countToken, strval( $count ), $intro );
      $outro = str_replace( $countToken, strval( $count ), $outro );
    }
    return $intro . $content . $outro;
  }
  
  /**
   * Turns the input value into one or more template parameters, processes the templates with those parameters, and
   * returns the result.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters.
   * @param String $template The template to pass the parameters to.
   * @param String $fieldSep The delimiter separating the parameter values.
   * @return The result of the template.
   */
  static private function applyTemplate( $parser, $frame, $inValue, $template, $fieldSep ) {
    $inValue = trim( $inValue );
    if ( $inValue != '' ) {
      if ( $fieldSep === '' ) {
        $outValue = $frame->virtualBracketedImplode( '{{', '|', '}}', $template, '1=' . $inValue );
      } else {
        $inFields = explode( $fieldSep, $inValue );
        $outFields = Array();
        $outFields[] = $template;
        for ( $i = 0; $i < count( $inFields ); $i++ ) {
          $outFields[] = ( $i + 1 ) . '=' . $inFields[$i];
        }
        $outValue = $frame->virtualBracketedImplode( '{{', '|', '}}', $outFields );
      }
      if ( $outValue instanceof PPNode_Hash_Array ) {
        $outValue = $outValue->value;
      }
      return $parser->replaceVariables( implode( '', $outValue ), $frame );
    }
  }

  /**
   * This function performs the filtering operation for the listfiler function when done by value inclusion.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $inValues Array with the input values.
   * @param String $values The list of values to include, not yet exploded.
   * @param String $valueSep The delimiter separating the values to include.
   * @param bool $valueCS true to match in a case-sensitive manner, false to match in a case-insensitive manner
   * @param bool $valueRE true to match based on a regex, false to match exactly
   * @return Array The function output along with relevant parser options.
   */
  static private function filterListByInclusion( $inValues, $values, $valueSep, $valueCS, $valueRE = 'no' ) {
    return self::filterListByInclusionOrExclusion( 'include', $inValues, $values, $valueSep, $valueCS, $valueRE );
  }
  
  /**
   * This function performs the filtering operation for the listfiler function when done by value exclusion.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $inValues Array with the input values.
   * @param String $values The list of values to exclude, not yet exploded.
   * @param String $valueSep The delimiter separating the values to exclude.
   * @param bool $valueCS true to match in a case-sensitive manner, false to match in a case-insensitive manner
   * @param bool $valueRE true to match based on a regex, false to match exactly
   * @return Array The function output along with relevant parser options.
   */
  static private function filterListByExclusion( $inValues, $values, $valueSep, $valueCS, $valueRE = 'no' ) {
    return self::filterListByInclusionOrExclusion( 'exclude', $inValues, $values, $valueSep, $valueCS, $valueRE );
  }
  
  /**
   * This function performs the filtering operation for the listfiler function by either inclusion or exclusion
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $method How to filter the values; "include" or "exclude".
   * @param Array $inValues Array with the input values.
   * @param String $values The list of values to include or exclude, not yet exploded.
   * @param String $valueSep The delimiter separating the values to include/exclude.
   * @param bool $valueCS true to match in a case-sensitive manner, false to match in a case-insensitive manner
   * @param bool $valueRE true to match based on a regex, false to match exactly
   * @return Array The function output along with relevant parser options.
   */
  static private function filterListByInclusionOrExclusion( $method, $inValues, $values, $valueSep, $valueCS, $valueRE ) {
      $equality_val = $method === 'include' ? true : false;

      if ( $valueRE !== 'no' ) {
        $valueSep = '';
      }

      if ( $valueSep !== '' ) {
        $ieValues = self::arrayTrimUnescape( self::explodeList( $valueSep, $values ) );
      } else {
        $ieValues = Array( ParserPower::unescape( trim( $values ) ) );
      }

      $outValues = Array();

      if ( $valueRE !== 'no' ) {
        $pattern = sprintf( '/%s/%s', $ieValues[0], $valueCS ? '' : 'i' );
        foreach ( $inValues as $inValue ) {
          if ( preg_match( $pattern, $inValue ) === intval( $equality_val ) ) {
            if ( $equality_val === true && $valueRE === 'strip' ) {
              $inValue = preg_replace( $pattern, '', $inValue );
            }
            $outValues[] = $inValue;
          }
        }
      } else {
        if ( $valueCS ) {
          foreach ( $inValues as $inValue ) {
            if ( in_array( $inValue, $ieValues ) === $equality_val ) {
              $outValues[] = $inValue;
            }
          }
        } else {
          $ieValues = array_map( 'strtolower', $ieValues );
          foreach ( $inValues as $inValue ) {
            if ( in_array( strtolower( $inValue ), $ieValues ) === $equality_val ) {
              $outValues[] = $inValue;
            }
          }
        }
      }
      
      return $outValues;
  }
  
  /**
   * This function performs the filtering operation for the listfilter function when done by pattern.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValues Array with the input values.
   * @param String $fieldSep Separator between fields, if any.
   * @param String $indexToken The token to replace with the current 1-based index of the element. Null/empty to skip.
   * @param String $token The token(s) in the pattern that represents where the list value should go.
   * @param String $tokenSep The separator between tokens if used.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @return Array The function output along with relevant parser options.
   */
  static private function filterFromListByPattern( $parser, 
                                                   $frame, 
                                                   $inValues,
                                                   $fieldSep,
                                                   $indexToken, 
                                                   $token, 
                                                   $tokenSep,
                                                   $pattern
                                                 ) {
    $outValues = Array();
    if ( $fieldSep !== '' && $tokenSep !== '' ) {
      $tokens = explode( $tokenSep, $token );
      $tokenCount = count( $tokens );
      $index = 1;
      foreach ( $inValues as $value ) {
        if ( trim( $value ) !== '' ) {
          $result = self::applyFieldPatternWithIndex( $parser, 
                                                      $frame, 
                                                      $value, 
                                                      $fieldSep, 
                                                      $indexToken, 
                                                      $index, 
                                                      $tokens, 
                                                      $tokenCount, 
                                                      $pattern 
                                                    );
          $result = strtolower( $parser->replaceVariables( ParserPower::unescape( trim( $result ) ), $frame ) );
          if ( $result !== "remove" ) {
            $outValues[] = $value;
          }
          ++$index;
        }
      }
    } else {
      $index = 1;
      foreach ( $inValues as $value ) {
        if ( trim( $value ) !== '' ) {
          $result = self::applyPatternWithIndex( $parser, $frame, $value, $indexToken, $index, $token, $pattern );
          $result = strtolower( $parser->replaceVariables( ParserPower::unescape( $result ), $frame ) );
          if ( $result !== "remove" ) {
            $outValues[] = $value;
          }
          ++$index;
        }
      }
    }

    return $outValues;
  }
  
  /**
   * This function performs the filtering operation for the listfilter function when done by template.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValues Array with the input values.
   * @param String $template The template to use.
   * @param String $fieldSep Separator between fields, if any.
   * @return Array The array stripped of any values with non-unique keys.
   */
  static private function filterFromListByTemplate( $parser, $frame, $inValues, $template, $fieldSep ) {
    $outValues = Array();
    foreach ( $inValues as $value ) {
      $result = self::applyTemplate( $parser, $frame, $value, $template, $fieldSep );
      if ( $value != '' && strtolower( $result ) !== "remove" ) {
        $outValues[] = $value;
      }
    }

    return $outValues;
  }
  
  /**
   * This function renders the listfilter function, sending it to the appropriate processing function based on what
   * parameter values are provided.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function listfilterRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params["list"] ) ? trim( $frame->expand( $params["list"] ) ) : '';
    $default = isset( $params["default"] ) ? ParserPower::unescape( trim( $frame->expand( $params["default"] ) ) ) : '';
    
    if ( $inList !== '' ) {
      $keepValues = isset( $params["keep"] ) ? trim( $frame->expand( $params["keep"] ) ) : '';
      $keepRE = isset( $params["keepre"] ) ? trim( $frame->expand( $params["keepre"] ) ) : 'no';
      $keepSep = isset( $params["keepsep"] ) ? trim( $frame->expand( $params["keepsep"] ) ) : ',';
      $keepCS = isset( $params["keepcs"] ) ? 
                strtolower( trim( $frame->expand( $params["keepcs"] ) ) ) : 
                'no';
      $removeValues = isset( $params["remove"] ) ? trim( $frame->expand( $params["remove"] ) ) : '';
      $removeRE = isset( $params["removere"] ) ? trim( $frame->expand( $params["removere"] ) ) : 'no';
      $removeSep = isset( $params["removesep"] ) ? trim( $frame->expand( $params["removesep"] ) ) : ',';
      $removeCS = isset( $params["removecs"] ) ? 
                  strtolower( trim( $frame->expand( $params["removecs"] ) ) ) : 
                  'no';
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $indexToken = isset( $params["indextoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["indextoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $token = isset( $params["token"] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params["token"], 
                                      PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                    ) : '';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $pattern = isset( $params["pattern"] ) ? $params["pattern"] : '';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $countToken = isset( $params["counttoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["counttoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $intro = isset( $params["intro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["intro"] ) ) ) : '';
      $outro = isset( $params["outro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["outro"] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      $tokenSep = $parser->mStripState->unstripNoWiki( $tokenSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );

      if ( $keepValues !== '' ) {
        $outValues = self::filterListByInclusion( $inValues, 
                                                  $keepValues, 
                                                  $keepSep, 
                                                  ( $keepCS === "yes" ),
                                                  $keepRE
                                                );
      } else if ( $removeValues !== '' ) {
        $outValues = self::filterListByExclusion( $inValues,
                                                  $removeValues, 
                                                  $removeSep, 
                                                  ( $removeCS === "yes" ),
                                                  $removeRE
                                                );
      } else if ( $template !== '' ) {
        $outValues = self::filterFromListByTemplate( $parser, $frame, $inValues, $template, $fieldSep );
      } else {
        $outValues = self::filterFromListByPattern( $parser, 
                                                    $frame, 
                                                    $inValues, 
                                                    $fieldSep, 
                                                    $indexToken,
                                                    $token, 
                                                    $tokenSep,
                                                    $pattern
                                                  );
      }
      
      if ( count( $outValues ) > 0 ) {
        $outList = implode( $outSep, $outValues );
        $count = strval( count( $outValues ) );
        return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
      } else {
        return Array( $default, 'noparse' => false );
      }
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
  
  /**
   * This function renders the lstfltr function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstfltrRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params[2] ) ? trim( $frame->expand( $params[2] ) ) : '';
    
    if ( $inList !== '' ) {
      $values = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
      $valueSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : ',';
      $inSep = isset( $params[3] ) ? ParserPower::unescape( trim( $frame->expand( $params[3] ) ) ) : ',';
      $outSep = isset( $params[4] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params[4] ) ) ) : ', ';
      $csOption = isset ( $params[5] ) ? 
                  strtolower( trim( $frame->expand( $params[5] ) ) ) : 'ncs';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      $outValues = self::filterListByInclusion( $inValues,
                                                $values, 
                                                $valueSep, 
                                                ( $csOption === "cs" )
                                              );
      
      if ( count( $outValues ) > 0 ) {
        return Array( implode( $outSep, $outValues ), 'noparse' => false );
      } else {
        return Array( '', 'noparse' => false );
      }
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function renders the lstrm function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstrmRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params[1] ) ? trim( $frame->expand( $params[1] ) ) : '';
    
    if ( $inList !== '' ) {
      $value = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
      $inSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : ',';
      $outSep = isset( $params[3] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params[3] ) ) ) : ', ';
      $csOption = isset ( $params[4] ) ? 
                  strtolower( trim( $frame->expand( $params[4] ) ) ) : 'ncs';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      $outValues = self::filterListByExclusion( $inValues, 
                                                $value, 
                                                '', 
                                                ( $csOption === "cs" )
                                              );
      
      if ( count( $outValues ) > 0 ) {
        return Array( implode( $outSep, $outValues ), 'noparse' => false );
      } else {
        return Array( '', 'noparse' => false );
      }
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function reduces an array to unique values.
   * @param Array $values The array of values to reduce to unique values.
   * @param bool $valueCS true to determine uniqueness case-sensitively, false to determine it case-insensitively
   * @return Array The function output along with relevant parser options.
   */
  static public function reduceToUniqueValues( $values, $valueCS ) {
    if ( $valueCS ) {
      return array_unique( $values );
    } else {
      return array_intersect_key( $values, array_unique( array_map( "strtolower", $values ) ) );
    }
  }
  
  /**
   * This function directs the counting operation for the lstcntuniq function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstcntuniqRender( $parser, $frame, $params ) {
    $list = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $sep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $csOption = isset ( $params[2] ) ? 
                  strtolower( trim( $frame->expand( $params[2] ) ) ) : 'ncs';
      
      $sep = $parser->mStripState->unstripNoWiki( $sep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $sep, $list ) );
      $values = self::reduceToUniqueValues( $values, $csOption === "cs" );
      return Array( strval( count( $values ) ), 'noparse' => false );
      
    } else {
      return Array( '0', 'noparse' => false );
    }
  }
  
  /**
   * Generates keys by replacing tokens in a pattern with the fields in the values, excludes any value that generates
   * any key generated by the previous values, and returns an array of the nonexcluded values.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $inValues The input list.
   * @param String $fieldSep Separator between fields, if any.
   * @param String $indexToken The token to replace with the current 1-based index of the element. Null/empty to skip.
   * @param String $token The token in the pattern that represents where the list value should go.
   * @param Array $tokens Or if there are mulitple fields, the tokens representing where they go.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @return Array An array with only values that generated unique keys via the given pattern.
   */
  static private function reduceToUniqueValuesByKeyPattern( $parser,
                                                            $frame,
                                                            $inValues,
                                                            $fieldSep,
                                                            $indexToken,
                                                            $token,
                                                            $tokens,
                                                            $pattern
                                                          ) {
    $previousKeys = Array();
    $outValues = Array();
    if ( ( isset( $tokens ) && is_array( $tokens ) ) ) {
      $tokenCount = count( $tokens );
      $index = 1;
      foreach ( $inValues as $value ) {
        if ( trim( $value ) !== '' ) {
          $key = self::applyFieldPatternWithIndex( $parser, 
                                                   $frame, 
                                                   $value, 
                                                   $fieldSep, 
                                                   $indexToken, 
                                                   $index, 
                                                   $tokens, 
                                                   $tokenCount, 
                                                   $pattern 
                                                 );
          $key = $parser->replaceVariables( ParserPower::unescape( $key ), $frame );
          if ( !in_array( $key, $previousKeys ) ) {
            $previousKeys[] = $key;
            $outValues[] = $value;
          }
          ++$index;
        }
      }
    } else {
      $index = 1;
      foreach ( $inValues as $value ) {
        if ( trim( $value ) !== '' ) {
          $key = self::applyPatternWithIndex( $parser, $frame, $value, $indexToken, $index, $token, $pattern );
          $key = $parser->replaceVariables( ParserPower::unescape( $key ), $frame );
          if ( !in_array( $key, $previousKeys ) ) {
            $previousKeys[] = $key;
            $outValues[] = $value;
          }
          ++$index;
        }
      }
    }
    
    return $outValues;
  }
  
  /**
   * Generates keys by turning the input value into one or more template parameters and processing that template, 
   * excludes any value that generates any key generated by the previous values, and returns an array of the 
   * nonexcluded values.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $inValues The input list.
   * @param String $fieldSep Separator between fields, if any.
   * @return Array An array with only values that generated unique keys via the given pattern.
   */
  static private function reduceToUniqueValuesByKeyTemplate( $parser, $frame, $inValues, $template, $fieldSep ) {
    $previousKeys = Array();
    $outValues = Array();
    foreach ( $inValues as $value ) {
      $key = self::applyTemplate( $parser, $frame, $value, $template, $fieldSep );
      if ( !in_array( $key, $previousKeys ) ) {
        $previousKeys[] = $key;
        $outValues[] = $value;
      }
    }
    
    return $outValues;
  }
  
  /**
   * This function renders the listunique function, sending it to the appropriate processing function based on what
   * parameter values are provided.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function listuniqueRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params["list"] ) ? trim( $frame->expand( $params["list"] ) ) : '';
    $default = isset( $params["default"] ) ? ParserPower::unescape( trim( $frame->expand( $params["default"] ) ) ) : '';
    
    if ( $inList !== '' ) {
      $uniqueCS = isset ( $params["uniquecs"] ) ? 
                  strtolower( trim( $frame->expand( $params["uniquecs"] ) ) ) : 'no';
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $indexToken = isset( $params["indextoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["indextoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $token = isset( $params["token"] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params["token"], 
                                      PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                    ) : '';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $pattern = isset( $params["pattern"] ) ? $params["pattern"] : '';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $countToken = isset( $params["counttoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["counttoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $intro = isset( $params["intro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["intro"] ) ) ) : '';
      $outro = isset( $params["outro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["outro"] ) ) ) : '';
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      if ( $fieldSep !== '' && $tokenSep !== '' ) {
        $tokens = explode( $tokenSep, $token );
      }
      
      if ( $template !== '' ) {
        $outValues = self::reduceToUniqueValuesByKeyTemplate( $parser, 
                                                              $frame, 
                                                              $inValues, 
                                                              $template,
                                                              $fieldSep
                                                            );
      } else if ( ( $indexToken !== '' || $token !== '' ) && $pattern !== '' ) {
        $outValues = self::reduceToUniqueValuesByKeyPattern( $parser, 
                                                             $frame, 
                                                             $inValues, 
                                                             $fieldSep, 
                                                             $indexToken,
                                                             $token, 
                                                             isset( $tokens ) ? $tokens : null,
                                                             $pattern
                                                           );
      } else {
        $outValues = self::reduceToUniqueValues( $inValues, $uniqueCS === "yes" );
      }
      $outList = implode( $outSep, $outValues );
      $count = strval( count( $outValues ) );
      return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
 
  /**
   * This function converts a string containing sort option keywords into an integer of sort option flags.
   * @param string $param The string containg sort options keywords.
   * @param int $default ANy flags that should be set by default.
   * @return int The flags representing the requested options.
   */
  static private function sortOptionsFromParam( $param, $default = 0 ) {
    $optionKeywords = explode( ' ', $param );
    $options = $default;
    foreach( $optionKeywords as $optionKeyword ) {
      switch ( strtolower( trim( $optionKeyword ) ) ) {
        case 'numeric':   $options |= self::SORT_NUMERIC;         break;
        case 'alpha':     $options &= ~self::SORT_NUMERIC;        break;
        case 'cs':        $options |= self::SORT_CS;              break;
        case 'ncs':       $options &= ~self::SORT_CS;             break;
        case 'desc':      $options |= self::SORT_DESC;            break;
        case 'asc':       $options &= ~self::SORT_DESC;           break;
      }
    }
    
    return $options;
  }
  
  /**
   * This function directs the duplicate removal function for the lstuniq function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstuniqRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $outSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
      $csOption = isset ( $params[3] ) ? 
                  strtolower( trim( $frame->expand( $params[3] ) ) ) : 'ncs';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      $values = self::reduceToUniqueValues( $values, $csOption === "cs" );
      return Array( implode( $outSep, $values ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
 
  /**
   * This function converts a string containing index option keywords into an integer of index option flags.
   * @param string $param The string containg index options keywords.
   * @param int $default ANy flags that should be set by default.
   * @return int The flags representing the requested options.
   */
  static private function indexOptionsFromParam( $param, $default = 0 ) {
    $optionKeywords = explode( ' ', $param );
    $options = $default;
    foreach( $optionKeywords as $optionKeyword ) {
      switch ( strtolower( trim( $optionKeyword ) ) ) {
        case 'neg':       $options |= self::INDEX_NEG;             break;
        case 'pos':       $options &= ~self::INDEX_NEG;            break;
        case 'cs':        $options |= self::INDEX_CS;              break;
        case 'ncs':       $options &= ~self::INDEX_CS;             break;
        case 'desc':      $options |= self::INDEX_DESC;            break;
        case 'asc':       $options &= ~self::INDEX_DESC;           break;
      }
    }
    
    return $options;
  }
  
  /**
   * This function sorts an array according to the parameters supplied.
   * @param Array $values An array of values to sort.
   * @param string $optionParam The sorting options parameter value as provided by the user.
   * @return Array The values in an array of strings.
   */
  static private function sortList( $values, $optionParam ) {
    $options = self::sortOptionsFromParam( $optionParam );
    
    if ( $options & self::SORT_NUMERIC ) {
      if ( $options & self::SORT_DESC ) {
        rsort( $values, SORT_NUMERIC );
        return $values;
      } else {
        sort( $values, SORT_NUMERIC );
        return $values;
      }
    } else {
      if ( $options & self::SORT_CS ) {
        if ( $options & self::SORT_DESC ) {
          rsort( $values, SORT_STRING );
          return $values;
        } else {
          sort( $values, SORT_STRING );
          return $values;
        }
      } else {
        if ( $options & self::SORT_DESC ) {
          usort( $values, 'ParserPowerCompare::rstrcasecmp' );
          return $values;
        } else {
          usort( $values, 'strcasecmp' );
          return $values;
        }
      }
    }
  }
  
  /**
   * Generates the sort keys by replacing tokens in a pattern with the fields in the values. This returns an array
   * of the values where each element is an array with the sort key in element 0 and the value in element 1.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $values The input list.
   * @param String $fieldSep Separator between fields, if any.
   * @param String $token The token in the pattern that represents where the list value should go.
   * @param Array $tokens Or if there are mulitple fields, the tokens representing where they go.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @return Array An array where each value has been paired with a sort key in a two-element array.
   */
  static private function generateSortKeysByPattern( $parser,
                                                     $frame,
                                                     $values,
                                                     $fieldSep,
                                                     $indexToken,
                                                     $token,
                                                     $tokens,
                                                     $pattern
                                                   ) {
    $pairedValues = Array();
    if ( ( isset( $tokens ) && is_array( $tokens ) ) ) {
      $tokenCount = count( $tokens );
      $index = 1;
      foreach ( $values as $value ) {
        if ( trim( $value ) !== '' ) {
          $key = self::applyFieldPatternWithIndex( $parser, 
                                                   $frame, 
                                                   $value, 
                                                   $fieldSep, 
                                                   $indexToken, 
                                                   $index, 
                                                   $tokens, 
                                                   $tokenCount, 
                                                   $pattern 
                                                 );
          $key = $parser->replaceVariables( ParserPower::unescape( $key ), $frame );
          $pairedValues[] = Array( $key, $value );
          ++$index;
        }
      }
    } else {
    foreach ( $values as $value ) {
        $index = 1;
        if ( trim( $value ) !== '' ) {
          $key = self::applyPatternWithIndex( $parser, $frame, $value, $indexToken, $index, $token, $pattern );
          $key = $parser->replaceVariables( ParserPower::unescape( $key ), $frame );
          $pairedValues[] = Array( $key, $value );
          ++$index;
        }
      }
    }
    
    return $pairedValues;
  }
  
  /**
   * Generates the sort keys by turning the input value into one or more template parameters and processing that 
   * template. This returns an array of the values where each element is an array with the sort key in element 0 and 
   * the value in element 1.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $values The input list.
   * @param String $fieldSep Separator between fields, if any.
   * @return Array An array where each value has been paired with a sort key in a two-element array.
   */
  static private function generateSortKeysByTemplate( $parser, $frame, $values, $template, $fieldSep ) {
    $pairedValues = Array();
    foreach ( $values as $value ) {
      $pairedValues[] = Array( self::applyTemplate( $parser, $frame, $value, $template, $fieldSep ), $value );
    }
    
    return $pairedValues;
  }
  
  /**
   * This takes an array where each element is an array with a sort key in element 0 and a value in element 1, and it
   * returns an array with just the values.
   * @param Array $pairedValues An array with values paired with sort keys.
   * @return Array An array with just the values.
   */
  static private function discardSortKeys( $pairedValues ) {
    $values = Array();
    
    foreach( $pairedValues as $pairedValue ) {
      $values[] = $pairedValue[1];
    }
    
    return $values;
  }
  
  /**
   * This takes an array where each element is an array with a sort key in element 0 and a value in element 1, and it
   * returns an array with just the sort keys wrapped in <nowiki> tags. Used for debugging purposes.
   * @param Array $pairedValues An array with values paired with sort keys.
   * @return Array An array with just the sort keys wrapped in <nowiki>..
   */
  static private function discardValues( $pairedValues ) {
    $values = Array();
    
    foreach( $pairedValues as $pairedValue ) {
      $values[] = '<nowiki>' . $pairedValue[0] . '</nowiki>';
    }
    
    return $values;
  }
  
  /**
   *
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $template The template to use.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $indexToken The token to replace with the current 1-based index of the element. Null/empty to skip.
   * @param String $token The token in the pattern that represents where the list value should go.
   * @param Array $tokens Or if there are mulitple fields, the tokens representing where they go.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @param String $sortOptions A string of options for the key sort as handled by #listsort.
   * @param String $subsort A string indicating whether to perform a value sort where sort keys are equal.
   * @param String $subsortOptions A string of options for the value sort as handled by #listsort.
   * @return Array An array where each value has been paired with a sort key in a two-element array.
   */
  static private function sortListByKeys( $parser,
                                          $frame,
                                          $values,
                                          $template,
                                          $fieldSep, 
                                          $indexToken,
                                          $token,
                                          $tokens,
                                          $pattern,
                                          $sortOptions,
                                          $subsort,
                                          $subsortOptions
                                        ) {
    if ( $template !== '' ) {
      $pairedValues = self::generateSortKeysByTemplate( $parser, $frame, $values, $template, $fieldSep );
    } else {
      $pairedValues = self::generateSortKeysByPattern( $parser, 
                                                       $frame, 
                                                       $values, 
                                                       $fieldSep, 
                                                       $indexToken, 
                                                       $token, 
                                                       $tokens, 
                                                       $pattern 
                                                     );
    }
    
    $comparer = new ParserPowerSortKeyValueComparer( self::sortOptionsFromParam( $sortOptions, self::SORT_NUMERIC ),
                                                     $subsort === 'yes',
                                                     self::sortOptionsFromParam( $subsortOptions )
                                                   );
    
    usort( $pairedValues, Array( $comparer, "compare" ) );
    
    return self::discardSortKeys( $pairedValues );
  }
  
  /**
   * This function directs the sort operation for the listsort function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function listsortRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params["list"] ) ? trim( $frame->expand( $params["list"] ) ) : '';
    $default = isset( $params["default"] ) ? ParserPower::unescape( trim( $frame->expand( $params["default"] ) ) ) : '';
    
    if ( $inList !== '' ) {
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $indexToken = isset( $params["indextoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["indextoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $token = isset( $params["token"] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params["token"], 
                                      PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                    ) : '';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $pattern = isset( $params["pattern"] ) ? $params["pattern"] : '';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $sortOptions = isset( $params["sortoptions"] ) ? trim( $frame->expand( $params["sortoptions"] ) ) : '';
      $subsort = isset ( $params["subsort"] ) ? 
                 strtolower( trim( $frame->expand( $params["subsort"] ) ) ) : 'no';
      $subsortOptions = isset( $params["subsortoptions"] ) ? trim( $frame->expand( $params["subsortoptions"] ) ) : '';
      $duplicates = isset ( $params["duplicates"] ) ? 
                    strtolower( trim( $frame->expand( $params["duplicates"] ) ) ) : 'keep';
      $countToken = isset( $params["counttoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["counttoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $intro = isset( $params["intro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["intro"] ) ) ) : '';
      $outro = isset( $params["outro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["outro"] ) ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      if ( $duplicates === 'strip' ) {
        $values = array_unique( $values );
      }
      
      if ( $fieldSep !== '' && $tokenSep !== '' ) {
        $tokens = explode( $tokenSep, $token );
      }
      
      if ( $template !== '' || ( ( $indexToken !== '' || $token !== '' ) && $pattern !== '' ) ) {
        $values = self::sortListByKeys( $parser, 
                                        $frame, 
                                        $values, 
                                        $template,
                                        $fieldSep, 
                                        $indexToken,
                                        $token,
                                        isset( $tokens ) ? $tokens : null,
                                        $pattern, 
                                        $sortOptions,
                                        $subsort,
                                        $subsortOptions
                                      );
        
      } else {
        $values = self::sortList( $values, $sortOptions );
      }
      
      if ( count( $values ) > 0 ) {
        $outList = implode( $outSep, $values );
        $count = strval( count( $values ) );
        return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
      } else {
        return Array( $default, 'noparse' => false );
      }
    } else {
      return Array( $default, 'noparse' => false );
    }
  }

  /**
   * This function directs the sort option for the lstsrt function.
   * @param Parser $parser The parser object. Ignored.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstsrtRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : '';
      $outSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : '';
      $sortOptions = isset( $params[3] ) ? trim( $frame->expand( $params[3] ) ) : '';
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      $values = self::sortList( $values, $sortOptions );
      return Array( implode( $outSep, $values ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function performs the pattern changing operation for the listmap function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $fieldSep The optional delimiter seoarating fields in each value.
   * @param String $indexToken The token to replace with the current 1-based index of the element. Null/empty to skip. 
   * @param String $token The token(s) in the pattern that represents where the list value should go.
   * @param String $tokenSep The separator between tokens if used.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $duplicates When to strip duplicate values, if at all.
   * @param String $countToken The token to replace with the list count. Null/empty to skip.
   * @param String $intro Content to include before outputted list values, only if at least one item is output.
   * @param String $outro Content to include after outputted list values, only if at least one item is output.
   * @param String $default Content to output if no list values are.
   * @return Array The function output along with relevant parser options.
   */
  static private function applyPatternToList( $parser, 
                                              $frame, 
                                              $inList, 
                                              $inSep,
                                              $fieldSep,
                                              $indexToken,
                                              $token, 
                                              $tokenSep,
                                              $pattern, 
                                              $outSep, 
                                              $sortMode, 
                                              $sortOptions,
                                              $duplicates, 
                                              $countToken,
                                              $intro,
                                              $outro,
                                              $default
                                            ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      if ( $duplicates === 'prestrip' || $duplicates === 'pre/poststrip' ) {
        $inValues = array_unique( $inValues );
      }
      
      if ( ( $indexToken !== "" && $sortMode === "sort" ) || $sortMode === 'presort' || $sortMode === 'pre/postsort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      $outValues = Array();
      $index = 1;
      if ( $fieldSep !== '' && $tokenSep !== '' ) {
        $tokens = explode( $tokenSep, $token );
        $tokenCount = count( $tokens );
        foreach ( $inValues as $inValue ) {
          if ( trim( $inValue ) !== '' ) {
            $outValue = self::applyFieldPatternWithIndex( $parser, 
                                                          $frame, 
                                                          $inValue, 
                                                          $fieldSep, 
                                                          $indexToken,
                                                          $index, 
                                                          $tokens, 
                                                          $tokenCount, 
                                                          $pattern 
                                                        );
            if ( $outValue !== '' ) {
              $outValues[] = $outValue;
              ++$index;
            }
          }
        }
      } else {
        foreach ( $inValues as $inValue ) {
          if ( trim( $inValue ) !== '' ) {
            $outValue = self::applyPatternWithIndex( $parser, $frame, $inValue, $indexToken, $index, $token, $pattern );
            if ( $outValue !== '' ) {
              $outValues[] = $outValue;
              ++$index;
            }
          }
        }
      }
      
      if ( $duplicates == 'strip' || $duplicates == 'poststrip' || $duplicates == 'pre/postsort' ) {
        $outValues = array_unique( $outValues );
      }
      
      if ( ( $indexToken === "" && $sortMode === "sort" ) || $sortMode == 'postsort' || $sortMode == 'pre/postsort' ) {
        $outValues = self::sortList( $outValues, $sortOptions );
      }
      
      if ( count( $outValues ) > 0 ) {
        if ( $countToken !== null && $countToken !== "" ) {
          $intro = str_replace( $countToken, strval( count( $outValues ) ), $intro );
          $outro = str_replace( $countToken, strval( count( $outValues ) ), $outro );
        }
        return Array( $intro . implode( $outSep, $outValues ) . $outro, 'noparse' => false );
      } else {
        return Array( $default, 'noparse' => false );
      }
      
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
  
  /**
   * This function performs the sort option for the listmtemp function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $template The template to use.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $fieldSep The optional delimiter seoarating fields in each value.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $duplicates When to strip duplicate values, if at all.
   * @param String $countToken The token to replace with the list count. Null/empty to skip.
   * @param String $intro Content to include before outputted list values, only if at least one item is output.
   * @param String $outro Content to include after outputted list values, only if at least one item is output.
   * @param String $default Content to output if no list values are.
   * @return Array The function output along with relevant parser options.
   */
  static private function applyTemplateToList( $parser, 
                                               $frame, 
                                               $inList,
                                               $template,
                                               $inSep,
                                               $fieldSep,
                                               $outSep,
                                               $sortMode,
                                               $sortOptions,
                                               $duplicates, 
                                               $countToken,
                                               $intro,
                                               $outro,
                                               $default
                                             ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      if ( $duplicates == 'prestrip' || $duplicates == 'pre/postsort' ) {
        $inValues = array_unique( $inValues );
      }
      
      if ( $sortMode == 'presort' || $sortMode == 'pre/postsort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      $outValues = Array();
      foreach ( $inValues as $inValue ) {
        $outValues[] = self::applyTemplate( $parser, $frame, $inValue, $template, $fieldSep );
      }
      
      if ( $sortMode == 'sort' || $sortMode == 'postsort' || $sortMode == 'pre/postsort' ) {
        $outValues = self::sortList( $outValues, $sortOptions );
      }
      
      if ( $duplicates == 'strip' || $duplicates == 'poststrip' || $duplicates == 'pre/postsort' ) {
        $outValues = array_unique( $outValues );
      }
      
      if ( count( $outValues ) > 0 ) {
        $outList = implode( $outSep, $outValues );
        $count = strval( count( $outValues ) );
        return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
      } else {
        return Array( $default, 'noparse' => false );
      }
      
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
  
  /**
   * This function renders the listmap function, sending it to the appropriate processing function based on what
   * parameter values are provided.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function listmapRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params["list"] ) ? trim( $frame->expand( $params["list"] ) ) : '';
    $default = isset( $params["default"] ) ? ParserPower::unescape( trim( $frame->expand( $params["default"] ) ) ) : '';
    
    if ( $inList !== '' ) {
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $indexToken = isset( $params["indextoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["indextoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $token = isset( $params["token"] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params["token"], 
                                      PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                    ) : '';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $pattern = isset( $params["pattern"] ) ? $params["pattern"] : '';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $sortMode = isset ( $params["sortmode"] ) ? 
                  strtolower( trim( $frame->expand( $params["sortmode"] ) ) ) : 'nosort';
      $sortOptions = isset( $params["sortoptions"] ) ? trim( $frame->expand( $params["sortoptions"] ) ) : '';
      $duplicates = isset ( $params["duplicates"] ) ? 
                    strtolower( trim( $frame->expand( $params["duplicates"] ) ) ) : 'keep';
      $countToken = isset( $params["counttoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["counttoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $intro = isset( $params["intro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["intro"] ) ) ) : '';
      $outro = isset( $params["outro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["outro"] ) ) ) : '';
      
      if ( $template !== '' ) {
        return self::applyTemplateToList( $parser, 
                                          $frame, 
                                          $inList, 
                                          $template, 
                                          $inSep,
                                          $fieldSep,
                                          $outSep, 
                                          $sortMode, 
                                          $sortOptions,
                                          $duplicates,
                                          $countToken,
                                          $intro,
                                          $outro,
                                          $default
                                        );
      } else {
        return self::applyPatternToList( $parser, 
                                         $frame, 
                                         $inList, 
                                         $inSep, 
                                         $fieldSep, 
                                         $indexToken,
                                         $token, 
                                         $tokenSep,
                                         $pattern, 
                                         $outSep, 
                                         $sortMode, 
                                         $sortOptions,
                                         $duplicates,
                                         $countToken,
                                         $intro,
                                         $outro,
                                         $default
                                       );
      }
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
  
  /**
   * This function performs the sort option for the listm function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstmapRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $inSep = isset( $params[1] ) ? ParserPower::unescape( trim( $frame->expand( $params[1] ) ) ) : ',';
      $token = isset( $params[2] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params[2], PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) ) : 
               'x';
      $pattern = isset( $params[3] ) ? $params[3] : 'x';
      $outSep = isset( $params[4] ) ? ParserPower::unescape( trim( $frame->expand( $params[4] ) ) ) : ', ';
      $sortMode = isset ( $params[5] ) ? strtolower( trim( $frame->expand( $params[5] ) ) ) : 'nosort';
      $sortOptions = isset( $params[6] ) ? trim( $frame->expand( $params[6] ) ) : '';
      
      return self::applyPatternToList( $parser, 
                                       $frame, 
                                       $inList, 
                                       $inSep, 
                                       '',
                                       '',
                                       $token, 
                                       '',
                                       $pattern, 
                                       $outSep, 
                                       $sortMode, 
                                       $sortOptions,
                                       '',
                                       '',
                                       '',
                                       '',
                                       ''
                                     );
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function performs the sort option for the lstmaptemp function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function lstmaptempRender( $parser, $frame, $params ) {
    $inList = isset( $params[0] ) ? trim( $frame->expand( $params[0] ) ) : '';
    
    if ( $inList !== '' ) {
      $template = isset( $params[1] ) ? trim( $frame->expand( $params[1] ) ) : '';
      $inSep = isset( $params[2] ) ? ParserPower::unescape( trim( $frame->expand( $params[2] ) ) ) : ',';
      $outSep = isset( $params[3] ) ? ParserPower::unescape( trim( $frame->expand( $params[3] ) ) ) : ', ';
      $sortMode = isset ( $params[4] ) ? strtolower( trim( $frame->expand( $params[4] ) ) ) : 'nosort';
      $sortOptions = isset( $params[5] ) ? trim( $frame->expand( $params[5] ) ) : '';
      
      return self::applyTemplateToList( $parser, 
                                        $frame, 
                                        $inList, 
                                        $template, 
                                        $inSep, 
                                        '',
                                        $outSep, 
                                        $sortMode, 
                                        $sortOptions,
                                        '',
                                        '',
                                        '',
                                        '',
                                        ''
                                      );
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * Breaks the input values into fields and then replaces the indicated tokens in the pattern with those field values.
   * This is for special cases when two sets of replacements are necessary for a given pattern.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue1 The first value to (potentially) split and replace tokens with
   * @param String $inValue2 The second value to (potentially) split and replace tokens with
   * @param String $fieldSep The delimiter separating the fields in the value.
   * @param Array $tokens1 The list of tokens to replace when performing the replacement for $inValue1.
   * @param Array $tokens2 The list of tokens to replace when performing the replacement for $inValue2.
   * @param String $pattern Pattern containing tokens to be replaced by field (or unsplit) values.
   * @return The result of the token replacement within the pattern.
   */
  static private function applyTwoSetFieldPattern( $parser, 
                                                   $frame, 
                                                   $inValue1,
                                                   $inValue2,
                                                   $fieldSep,
                                                   $tokens1, 
                                                   $tokens2,
                                                   $pattern 
                                                 ) {
    $inValue1 = trim( $inValue1 );
    $inValue2 = trim( $inValue2 );
    $tokenCount1 = count( $tokens1 );
    $tokenCount2 = count( $tokens2 );
    
    if ( $inValue1 != '' && $inValue2 != '' ) {
      $outValue = $frame->expand( $pattern, PPFrame::NO_ARGS || PPFrame::NO_TEMPLATES );
      if ( $inValue1 != '' ) {
        $fields = explode( $fieldSep, $inValue1, $tokenCount1 );
        $fieldCount = count( $fields );
        for ( $i = 0; $i < $tokenCount1; $i++ ) {
          $outValue = str_replace( $tokens1[$i], ( $i < $fieldCount ) ? $fields[$i] : '', $outValue );
        }
      }
      if ( $inValue2 != '' ) {
        $fields = explode( $fieldSep, $inValue2, $tokenCount2 );
        $fieldCount = count( $fields );
        for ( $i = 0; $i < $tokenCount2; $i++ ) {
          $outValue = str_replace( $tokens2[$i], ( $i < $fieldCount ) ? $fields[$i] : '', $outValue );
        }
      }
      $outValue = $parser->preprocessToDom( $outValue, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
      return ParserPower::unescape( trim( $frame->expand( $outValue ) ) );
    }
  }
  
  /**
   * Turns the input value into one or more template parameters, processes the templates with those parameters, and
   * returns the result.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue1 The first value to change into one or more template parameters.
   * @param String $inValue2 The second value to change into one of more template parameters.
   * @param String $template The template to pass the parameters to.
   * @param String $fieldSep The delimiter separating the parameter values.
   * @return The result of the template.
   */
  static private function applyTemplateToTwoValues( $parser, $frame, $inValue1, $inValue2, $template, $fieldSep ) {
    return self::applyTemplate( $parser, $frame, $inValue1 . $fieldSep . $inValue2, $template, $fieldSep );
  }
  
  /**
   * This function performs repeated merge passes until either the input array is merged to a single value, or until
   * a merge pass is completed that does not perform any further merges (pre- and post-pass array count is the same).
   * Each merge pass operates by performing a conditional on all possible pairings of items, immediately merging two
   * if the conditional indicates it should and reducing the possible pairings. The logic for the conditional and
   * the actual merge process is supplied through a user-defined function. 
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $inValues The input values, should be already exploded and fully preprocessed.
   * @param String $applyFunction Valid name of the function to call for both match and merge processes.
   * @param Array $matchParams An array of parameter values for the matching process, with open spots for the values.
   * @param Array $mergeParams An array of parameter values for the merging process, with open spots for the values.
   * @param int $valueIndex1 The index in $matchParams and $mergeParams where the first value is to go.
   * @param int $valueIndex2 The index in $matchParams and $mergeParams where the second value is to go.
   * @return Array The function output along with relevant parser options.
   */
  static private function iterativeListMerge( $parser, 
                                              $frame,  
                                              $inValues,  
                                              $applyFunction, 
                                              $matchParams, 
                                              $mergeParams, 
                                              $valueIndex1, 
                                              $valueIndex2 
                                            ) {
    $preValues = $inValues;
    $debug1 = $debug2 = $debug3 = 0;
    
    do {
      $postValues = array();
      $preCount = count( $preValues );
      
      while ( count( $preValues ) > 0 ) {
        $value1 = $matchParams[$valueIndex1] = $mergeParams[$valueIndex1] = array_shift( $preValues );
        $otherValues = $preValues;
        $preValues = array();

        while ( count( $otherValues ) > 0 ) {
          $value2 = $matchParams[$valueIndex2] = $mergeParams[$valueIndex2] = array_shift( $otherValues );
          $doMerge = call_user_func_array( $applyFunction, $matchParams );
          $doMerge = strtolower( $parser->replaceVariables( ParserPower::unescape( trim( $doMerge ) ), $frame ) );

          if ( $doMerge === 'yes' ) {
            $value1 = call_user_func_array( $applyFunction, $mergeParams );
            $value1 = $parser->replaceVariables( ParserPower::unescape( trim( $value1 ) ), $frame );
            $matchParams[$valueIndex1] = $mergeParams[$valueIndex1] = $value1;
          } else {
            $preValues[] = $value2;
          }
        }

        $postValues[] = $value1;
      }
      $postCount = count( $postValues );
      $preValues = $postValues;
    } while ( $postCount < $preCount && $postCount > 1 );
    
    return $postValues;
  } 
  
  /**
   * This function performs the pattern changing operation for the listmerge function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $fieldSep The optional delimiter seoarating fields in each value.
   * @param String $token1 The token(s) in the pattern that represents where the list value should go for item 1.
   * @param String $token2 The token(s) in the pattern that represents where the list value should go for item 2.
   * @param String $tokenSep The separator between tokens if used.
   * @param String $matchPattern The pattern of text containing token that determines if items match.
   * @param String $mergePattern The pattern of text containing token that list values are inserted into at that token.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $countToken The token to replace with the list count. Null/empty to skip.
   * @param String $intro Content to include before outputted list values, only if at least one item is output.
   * @param String $outro Content to include after outputted list values, only if at least one item is output.
   * @param String $default Content to output if no list values are.
   * @return Array The function output along with relevant parser options.
   */
  static private function mergeListByPattern( $parser, 
                                              $frame, 
                                              $inList, 
                                              $inSep,
                                              $fieldSep,
                                              $token1,
                                              $token2,
                                              $tokenSep,
                                              $matchPattern,
                                              $mergePattern,
                                              $outSep, 
                                              $sortMode, 
                                              $sortOptions,
                                              $countToken,
                                              $intro,
                                              $outro,
                                              $default
                                            ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      if ( $sortMode == 'presort' || $sortMode == 'pre/postsort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      if ( $tokenSep !== '' ) {
        $tokens1 = explode( $tokenSep, $token1 );
        $tokens2 = explode( $tokenSep, $token2 );
      } else {
        $tokens1 = Array( $token1 );
        $tokens2 = Array( $token2 );
      }
      
      $matchParams = Array( $parser, $frame, null, null, $fieldSep, $tokens1, $tokens2, $matchPattern );
      $mergeParams = Array( $parser, $frame, null, null, $fieldSep, $tokens1, $tokens2, $mergePattern );
      $outValues = self::iterativeListMerge( $parser,
                                             $frame,
                                             $inValues, 
                                             "ParserPowerLists::applyTwoSetFieldPattern", 
                                             $matchParams, 
                                             $mergeParams, 
                                             2, 
                                             3
                                           );
      
      if ( $sortMode == 'sort' || $sortMode == 'postsort' || $sortMode == 'pre/postsort' ) {
        $outValues = self::sortList( $outValues, $sortOptions );
      }
      
      if ( count( $outValues ) > 0 ) {
        $outList = implode( $outSep, $outValues );
        $count = strval( count( $outValues ) );
        return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
      } else {
        return Array( $default . "0 count", 'noparse' => false );
      }
      
    } else {
      return Array( $default . "no input", 'noparse' => false );
    }
  }
  
  /**
   * This function performs the template changing option for the listmerge function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $matchTemplate The template to use for the matching test.
   * @param String $mergeTemplate The template to use for the merging operation.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $fieldSep The optional delimiter seoarating fields in each value.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $countToken The token to replace with the list count. Null/empty to skip.
   * @param String $intro Content to include before outputted list values, only if at least one item is output.
   * @param String $outro Content to include after outputted list values, only if at least one item is output.
   * @param String $default Content to output if no list values are.
   * @return Array The function output along with relevant parser options.
   */
  static private function mergeListByTemplate( $parser, 
                                               $frame, 
                                               $inList,
                                               $matchTemplate,
                                               $mergeTemplate,
                                               $inSep,
                                               $fieldSep,
                                               $outSep,
                                               $sortMode,
                                               $sortOptions,
                                               $countToken,
                                               $intro,
                                               $outro,
                                               $default
                                             ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      if ( $sortMode == 'presort' || $sortMode == 'pre/postsort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      $matchParams = Array( $parser, $frame, null, null, $matchTemplate, $fieldSep );
      $mergeParams = Array( $parser, $frame, null, null, $mergeTemplate, $fieldSep );
      $outValues = self::iterativeListMerge( $parser,
                                             $frame,
                                             $inValues, 
                                             "ParserPowerLists::applyTemplateToTwoValues", 
                                             $matchParams, 
                                             $mergeParams, 
                                             2, 
                                             3
                                           );
      
      if ( count( $outValues ) > 0 ) {
        $outList = implode( $outSep, $outValues );
        $count = strval( count( $outValues ) );
        return Array( self::applyIntroAndOutro( $intro, $outList, $outro, $countToken, $count ), 'noparse' => false );
      } else {
        return Array( $default, 'noparse' => false );
      }
      
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
  
  /**
   * This function renders the listmerge function, sending it to the appropriate processing function based on what
   * parameter values are provided.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param Array $params The parameters and values together, not yet expanded or trimmed.
   * @return Array The function output along with relevant parser options.
   */
  static public function listmergeRender( $parser, $frame, $params ) {
    $params = ParserPower::arrangeParams( $frame, $params );
    
    $inList = isset( $params["list"] ) ? trim( $frame->expand( $params["list"] ) ) : '';
    $default = isset( $params["default"] ) ? ParserPower::unescape( trim( $frame->expand( $params["default"] ) ) ) : '';
    
    if ( $inList !== '' ) {
      $matchTemplate = isset( $params["matchtemplate"] ) ? trim( $frame->expand( $params["matchtemplate"] ) ) : '';
      $mergeTemplate = isset( $params["mergetemplate"] ) ? trim( $frame->expand( $params["mergetemplate"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $token1 = isset( $params["token1"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["token1"], 
                                       PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                     ) : '';
      $token2 = isset( $params["token2"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["token2"], 
                                       PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                     ) : '';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $matchPattern = isset( $params["matchpattern"] ) ? $params["matchpattern"] : '';
      $mergePattern = isset( $params["mergepattern"] ) ? $params["mergepattern"] : '';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $sortMode = isset ( $params["sortmode"] ) ? 
                  strtolower( trim( $frame->expand( $params["sortmode"] ) ) ) : 'nosort';
      $sortOptions = isset( $params["sortoptions"] ) ? trim( $frame->expand( $params["sortoptions"] ) ) : '';
      $countToken = isset( $params["counttoken"] ) ? 
                    ParserPower::unescape( trim( $frame->expand( $params["counttoken"], 
                                           PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                         ) : '';
      $intro = isset( $params["intro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["intro"] ) ) ) : '';
      $outro = isset( $params["outro"] ) ? ParserPower::unescape( trim( $frame->expand( $params["outro"] ) ) ) : '';
      
      if ( $matchTemplate !== '' && $mergeTemplate !== '' ) {
        return self::mergeListByTemplate( $parser, 
                                          $frame, 
                                          $inList, 
                                          $matchTemplate, 
                                          $mergeTemplate,
                                          $inSep,
                                          $fieldSep,
                                          $outSep, 
                                          $sortMode, 
                                          $sortOptions,
                                          $countToken,
                                          $intro,
                                          $outro,
                                          $default
                                        );
      } else {
        return self::mergeListByPattern( $parser, 
                                         $frame, 
                                         $inList, 
                                         $inSep, 
                                         $fieldSep,
                                         $token1, 
                                         $token2, 
                                         $tokenSep,
                                         $matchPattern,
                                         $mergePattern,
                                         $outSep, 
                                         $sortMode, 
                                         $sortOptions,
                                         $countToken,
                                         $intro,
                                         $outro,
                                         $default
                                       );
      }
    } else {
      return Array( $default, 'noparse' => false );
    }
  }
}
