<?php
	require_once __DIR__.'/../admin/includes/managers/config_manager.php';
	$numberOfBeers;
	$beers;
	$tapOrBottle;
	$pours;
	function printKegList($beerList, $beerListSize, $containerType, $editing = FALSE)
	{
		global $numberOfBeers,$beers,$tapOrBottle;
		$beers = $beerList;
		$numberOfBeers = $beerListSize;
		$tapOrBottle = $containerType;
		$editingTable = $editing;
		$config = getAllConfigs();
		include "kegListTable.php";		
	}
?>
