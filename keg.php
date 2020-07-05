<?php
	if (!file_exists(__DIR__.'/includes/config.php')) {
		header('Location: install/index.php', true, 303);
		die();
	}
?>
<?php
	require_once __DIR__.'/admin/includes/managers/config_manager.php';
	require_once __DIR__.'/includes/config.php';
	require_once __DIR__.'/includes/common.php';
	require_once __DIR__.'/includes/keg.php';

	require_once __DIR__.'/admin/includes/managers/tap_manager.php';
	require_once __DIR__.'/admin/includes/managers/tempProbe_manager.php';
	require_once __DIR__.'/admin/includes/managers/bottle_manager.php';
	require_once __DIR__.'/admin/includes/managers/pour_manager.php'; 
		
	$plaatoPins = array(
	    "style" => 'v64',
	    "abv" => 'v68',
	    "og" => 'v65',
	    "fg" => 'v66',
	    "remainAmount" => 'v51',
	    "lastPour" => 'v47',
	    "temp" => 'v69'
	);
	$plaatoTemps = array();
	//This can be used to choose between CSV or MYSQL DB
	$db = true;
	
	// Setup array for all the beers that will be contained in the list
	$taps = array();
	$bottles = array();
	
	if($db){
		// Connect to the database
		$mysqli = db();		
		$config = getAllConfigs();
		
		$sql =  "SELECT * FROM vwGetActiveTaps";
		$qry = $mysqli->query($sql);
		while($b = mysqli_fetch_array($qry))
		{
			$beeritem = array(
				"id" => $b['id'],
				"beerId" => $b['beerId'],
				"beername" => $b['name'],
				"untID" => $b['untID'],
				"style" => $b['style'],
		        "brewery" => $b['breweryName'],
		        "breweryImage" => $b['breweryImageUrl'],
				"notes" => $b['notes'],
				"abv" => $b['abv'],
				"og" => $b['og'],
				"ogUnit" => $b['ogUnit'],
				"fg" => $b['fg'],
				"fgUnit" => $b['fgUnit'],
				"srm" => $b['srm'],
				"ibu" => $b['ibu'],
			    "startAmount" => $b['startAmount'],
			    "startAmountUnit" => $b['startAmountUnit'],
			    "remainAmount" => $b['remainAmount'],
				"remainAmountUnit" => $b['remainAmountUnit'],
				"tapRgba" => $b['tapRgba'],
				"tapNumber" => $b['tapNumber'],
				"rating" => $b['rating'],
				"srmRgb" => $b['srmRgb'],
				"valvePinState" => $b['valvePinState'],
				"plaatoAuthToken" => $b['plaatoAuthToken']
			);
			if($config[ConfigNames::UsePlaato]) {
    			if(isset($b['plaatoAuthToken']) && $b['plaatoAuthToken'] !== NULL && $b['plaatoAuthToken'] != '')
    			{
    			    foreach( $plaatoPins as $value => $pin)
    			    {
    			        $plaatoValue = file_get_contents("http://plaato.blynk.cc/".$b['plaatoAuthToken']."/get/".$pin);
    			        $plaatoValue = substr($plaatoValue, 2, strlen($plaatoValue)-4);
    			        if( $value == 'fg' || $value == 'og' ) $plaatoValue = $plaatoValue/1000;
    			        if( $value == "temp"){
    			            if($config[ConfigNames::UsePlaatoTemp])
    			            {
    			                $tempInfo["tempUnit"] = (strpos($plaatoValue,"C")?UnitsOfMeasure::TemperatureCelsius:UnitsOfMeasure::TemperatureFahrenheight);
    			                $tempInfo["temp"] = substr($plaatoValue, 0, strpos($plaatoValue, '�'));
    			                $tempInfo["probe"] = $b['id'];
    			                $tempInfo["takenDate"] = date('Y-m-d H:i:s');
    			                array_push($plaatoTemps, $tempInfo);
    			            }
    			            //echo $value."=http://plaato.blynk.cc/".$b['plaatoAuthToken']."/get/".$pin."-".$plaatoTemp.'-'.$plaatoValue.'<br/>';
        			    }else{
        			        if( $plaatoValue !== NULL && $plaatoValue != '') $beeritem[$value] = $plaatoValue;
    			            //echo $value."=http://plaato.blynk.cc/".$b['plaatoAuthToken']."/get/".$pin."-".$beeritem[$value].'-'.$plaatoValue.'<br/>';
        			    }
    			        
    			    }
    			}
			}
			$taps[$b['id']] = $beeritem;
		}
		
		
		$tapManager = new TapManager();
		$numberOfTaps = $tapManager->getNumberOfTaps();

		$sql =  "SELECT * FROM vwGetFilledBottles";
    	$rowNumber = 1;
		$qry = $mysqli->query($sql);
		while($b = mysqli_fetch_array($qry))
		{
			$beeritem = array(
				"id" => $b['id'],
				"beerId" => $b['beerId'],
				"beername" => $b['name'],
				"untID" => $b['untID'],
				"style" => $b['style'],
		        "brewery" => $b['breweryName'],
		        "breweryImage" => $b['breweryImageUrl'],
				"notes" => $b['notes'],
			    "abv" => $b['abv'],
			    "og" => $b['og'],
			    "ogUnit" => $b['ogUnit'],
			    "fg" => $b['fg'],
			    "fgUnit" => $b['fgUnit'],
				"srm" => $b['srm'],
				"ibu" => $b['ibu'],
			    "volume" => $b['volume'],
			    "volumeUnit" => $b['volumeUnit'],
				"startAmount" => $b['startAmount'],
				"amountPoured" => $b['amountPoured'],
				"remainAmount" => $b['remainAmount'],
				"capRgba" => $b['capRgba'],
				"capNumber" => $b['capNumber'],
				"rating" => $b['rating'],
				"srmRgb" => $b['srmRgb'],
				"valvePinState" => $b['valvePinState']
			);
			$bottles[$rowNumber] = $beeritem;
      		$rowNumber = $rowNumber+1;
		}
		$bottleManager = new BottleManager();
    	//$bottleManager->UpdateCounts();
		$numberOfBottles = $bottleManager->getCount();
		
		$numberOfPours = 0;
		if($config[ConfigNames::ShowPourListOnHome]){
    		$poursManager = new PourManager();
    		$page = 1;
    		$limit = $config[ConfigNames::NumberOfDisplayPours];
    		$totalRows = 0;
    		$poursList = $poursManager->getLastPours($page, $limit, $totalRows);
    		$numberOfPours = count($poursList);
		}
	}
		
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>RaspberryPints</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<!-- Set location of Cascading Style Sheet -->
		<link rel="stylesheet" type="text/css" href="style.css">
		
		<?php if($config[ConfigNames::UseHighResolution]) { ?>
			<link rel="stylesheet" type="text/css" href="style-high-res.css">
		<?php } ?>
		
		<?php	
		if(! empty($_SERVER['HTTP_USER_AGENT'])){
    		$useragent = $_SERVER['HTTP_USER_AGENT'];
    		if( preg_match('@(Android)@', $useragent) ){ ?>
			<link rel="stylesheet" type="text/css" href="style-aftv.css">
    	<?php	    
    		}
		} ?>
		
		<link rel="shortcut icon" href="img/pint.ico">
<!-- <meta name="viewport" content="initial-scale=0.7,width=device-width,height=device-height,target-densitydpi=device-dpi,user-scalable=yes" />  -->		
		<script type="text/javascript" src="admin/scripts/ws.js"></script>	
    <style>
        body { background-image: url('img/beer-mugs.jpg'); }
        table { width  : 99%; }
        table h1 { font-size: 6em; color: rgb(0,0,200); -webkit-text-stroke-width: 4px; -webkit-text-stroke-color: yellow; }
        table h3 { color: yellow; }
    </style>

	</head> 

<!--<body> -->
<body onload="wsconnect(); <?php if($config[ConfigNames::ShowTempOnMainPage])echo "setTimeout(function(){window.location.reload(1);}, 60000);"; ?>">
		<div class="bodywrapper" id="mainTable">
			
			<?php 
				if($numberOfTaps > 0)printKegList($taps, $numberOfTaps, ConfigNames::CONTAINER_TYPE_KEG);
			?>
		</div>
		<!-- <div class="copyright">Data provided by <a href="http://untappd.com">Untappd</a></div> -->
		
		<?php if($config[ConfigNames::DisplayRowsSameHeight]) { ?>
		<script type="text/javascript">
		window.onload = function(){
			tables = document.getElementsByTagName("table")
			for (var i = 0; i < tables.length; i++) {
			    var table = tables[i];		
				maxHeight = -1;
				//Start at 1 to avoid header row
				for (var j = 1; j < table.rows.length; j++) {
				    var row = table.rows[j];		
					if( row.offsetHeight > maxHeight ) maxHeight = row.offsetHeight;
				}
				if( maxHeight > 0 ){
    				for (var j = 1; j < table.rows.length; j++) {
    				    var row = table.rows[j];	
    					row.style.height = maxHeight + 'px';
    				}
				}
			}

			wsconnect();

			<?php if($config[ConfigNames::ShowTempOnMainPage])echo "setTimeout(function(){window.location.reload(1);}, 60000);"; ?>
		}
		</script>
		<?php } ?>
	</body>
</html>
