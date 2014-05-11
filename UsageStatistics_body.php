<?php

class SpecialUserStats extends SpecialPage {

	function __construct() {
		parent::__construct( 'SpecialUserStats' );
	}

	function execute( $par ) {
		global $wgRequest, $wgOut, $wgUser;

		$this->setHeaders();
		$wgOut->setPagetitle( wfMsg( 'usagestatistics' ) );

		$user = $wgRequest->getVal( 'user', '' );
		$interval = $wgRequest->getVal( 'interval', '' );
		$namespace = $wgRequest->getVal('namespace', '' );
		$noredirects = $wgRequest->getCheck( 'noredirects' );
		$type = $wgRequest->getVal( 'type', '' );
		$start = $wgRequest->getVal( 'start', '' );
		$end = $wgRequest->getVal( 'end', '' );

		$db = wfGetDB( DB_SLAVE );

		if ($user == '')
			$user = $wgUser->getName();

		if ($namespace == '')
			$namespace = 0;

		$wgOut->addWikiMsg( 'usagestatisticsfor', $user );

		if ($interval == 'month')
			$interval = 2629744;
		else if ($interval == 'week')
			$interval = 604800;
		else
			$interval = 86400;

		if ( $start == '' ) {
			$conds = 'user_name is "' . $user . '"';
	
			$dr = $db->select(
				'user',
				array( 'user_registration' ),
				$conds,
				__METHOD__
			);

			$d = $db->fetchRow($dr)[0];
			
			preg_match( '/^(\d{4})(\d{2})(\d{2})/', $d, $matches );
			$start = "$matches[3]-$matches[2]-$matches[1]";
		}
		
		if ( $end == '' ) 
			$end = date("d-m-Y");


		self::displayForm( $user, $start, $end, $namespace, $noredirects );

		$conds = 'user_name is "' . $user . '"';
		$dr = $db->select(
			'user',
			array( 'user_id' ),
			$conds,
			__METHOD__
		);

		if ($db->fetchRow($dr)[0] == '') {
			$wgOut->addWikiText( '* <font color=red>' . wfMsg( 'usagestatisticsnostart' ) . '</font>' );
			return;
		}
		


		$this->getUserUsage( $db, $user, $start, $end, $interval, $namespace, $noredirects, $type );
		
	}

	function generate_google_chart( $dates, $edits, $pages ) {
		$x_labels = 3;
		$max_url = 2080; // this is a typical minimum limitation of many browsers

		$max_edits = max( $edits );
		$min_edits = min( $edits );
		$max_pages = max( $pages );
		$min_pages = min( $pages );

		if ( !$max_edits ) $max_edits = 1;
		if ( !$max_pages ) $max_pages = 1;

		$qry = 'http://chart.apis.google.com/chart?' . // base URL
			   'chs=400x275' .                         // size of the graph
			   '&cht=lc' .                             // line chart type
			   '&chxt=x,y,r' .                         // labels for x-axis and both y-axes
			   '&chco=ff0000,0000ff' .                 // specify the line colors
			   '&chxs=1,ff0000|2,0000ff' .             // specify the axis colors
			   '&chdl=Edits|Pages' .                   // specify the label
			   '&chxr=' .                              // start to specify the labels for the y-axes
			   "1,$min_edits,$max_edits|" .            // the edits axis
			   "2,$min_pages,$max_pages" .             // the pages axis
			   '&chxl=0:';                             // start specifying the x-axis labels
		foreach ( self::thin( $dates, $x_labels ) as $d ) {
			$qry .= "|$d";                             // the dates
		}
		$qry .= '&chd=t:';                             // start specifying the first data set
		$max_datapoints = ( $max_url - strlen( $qry ) ) / 2; // figure out how much space we have left for each set of data
		foreach ( self::thin( $edits, $max_datapoints / 5 ) as $e ) { // on avg, there are 5 chars per datapoint
			$qry .= sprintf( '%.1f,',
				100 * $e / $max_edits );                // the edits
		}
		$qry = substr_replace( $qry, '', - 1 );            // get rid of the unwanted comma
		$qry .= '|';                                   // start specifying the second data set
		foreach ( self::thin( $pages, $max_datapoints / 5 ) as $p ) { // on avg, there are 5 chars per datapoint
			$qry .= sprintf( '%.1f,',
				100 * $p / $max_pages );                // the pages
		}
		$qry = substr_replace( $qry, '', - 1 );            // get rid of the unwanted comma

		return $qry;
	}

	function thin( $input, $max_size ) {
		$ary_size = sizeof( $input );
		if ( $ary_size <= $max_size ) return $input;

		# we will always keep the first and the last point
		$prev_index = 0;
		$new_ary[] = $input[0];
		$index_increment = ( $ary_size - $prev_index - 2 ) / ( $max_size - 1 );

		while ( ( $ary_size - $prev_index - 2 ) >= ( 2 * $index_increment ) ) {
			$new_index = $prev_index + $index_increment;
			$new_ary[] = $input[(int)$new_index];
			$prev_index = $new_index;
		}

		$new_ary[] = $input[$ary_size - 1];

		// print_r($input);
		// print_r($new_ary);
		// print "size was " . sizeof($input) . " and became " . sizeof($new_ary) . "\n";

		return $new_ary;
	}

	function generate_google_chart_modern( $dates, $edits, $pages ){
		global $wgOut;
		$wgOut->addHTML('
		<head>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(init);


		function init() {
			var dataEdits = [
				["Date", "Edits"],
			');

		foreach ($dates as $i=>$d) {
			$wgOut->addHTML( "['$d', $edits[$i]],\n" );
		}

		$wgOut->addHTML('
			];

			var dataPages = [
				["Date", "Pages"],');

		foreach ($dates as $i=>$d) {
			$wgOut->addHTML( "['$d', $pages[$i]],\n" );
		}

		$wgOut->addHTML('
			];

			var data = [google.visualization.arrayToDataTable(dataEdits), google.visualization.arrayToDataTable(dataPages)]
			var current = 0;

			var options = {
				animation:{
				duration: 1000,
				easing: "out"
				},
			};

			var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
			var button = document.getElementById("b1");

			google.visualization.events.addListener(chart, "ready",
				function() {
					button.disabled = false;
					button.value = "Switch to " + (current ? "Edits" : "Pages");
				});

			function drawChart() {
				button.disabled = true;
				chart.draw(data[current], options);
			}

			button.onclick = function() {
				current = 1 - current;
				drawChart();
			}
	
			drawChart();
		}
		</script>
		</head>
		');

		$wgOut->addHTML( '<div id="chart_div" style="width: 1000px; height: 300px;"></div>');
		$wgOut->addHTML( '<form style="margin-left:450px;"><input id="b1" type="button" value="Switch to Pages"></form>');
	}

	function getUserUsage( $db, $user, $start, $end, $interval, $namespace, $noredirects, $type ) {
		global $wgOut, $wgUser, $wgUserStatsGlobalRight, $wgUserStatsGoogleCharts, $wgContLang;

		list( $start_d, $start_m, $start_y ) = explode( '-', $start );
		$start_t = mktime( 0, 0, 0, $start_m, $start_d, $start_y );
		list( $end_d, $end_m, $end_y ) = explode( '-', $end );
		$end_t = mktime( 0, 0, 0, $end_m, $end_d, $end_y );

		if ( $start_t >= $end_t ) {
			$wgOut->addWikiMsg( 'usagestatisticsbadstartend' );
			return;
		}
		if ( $namespace != 'all' ) {
			$nstext = $wgContLang->getNSText( $namespace );
			$displayns = $nstext;
			if ( $displayns == '' )
					$displayns = wfMsg( 'blanknamespace' );
			$wgOut->addWikiMsg( 'usagestatistics-namespace', $nstext, $displayns );
		}
		if ( $noredirects ) {
			$wgOut->addWikiMsg( 'usagestatistics-noredirects' );
		}
		$dates = array();
		$cur_t = $start_t;
		while ( $cur_t <= $end_t ) {
			$a_date = date( "Ymd", $cur_t ) . '000000';
			$dates[$a_date] = array();
			$cur_t += $interval;
		}
		# Let's process the edits that are recorded in the database
		$u = array();
		$conds = array( 'rev_page=page_id' );
		if ( $noredirects ) {
			$conds['page_is_redirect'] = 0;
		}

		$res = $db->select(
			array( 'page', 'revision' ),
			array( 'rev_user_text', 'rev_timestamp', 'page_id' ),
			$conds,
			__METHOD__
		);

		# Sort the DB results into the user tables
		for ( $j = 0; $j < $db->numRows( $res ); $j++ ) {
			$row = $db->fetchRow( $res );
			if ( !isset( $u[$row[0]] ) )
				$u[$row[0]] = $dates; 
			foreach ( $u[$row[0]] as $d => $v ) {
				if ( $d > $row[1] ) {
					if ( !isset( $u[$row[0]][$d][$row[2]] ) )
					$u[$row[0]][$d][$row[2]] = 0;
					$u[$row[0]][$d][$row[2]]++;
					break;
				}
			}
		}
		$db->freeResult( $res );

		# in case the current user is not already in the database
		if ( !isset( $u[$user] ) ) {
			$u[$user] = $dates;
		}

		# plot the user statistics
		$first = true;
		$e = 0;
		$p = 0;
		$ary_dates = array();
		$ary_edits = array();
		$ary_pages = array();
		foreach ( $u[$user] as $d => $v ) {
			$date = '';
			if ( preg_match( '/^(\d{4})(\d{2})(\d{2})/', $d, $matches ) )
				$date = "$matches[2]/$matches[3]/$matches[1]";
			if ( $type == 'incremental' ) {
				# the first data point includes all edits up to that date, so skip it
				if ( $first ) {
					$first = false;
					continue;
				}
				$e = 0;
				$p = 0;
			}
			foreach ( $v as $pageid => $edits ) {
				$p++;
				$e += $edits;
			}
			$ary_dates[] = $date;
			$ary_edits[] = $e;
			$ary_pages[] = $p;
		}

		// $wgOut->addHTML( '<img src="' .
		// 		self::generate_google_chart( $ary_dates, $ary_edits, $ary_pages ) .
		// 		'"/>' );

		self::generate_google_chart_modern( $ary_dates, $ary_edits, $ary_pages );

		return;

		// if ( !in_array( $wgUserStatsGlobalRight, $wgUser->getRights() ) )
		// 	return;

		// # plot overall usage statistics
		// $wgOut->addWikiMsg( 'usagestatisticsforallusers' );

		// $first = true;
		// $pages = 0;
		// $edits = 0;
		// $totals = array();
		// $ary_dates = array();
		// $ary_edits = array();
		// $ary_pages = array();
		// foreach ( $dates as $d => $v ) {
		// 	if ( $type == 'incremental' ) {
		// 		# the first data point includes all edits up to that date, so skip it
		// 		if ( $first ) {
		// 			$first = false;
		// 			continue;
		// 		}
		// 		$totals = array();
		// 	}
		// 	$date = '';
		// 	if ( preg_match( '/^(\d{4})(\d{2})(\d{2})/', $d, $matches ) )
		// 	$date = "$matches[2]/$matches[3]/$matches[1]";
		// 	foreach ( $u as $usr => $q )
		// 		foreach ( $u[$usr][$d] as $pageid => $numedits ) {
		// 			if ( !isset( $totals[$pageid] ) )
		// 			$totals[$pageid] = 0;
		// 			$totals[$pageid] += $numedits;
		// 		}
		// 	$pages = 0;
		// 	$edits = 0;
		// 	foreach ( $totals as $pageid => $e ) {
		// 		$pages++;
		// 		$edits += $e;
		// 	}
		// 	$ary_dates[] = $date;
		// 	$ary_edits[] = $edits;
		// 	$ary_pages[] = $pages;
		// }
		// return;
	}

	function displayForm( $user, $start, $end, $namespace, $noredirects ) {
		global $wgOut;

		$wgOut->addHTML( "
		<script type='text/javascript'>document.write(getCalendarStyles());</script>
		<form id=\"userstats\" method=\"get\">");

		$wgOut->addHTML(
				Xml::openElement( 'table', array( 'border' => '0' ) ) .
					Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . "User:" .
						Xml::closeElement( 'td' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-input' ) ) .
						"<input type='text' size='20' name='user' value='$user'/>"  .
						Xml::closeElement( 'td' ) .
						Xml::closeElement( 'td' ) .
					Xml::closeElement( 'tr' ) .
					Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . Xml::label( wfMsg( 'usagestatisticsnamespace' ), 'namespace' ) .
						Xml::closeElement( 'td' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-input' ) ) .
						Xml::namespaceSelector( $namespace, 'all' ) .
						Xml::closeElement( 'td' ) .
					Xml::closeElement( 'tr' ) .
					Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . wfMsg( 'usagestatisticsinterval' ) .
						Xml::closeElement( 'td' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-input' ) ) .
							Xml::openElement( 'select', array( 'name' => 'interval' ) ) .
							Xml::openElement( 'option', array( 'value' => 'day', 'selected' => 'selected' ) ) . wfMsg( 'usagestatisticsintervalday' ) .
							Xml::openElement( 'option', array( 'value' => 'week' ) ) . wfMsg( 'usagestatisticsintervalweek' ) .
							Xml::openElement( 'option', array( 'value' => 'month' )) . wfMsg( 'usagestatisticsintervalmonth' ) .
						Xml::closeElement( 'select' ) .
						Xml::closeElement( 'td' ) .
					Xml::closeElement( 'tr' ) .
					Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . wfMsg( 'usagestatisticstype' ) . Xml::closeElement( 'td' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-input' ) ) .
							Xml::openElement( 'select', array( 'name' => 'type' ) ) .
								Xml::openElement( 'option', array( 'value' => 'incremental', 'selected' => 'selected' ) ) . wfMsg( 'usagestatisticsincremental' ) .
								Xml::openElement( 'option', array( 'value' => 'cumulative' ) ) . wfMsg( 'usagestatisticscumulative' ) .
							Xml::closeElement( 'select' ) .
						Xml::closeElement( 'td' ) .
					Xml::closeElement( 'tr' ) .
						Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . Xml::label( wfMsg( 'usagestatisticsexcluderedirects' ), '' ) . Xml::closeElement( 'td' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-input' ) ) .
						Xml::check( 'noredirects', $noredirects ) .
						Xml::closeElement( 'td' ) .
					Xml::closeElement( 'tr' ) .
					Xml::openElement( 'tr' ) .
						Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . wfMsg( 'usagestatisticsstart' ) . Xml::closeElement( 'td' ) .
		"
		<td class='mw-input'>
		<input type='text' size='20'  name='start' value='$start'/>" . ' <i>DD-MM-YYYY</i>' .
						Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' ) .
						Xml::openElement( 'tr' ) .
							Xml::openElement( 'td', array( 'class' => 'mw-label' ) ) . wfMsg( 'usagestatisticsend' ) . Xml::closeElement( 'td' ) .
		"
		<td class='mw-input'>
		<input type='text' size='20'  name='end' value='$end'/>" . ' <i>DD-MM-YYYY</i>' .
						Xml::closeElement( 'td' ) . Xml::closeElement( 'tr' ) .
					Xml::closeElement( 'table' ) . 			"
		<input type='submit' value=\"" . wfMsg( 'usagestatisticssubmit' ) . "\" /> ".
				Xml::closeElement( 'form' ) ."

		<div id=\"testdiv1\" style=\"position:absolute;visibility:hidden;background-color:white;layer-background-color:white;\"></div>
			" );
	}

}
