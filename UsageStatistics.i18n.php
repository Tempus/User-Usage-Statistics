<?php
/**
 * Internationalisation file for extension UsageStatistics.
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

$messages['en'] = array(
	'specialuserstats'                => 'Usage statistics',
	'usagestatistics'                 => 'Usage statistics',
	'usagestatistics-desc'            => 'Show individual user and overall wiki usage statistics',
	'usagestatisticsfor'              => '<h2>Usage statistics for [[User:$1|$1]]</h2>',
	'usagestatisticsforallusers'      => '<h2>Usage statistics for all users</h2>',
	'usagestatisticsinterval'         => 'Interval:',
	'usagestatisticsnamespace'		  => 'Namespace:',
	'usagestatisticsexcluderedirects' => 'Exclude redirects',
	'usagestatistics-namespace'       => 'These are statistics on the [[Special:Allpages/$1|$2]] namespace.',
	'usagestatistics-noredirects'     => '[[Special:ListRedirects|Redirects]] are not taken into account.',
	'usagestatisticstype'             => 'Type:',
	'usagestatisticsstart'            => 'Start date:',
	'usagestatisticsend'              => 'End date:',
	'usagestatisticssubmit'           => 'Generate statistics',
	'usagestatisticsnostart'          => 'No such user exists',
	'usagestatisticsnoend'            => 'Please specify an end date',
	'usagestatisticsbadstartend'      => '<b>Bad <i>start</i> and/or <i>end</i> date!</b>',
	'usagestatisticsintervalday'      => 'Day',
	'usagestatisticsintervalweek'     => 'Week',
	'usagestatisticsintervalmonth'    => 'Month',
	'usagestatisticsincremental'      => 'Incremental',
	'usagestatisticsincremental-text' => 'incremental',
	'usagestatisticscumulative'       => 'Cumulative',
	'usagestatisticscumulative-text'  => 'cumulative',
	'usagestatisticscalselect'        => 'Select',
	'usagestatistics-editindividual'  => 'Individual user $1 edits statistics',
	'usagestatistics-editpages'       => 'Individual user $1 pages statistics',
	'right-viewsystemstats'           => 'View [[Special:UserStats|wiki usage statistics]]',
);

/** Message documentation (Message documentation)
 * @author Darth Kule
 * @author EugeneZelenko
 * @author Fryed-peach
 * @author Jon Harald Søby
 * @author Lejonel
 * @author Purodha
 * @author Siebrand
 */
$messages['qqq'] = array(
	'specialuserstats' => '{{Identical|Usage statistics}}',
	'usagestatistics' => '{{Identical|Usage statistics}}',
	'usagestatistics-desc' => '{{desc}}',
	'usagestatisticsnamespace' => '{{Identical|Namespace}}',
	'usagestatisticstype' => '{{Identical|Type}}',
	'usagestatisticsstart' => '{{Identical|Start date}}',
	'usagestatisticsend' => '{{Identical|End date}}',
	'usagestatisticsintervalday' => '{{Identical|Day}}',
	'usagestatisticsintervalmonth' => '{{Identical|Month}}',
	'usagestatisticsincremental' => 'This message is used on [[Special:SpecialUserStats]] in a dropdown menu to choose to generate incremental statistics.

Incremental statistics means that for each interval the number of edits in that interval is counted, as opposed to cumulative statistics were the number of edits in the interval an all earlier intervals are counted.

{{Identical|Incremental}}',
	'usagestatisticsincremental-text' => 'This message is used as parameter $1 both in {{msg|Usagestatistics-editindividual}} and in {{msg|Usagestatistics-editpages}} ($1 can also be {{msg|Usagestatisticscumulative-text}}).

{{Identical|Incremental}}',
	'usagestatisticscumulative' => 'This message is used on [[Special:SpecialUserStats]] in a dropdown menu to choose to generate cumulative statistics.

Cumulative statistics means that for each interval the number of edits in that interval and all earlier intervals are counted, as opposed to incremental statistics were only the edits in the interval are counted.

{{Identical|Cumulative}}',
	'usagestatisticscumulative-text' => 'This message is used as parameter $1 both in {{msg|Usagestatistics-editindividual}} and in {{msg|Usagestatistics-editpages}} ($1 can also be {{msg|Usagestatisticsincremental-text}}).

{{Identical|Cumulative}}',
	'usagestatisticscalselect' => '{{Identical|Select}}',
	'usagestatistics-editindividual' => "Text in usage statistics graph. Parameter $1 can be either 'cumulative' ({{msg|Usagestatisticscumulative-text}}) or 'incremental' ({{msg|Usagestatisticsincremental-text}})",
	'usagestatistics-editpages' => "Text in usage statistics graph. Parameter $1 can be either 'cumulative' ({{msg|Usagestatisticscumulative-text}}) or 'incremental' ({{msg|Usagestatisticsincremental-text}})",
	'right-viewsystemstats' => '{{doc-right|viewsystemstats}}',
);
