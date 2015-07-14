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
   * Flag for case insensitive sorting. 0 as this is a default mode, and ignored in numeric sorts.
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
   * Registers the list handling parser functions with the parser.
   * @param Parser $parser The parser object being initialized.
   */
  static public function setup( &$parser ) {
    $parser->setFunctionHook( 'MAG_LSTSEP', 
                              'ParserPowerLists::lstsepRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LSTUNIQ', 
                              'ParserPowerLists::lstuniqRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LISTSORT', 
                              'ParserPowerLists::listsortRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LSTSRT', 
                              'ParserPowerLists::lstsrtRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LISTMAP', 
                              'ParserPowerLists::listmapRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LSTMAP', 
                              'ParserPowerLists::lstmapRender', 
                              SFH_OBJECT_ARGS 
                            );
    $parser->setFunctionHook( 'MAG_LSTMAPTEMP', 
                              'ParserPowerLists::lstmaptempRender', 
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
    if ( $sep == '' ) {
			$values = preg_split( '/(.)/u', $list, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		} else {
			$values = explode( $sep, $list );
		}
    
    return $values;
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
      if ( !ParserPower::isEmpty( $inValue ) ) {
        $outValues[] = ParserPower::unescape( trim( $inValue ) );
      }
    }
    
    return $outValues;
  }
  
  /**
   * This function directs the delimiter replacement option for the lstsep function.
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
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      $values = array_unique( $values );
      return Array( implode( $outSep, $values ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
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
   * Replaces the indicated token in the pattern with the input value.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inValue The value to change into one or more template parameters.
   * @param String $token The token to replace.
   * @param String $pattern Pattern containing token to be replaced with the input value.
   * @return The result of the token replacement within the pattern.
   */
  static private function applyPattern( $parser, $frame, $inValue, $token, $pattern ) {
    $inValue = trim( $inValue );
    
    if ( $inValue != '' ) {
      $outValue = $frame->expand( $pattern, PPFrame::NO_ARGS || PPFrame::NO_TEMPLATES );
      $outValue = str_replace( $token, $inValue, $outValue );
      $outValue = $parser->preprocessToDom( $outValue, $frame->isTemplate() ? Parser::PTD_FOR_INCLUSION : 0 );
      return ParserPower::unescape( trim( $frame->expand( $outValue ) ) );
    }
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
    $inValue = trim( $inValue );
    
    if ( $inValue != '' ) {
      $outValue = $frame->expand( $pattern, PPFrame::NO_ARGS || PPFrame::NO_TEMPLATES );
      $fields = explode( $fieldSep, $inValue, $tokenCount );
      $fieldCount = count( $fields );
      for ( $i = 0; $i < $tokenCount; $i++ ) {
        $outValue = str_replace( $tokens[$i], ( $i < $fieldCount ) ? $fields[$i] : '', $outValue );
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
                                                        $token,
                                                        $tokens,
                                                        $pattern
                                                      ) {
    $pairedValues = Array();
    if ( ( isset( $tokens ) && is_array( $tokens ) ) ) {
      $tokenCount = count( $tokens );
      foreach ( $values as $value ) {
        $pairedValues[] = Array( self::applyFieldPattern( $parser, 
                                                          $frame, 
                                                          $value, 
                                                          $fieldSep, 
                                                          $tokens, 
                                                          $tokenCount, 
                                                          $pattern
                                                        ),
                                 $value,
                               );
      }
    } else {
      foreach ( $values as $value ) {
        $pairedValues[] = Array( self::applyPattern( $parser, $frame, $value, $token, $pattern ) );
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
      $pairedValues = self::generateSortKeysByPattern( $parser, $frame, $values, $fieldSep, $token, $tokens, $pattern );
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
    
    if ( $inList !== '' ) {
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
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
      
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $values = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      if ( $duplicates === 'strip' ) {
        $values = array_unique( $values );
      }
      
      if ( $fieldSep !== '' && $tokenSep !== '' ) {
        $tokens = explode( $tokenSep, $token );
      }
      
      if ( $template !== '' || ( $token !== '' && $pattern !== '' ) ) {
        $values = self::sortListByKeys( $parser, 
                                        $frame, 
                                        $values, 
                                        $template,
                                        $fieldSep,
                                        $token,
                                        isset( $tokens ) ? $tokens : null,
                                        $pattern, 
                                        $sortOptions,
                                        $subsort,
                                        $subsortOptions
                                      );
        
      } else {
        $values = array_filter( self::explodeList( $inSep, $inList ), "ParserPower::isEmpty" );
        $values = self::sortList( $values, $sortOptions );
      }
      return Array( implode( $outSep, $values ), 'noparse' => false );
    } else {
      return Array( '', 'noparse' => false );
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
   * This function performs the sort option for the listm function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $token The token(s) in the pattern that represents where the list value should go.
   * @param String $tokenSep The separator between tokens if used.
   * @param String $pattern The pattern of text containing token that list values are inserted into at that token.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $duplicates When to strip duplicate values, if at all.
   * @return Array The function output along with relevant parser options.
   */
  static private function applyPatternToList( $parser, 
                                                $frame, 
                                                $inList, 
                                                $inSep,
                                                $fieldSep,
                                                $token, 
                                                $tokenSep,
                                                $pattern, 
                                                $outSep, 
                                                $sortMode, 
                                                $sortOptions,
                                                $duplicates
                                              ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      
      if ( $duplicates == 'strip' || $duplicates == 'prestrip' ) {
        $inValues = array_unique( $inValues );
      }
      
      if ( $sortMode == 'sort' || $sortMode == 'presort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      $outValues = Array();
      if ( $fieldSep !== '' && $tokenSep !== '' ) {
        $tokens = explode( $tokenSep, $token );
        $tokenCount = count( $tokens );
        foreach ( $inValues as $inValue ) {
          $outValue = self::applyFieldPattern( $parser, $frame, $inValue, $fieldSep, $tokens, $tokenCount, $pattern );
          if ( $outValue != '' ) {
            $outValues[] = $outValue;
          }
        }
      } else {
        foreach ( $inValues as $inValue ) {
          $outValue = self::applyPattern( $parser, $frame, $inValue, $token, $pattern );
          if ( $outValue != '' ) {
            $outValues[] = $outValue;
          }
        }
      }
      
      if ( $duplicates == 'strip' || $duplicates == "poststrip" ) {
        $outValues = array_unique( $outValues );
      }
      
      if ( $sortMode == 'sort' || $sortMode == 'postsort' ) {
        $outValues = self::sortList( $outValues, $sortOptions );
      }
      return Array( implode( $outSep, $outValues ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
    }
  }
  
  /**
   * This function performs the sort option for the listmtemp function.
   * @param Parser $parser The parser object.
   * @param PPFrame $frame The parser frame object.
   * @param String $inList The input list.
   * @param String $template The template to use.
   * @param String $inSep The delimiter seoarating values in the input list.
   * @param String $outSep The delimiter that should separate values in the output list.
   * @param String $sortMode A string indicating what sort mode to use, if any.
   * @param String $sortOptions A string of options for the sort as handled by #listsort.
   * @param String $duplicates When to strip duplicate values, if at all.
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
                                                 $duplicates
                                               ) {
    if ( $inList !== '' ) {
      $inSep = $parser->mStripState->unstripNoWiki( $inSep );
      
      $inValues = self::arrayTrimUnescape( self::explodeList( $inSep, $inList ) );
      if ( $duplicates == 'strip' || $duplicates == 'prestrip' ) {
        $inValues = array_unique( $inValues );
      }
      
      if ( $sortMode == 'sort' || $sortMode == 'presort' ) {
        $inValues = self::sortList( $inValues, $sortOptions );
      }
      
      $outValues = Array();
      foreach ( $inValues as $inValue ) {
        $outValues[] = self::applyTemplate( $parser, $frame, $inValue, $template, $fieldSep );
      }
      
      if ( $sortMode == 'postsort' ) {
        $outValues = self::sortList( $outValues, $sortOptions );
      }
      
      if ( $duplicates == 'strip' || $duplicates == "poststrip" ) {
        $outValues = array_unique( $outValues );
      }
      return Array( implode( $outSep, $outValues ), 'noparse' => false );
      
    } else {
      return Array( '', 'noparse' => false );
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
    
    if ( $inList !== '' ) {
      $template = isset( $params["template"] ) ? trim( $frame->expand( $params["template"] ) ) : '';
      $inSep = isset( $params["insep"] ) ? ParserPower::unescape( trim( $frame->expand( $params["insep"] ) ) ) : ',';
      $fieldSep = isset( $params["fieldsep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["fieldsep"] ) ) ) : '';
      $token = isset( $params["token"] ) ? 
               ParserPower::unescape( trim( $frame->expand( $params["token"], 
                                      PPFrame::NO_ARGS | PPFrame::NO_TEMPLATES ) ) 
                                    ) : 'x';
      $tokenSep = isset( $params["tokensep"] ) ? 
                  ParserPower::unescape( trim( $frame->expand( $params["tokensep"] ) ) ) : ',';
      $pattern = isset( $params["pattern"] ) ? $params["pattern"] : 'x';
      $outSep = isset( $params["outsep"] ) ? 
                ParserPower::unescape( trim( $frame->expand( $params["outsep"] ) ) ) : ', ';
      $sortMode = isset ( $params["sortmode"] ) ? 
                  strtolower( trim( $frame->expand( $params["sortmode"] ) ) ) : 'nosort';
      $sortOptions = isset( $params["sortoptions"] ) ? trim( $frame->expand( $params["sortoptions"] ) ) : '';
      $duplicates = isset ( $params["duplicates"] ) ? 
                    strtolower( trim( $frame->expand( $params["duplicates"] ) ) ) : 'keep';
      
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
                                          $duplicates
                                        );
      } else {
        return self::applyPatternToList( $parser, 
                                         $frame, 
                                         $inList, 
                                         $inSep, 
                                         $fieldSep,
                                         $token, 
                                         $tokenSep,
                                         $pattern, 
                                         $outSep, 
                                         $sortMode, 
                                         $sortOptions,
                                         $duplicates
                                       );
      }
    } else {
      return Array( '', 'noparse' => false );
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
                                       $token, 
                                       '',
                                       $pattern, 
                                       $outSep, 
                                       $sortMode, 
                                       $sortOptions,
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
                                        ''
                                      );
    } else {
      return Array( '', 'noparse' => false );
    }
  }
}

?>
