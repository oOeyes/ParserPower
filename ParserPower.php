<?php

/**
 * A collection of extended parser functions for MediaWiki, particularly including functions for dealing with lists of
 * values separated by a dynamically-specified delimiter.
 *
 * @addtogroup Extensions
 *
 * @link 
 *
 * @author Eyes <eyes@aeongarden.com>
 * @copyright Copyright � 2013 Eyes
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// If this is run directly from the web die as this is not a valid entry point.
if ( !defined( 'MEDIAWIKI' ) ) {
  echo "This is a MediaWiki extension and cannot run standalone.\n";
  die( -1 );
}

// Extension credits.
$wgExtensionCredits[ 'parserhook' ][] = array(
  'name'           => 'ParserPower',
  'url'            => 'http://sw.aeongarden.com/wiki/Extension:ParserPower', 
  'description'    => 'A collection of extended parser functions for MediaWiki, particularly including functions for '.
                      'dealing with lists of values separated by a dynamically-specified delimiter.',
  'descriptionmsg' => 'parserpower-desc',
  'author'         => '[http://www.mediawiki.org/wiki/User:OoEyes Shawn Bruckner]',
  'version'        => '1.0',
);

/**
 * Options:
 *
 * None so far! --
 *       Description here where there is at least one.
 */

/**
 * Perform setup tasks.
 */
$wgMessagesDirs['parserpower'] = dirname ( __FILE__ ) . '/i18n';
$wgExtensionMessagesFiles['ParserPowerMagic'] = dirname( __FILE__ ) . '/ParserPower.i18n.php';

$wgAutoloadClasses['ParserPower'] = dirname( __FILE__ ) . '/includes/ParserPower.php';
$wgAutoloadClasses['ParserPowerCompare'] = dirname( __FILE__ ) . '/includes/ParserPowerCompare.php';
$wgAutoloadClasses['ParserPowerSortKeyValueComparer'] = dirname( __FILE__ ) . 
                                                        '/includes/ParserPowerSortKeyValueComparer.php';
$wgAutoloadClasses['ParserPowerSimple'] = dirname( __FILE__ ) . '/includes/ParserPowerSimple.php';
$wgAutoloadClasses['ParserPowerLists'] = dirname( __FILE__ ) . '/includes/ParserPowerLists.php';

$wgHooks['ParserFirstCallInit'][] = 'ParserPower::setup';