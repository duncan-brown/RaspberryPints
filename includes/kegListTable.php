<?php
//Only to be called from /includes/common.php printBeerList
require_once __DIR__.'/functions.php';
require_once __DIR__.'/../admin/includes/managers/config_manager.php';
require_once __DIR__.'/../admin/includes/html_helper.php';
$config = getAllConfigs();
$htmlHelper = new HtmlHelper();
$beerColSpan = 1;
$MAX_COLUMNS = 6;
$editting = (isset($editingTable) && $editingTable);

$maxTapCol = isset($config[ConfigNames::HozTapListCol])?$config[ConfigNames::HozTapListCol]+1:1;
if($editting) $maxTapCol = 1;
?>

<table>
	<?php if($editting || $config[ConfigNames::ShowBeerTableHead]){?>
	<?php echo !$editting?'<!-- <thead>':'<tbody>' ?>
		<tr>
		<?php for($tapCol = 0; $tapCol< $maxTapCol && $numberOfBeers > $tapCol; $tapCol++){ ?>
			<?php $beerColSpan = 1; ?>
			<?php for($col = 1; $col <= $MAX_COLUMNS; $col++){ ?>
    		
    			<?php if( beerListShouldDisplayRow($editting, $col, $config[ConfigNames::BeerInfoColNum])){?>
        			<?php if($config[ConfigNames::ShowBeerName]){ ?>
        				<?php 
                            if($config[ConfigNames::ShowBreweryImages]){ $beerColSpan++; }
                            if($config[ConfigNames::ShowBeerImages]){ $beerColSpan++; }
                        ?> 
        				<th <?php if($beerColSpan > 1){ echo 'colspan="'.$beerColSpan.'"';}?> class="beername"  <?php if($maxTapCol!=1) echo 'style="width:'.(100/$maxTapCol).'%";'?>>
        					<?php if($config[ConfigNames::ShowBeerName]){ ?>
        						BEER NAME 
        						<?php if($config[ConfigNames::ShowBeerStyle]){ ?>&nbsp; &nbsp; STYLE<hr><?php } ?>
        						<?php if($config[ConfigNames::ShowBeerNotes]){ ?>&nbsp; &nbsp; TASTING NOTES<?php } ?>
        						<?php if($config[ConfigNames::ShowBeerRating]){?>&nbsp; &nbsp; RATING<hr><?php } ?>
        					<?php } ?>
							<?php DisplayEditShowColumn($editting, $config, $col, ConfigNames::BeerInfoColNum)?>
    					<input type="hidden" name="<?php echo ConfigNames::BeerInfoColNum;?>" id="<?php echo ConfigNames::BeerInfoColNum;?>" value="<?php echo abs($config[ConfigNames::BeerInfoColNum]);?>"/>
        				</th>
        			<?php } ?>
    			<?php }?>
    			
    			<?php if($config[ConfigNames::ShowKegCol] &&
				         beerListShouldDisplayRow($editting, $col, $config[ConfigNames::KegColNum])){ ?>
    				<th class="keg">
    					DRINKS<hr>REMAINING
						<?php DisplayEditShowColumn($editting, $config, $col, ConfigNames::KegColNum)?>
    					<input type="hidden" name="<?php echo ConfigNames::KegColNum;?>" id="<?php echo ConfigNames::KegColNum;?>" value="<?php echo abs($config[ConfigNames::KegColNum]);?>"/>
    				</th>
    			<?php } ?>
			<?php } ?>
			<?php if($maxTapCol > 1 && $tapCol != $maxTapCol-1){ echo "<td style=width:70px;></td>"; } ?>
		<?php } ?>
		</tr>
	<?php echo !$editting?'</thead>--><tbody>':'' ?>
	<?php }?>
		<?php for($i = 1; $i <= ceil($numberOfBeers/$maxTapCol); $i++) {
			$beer = null;
			if( isset($beers[$i]) ) $beer = $beers[$i];
			if($tapOrBottle != ConfigNames::CONTAINER_TYPE_KEG  && !isset($beer) ) continue;
		?>
			<tr id="<?php echo $beer['id']; ?>">
				<?php 
			    for($tapCol = 0; $tapCol< $maxTapCol && $numberOfBeers > $tapCol; $tapCol++){
        			$beer = null;
        			if( $i+($tapCol * (ceil($numberOfBeers/$maxTapCol))) > $numberOfBeers ) continue;//Skip numbers outside of the tap range
        			if( isset($beers[$i+($tapCol * (ceil($numberOfBeers/$maxTapCol)))]) ) $beer = $beers[$i+($tapCol * (ceil($numberOfBeers/$maxTapCol)))];
        			if($tapOrBottle != ConfigNames::CONTAINER_TYPE_KEG  && !isset($beer) ) continue;
        		?>
				<?php for($col = 1; $col <= $MAX_COLUMNS; $col++){ ?>
			
			
				<?php if( beerListShouldDisplayRow($editting, $col, $config[ConfigNames::BeerInfoColNum]) ){?>
				<?php if($config[ConfigNames::ShowBreweryImages]){ ?>
					<td class="breweryimg" >
					<?php if(isset($beer) && $beer['beername']){ ?>
						<img style="border:0;width:100px" src="<?php echo $beer['breweryImage']; ?>" />
					<?php } ?>
					</td>
				<?php } ?>
				
				<?php if($config[ConfigNames::ShowBeerImages]){ ?>
				<?php /* If not the first column in the beer section 
				       ($beerColSpan = 1 if just beer 
				        $beerColSpan = 2 if breweryimg or beerimg and beer, 
				        $beerColSpan = 3 if all 3
				 */ ?>
					<td style="<?php if($beerColSpan > 2){ echo 'border-left: none;'; } ?>" class="beerimg">
					<?php if(isset($beer) && $beer['beername']){ ?>
						<?php 
						beerImg($config, $beer['untID'], $beer['beerId']);
						?>
					<?php } ?>
					</td>
				<?php } ?>
                
				<!-- If not the first column in the beer section-->
				<td class="name" <?php if($beerColSpan > 1){ echo 'style="border-left: none; width : '.($maxTapCol==1?($beerColSpan > 2?80:90).'%"':'50%"'); } ?>
							     <?php if($beerColSpan == 1){ echo 'style="width : '.(100/$maxTapCol).'%"'; } ?>>	
					<?php if(isset($beer) && $beer['beername']){ ?>		
                    					
						<?php if($config[ConfigNames::ShowBeerName]){ ?>
                            <h1><?php echo $beer['beername']; ?></h1>
                        <?php } ?>
                        
					
					<?php } ?>
				</td>
				<?php } ?>
				
			<?php if($config[ConfigNames::ShowKegCol] &&
				         beerListShouldDisplayRow($editting, $col, $config[ConfigNames::KegColNum])){ ?>
				<td class="keg" >
				<?php if(isset($beer) && $beer['beername']){ ?>
				<?php 
				//Convert to the correct units (use gal and l)
				    $beer['startAmount']  = convert_volume($beer['startAmount'], $beer['startAmountUnit'], $config[ConfigNames::DisplayUnitVolume], TRUE);
					$beer['startAmountUnit'] = $config[ConfigNames::DisplayUnitVolume];
				    $beer['remainAmount'] = convert_volume($beer['remainAmount'], $beer['remainAmountUnit'], $config[ConfigNames::DisplayUnitVolume], TRUE);
					$beer['remainAmountUnit'] = $config[ConfigNames::DisplayUnitVolume]; 
				?>
				<?php } ?>
				<?php if(isset($beer) && $beer['beername'] && 
				         $beer['startAmount'] > 0){ ?>
					<?php if(($editting || $config[ConfigNames::ShowLastPouredValue]) &&
					         $tapOrBottle == ConfigNames::CONTAINER_TYPE_KEG &&
					         isset($beer['lastPour']) && $beer['lastPour'] != ''){ ?>
    					<h3><?php echo $beer['lastPour']?></h3>
    				<?php }?>
					<?php if($config[ConfigNames::ShowPouredValue]){?>
					<?php if($tapOrBottle == ConfigNames::CONTAINER_TYPE_KEG){ ?>
					<?php } else { ?>
						<h3><?php echo $beer['remainAmount'].' x '.number_format(convert_volume($beer['volume'], $beer['volumeUnit'], $config[ConfigNames::DisplayUnitVolume]), 1); echo $config[ConfigNames::DisplayUnitVolume];?></h3> 
					<?php } ?>
					<?php } ?>
					<?php 
    					if($config[ConfigNames::ShowKegImg]){
    					    $kegImgColor = "0,255,0";
    						$percentRemaining = 0.0;
    						if($beer['startAmount'] && $beer['startAmount'] > 0)$percentRemaining = ($beer['remainAmount'] / $beer['startAmount']) * 100;
    						if( $beer['remainAmount'] <= 0 ) {
    						    $percentRemaining = 0;
    						} else if( $percentRemaining < 15 ) {
    						    $kegImgColor = "255,0,0";
    						} else if( $percentRemaining < 25 ) {
    						    $kegImgColor = "255,165,0";
    						} else if( $percentRemaining < 45 ) {
    						    $kegImgColor = "255,255,0";
    						} else if ( $percentRemaining < 100 ) {
    						    $kegImgColor = "0,255,0";
    						} else if( $percentRemaining >= 100 ) {
    						    $kegImgColor = "0,255,0";
    						}
    						$kegOn = "";
    						if($config[ConfigNames::UseTapValves]){
    						    if ( $tapOrBottle == ConfigNames::CONTAINER_TYPE_KEG &&
    						        $beer['valvePinState'] == $config[ConfigNames::RelayTrigger] ) 
    								$kegOn = "keg-enabled";
    							else
    								$kegOn = "keg-disabled";
    						}
					?>
    					<div class="keg-container">
    						<?php if($tapOrBottle == ConfigNames::CONTAINER_TYPE_KEG){ ?>
    							<?php 
							     $kegType="keg";
							     if(strtolower(substr($beer['kegType'], 0, 4)) == "corn") $kegType = "corny";
							    ?>
    							<div class="keg-indicator" style="background: url(img/keg/kegSvg.php?container=<?php echo $kegType?>&empty) no-repeat bottom left;"> 
								<div class="keg-full" style="height:100%; width: 100%; background: url(img/keg/kegSvg.php?container=<?php echo $kegType?>&fill=<?php echo $percentRemaining; ?>&rgb=<?php echo $kegImgColor ?>) no-repeat bottom left;" >
    								       <div class="<?php echo $kegOn ?>"></div>
    								</div>
    							</div>
    						<?php } else { ?>
    							<div class="bottle-indicator">
    								<div class="bottle-full" style="height:100%; background: url(img/bottle/bottleSvg.php?container=bottle&fill=<?php echo $percentRemaining; ?>&rgb=<?php echo $kegImgColor ?>) no-repeat bottom left;">
    								</div>
    							</div>
    						<?php } ?>
    					</div>
    					<?php }?>
				<?php }elseif( isset($beer) && $beer['beername'] && 
				               isset($beer['lastPour']) && $beer['lastPour'] != ''){ ?>
					<?php if($config[ConfigNames::ShowPouredValue]){?>
						<h3>Last pour:<br/><?php echo $beer['lastPour']?></h3>
					<?php } ?>
				<?php }?>
				</td>
			<?php } ?>
			<?php } //End for column loop ?>
			<?php if($maxTapCol > 1 && $tapCol != $maxTapCol-1){ echo "<td style=width:70px;></td>"; } ?>
			<?php } //End for tap column loop ?>
			</tr>
		<?php } ?>
	</tbody>
</table>
