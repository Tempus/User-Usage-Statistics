<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * A Special Page extension to display user statistics
 *
 * @file
 * @ingroup Extensions
 *
 * @author Colin Noga, and Paul Grinberg
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'UserStats',
	'version'        => 'v1.0.0',
	'author'         => 'Colin Noga, with gratuitous code taken from Paul Grinberg',
	'email'          => '',
	'url'            => '',
	'descriptionmsg' => 'usagestatistics-desc',
);

$wgUserStatsGlobalRight = 'viewsystemstats';
$wgAvailableRights[] = 'viewsystemstats';

# define the permissions to view systemwide statistics
$wgGroupPermissions['*'][$wgUserStatsGlobalRight] = false;
$wgGroupPermissions['manager'][$wgUserStatsGlobalRight] = true;
$wgGroupPermissions['sysop'][$wgUserStatsGlobalRight] = true;

$dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles['UserStats'] = $dir . '/UsageStatistics.i18n.php';
$wgExtensionMessagesFiles['UserStatsAlias'] = $dir . 'UsageStatistics.alias.php';
$wgAutoloadClasses['SpecialUserStats'] = $dir . '/UsageStatistics_body.php';
$wgSpecialPages['SpecialUserStats'] = 'SpecialUserStats';
$wgSpecialPageGroups['SpecialUserStats'] = 'wiki';
