<?php
require_once __DIR__.'/header.php';

$htmlHelper = new HtmlHelper();
$kegManager = new KegManager();
$kegStatusManager = new KegStatusManager();
$kegTypeManager = new KegTypeManager();
$beerManager = new BeerManager();
$tapManager = new TapManager();

$config = getAllConfigs();
//Change the beerId value from beerId~fg to just beerId
if(isset($_POST['beerId'])){
    $_POST['beerId'] = explode('~', $_POST['beerId'])[0];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keg = new Keg();
    $keg->setFromArray($_POST);
    if( isset($_POST['kickKeg'])){
        $tapManager->closeTapById($keg->get_onTapId());
    }
    if($kegManager->Save($keg)){
        unset($_POST);
        redirect('keg_list.php');
    }
}

$keg = null;
if( isset($_GET['id'])){
    $keg = $kegManager->GetById($_GET['id']);
}else if( isset($_POST['id'])){
    $keg = $kegManager->GetById($_POST['id']);
}

if($keg == null){
    $keg = new Keg();
    $keg->setFromArray($_POST);
}

$kegStatusList = $kegStatusManager->GetAll();
$kegTypeList = $kegTypeManager->GetAll();
$beerList = $beerManager->GetAllActive();

if( isset($_GET['beerId'])){
    $beer = $beerManager->GetById($_GET['beerId']);
}else{
    $beer = new Beer();
}
?>
	<!-- Start Header  -->
<?php
include 'top_menu.php';
?>
	<!-- End Header -->
		
	<!-- Top Breadcrumb Start -->
	<div id="breadcrumb">
		<ul>	
			<li><img src="img/icons/icon_breadcrumb.png" alt="Location" /></li>
			<li><strong>Location:</strong></li>
			<li><a href="keg_list.php">Keg List</a></li>
			<li>/</li>
			<li class="current">Keg Form</li>
		</ul>
	</div>
	<!-- Top Breadcrumb End --> 
	
	<!-- Right Side/Main Content Start -->
	<div id="rightside">
		<div class="contentcontainer med left">
	<p>
		fields marked with an * are required
		<?php $htmlHelper->ShowMessage(); ?>

	<form id="keg-form" method="POST">
		<input type="hidden" name="id" value="<?php echo $keg->get_id() ?>" />
		<input type="hidden" name="active" value="1" />

		<table style="width:950;border:0;cellspacing:1;cellpadding:0;">
			<tr>
				<td>
					Label: <b><font color="red">*</font></b>
				</td>
				<td>
					<input type="text" id="label" class="mediumbox" name="label" value="<?php echo $keg->get_label() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Beer Name:
				</td>
				<td>
					<?php 
						$str = "<select id='beerId' name='beerId' class=''>\n";
						$str .= "<option value=''>Select One</option>\n";
						foreach($beerList as $item){
						    if( !$item ) continue;
						    $sel = "";
						    if( isset($keg) && $keg->get_beerId() == $item->get_id())  $sel .= "selected ";
						    $desc = $item->get_name();
						    $str .= "<option value='".$item->get_id()."~".$item->get_fg()."' ".$sel.">".$desc."</option>\n";
						}
						$str .= "</select>\n";
						
						echo $str;
						// echo $htmlHelper->ToSelectList("beerId", "beerId", $beerList, "name", "id", $keg->get_beerId(), ($keg->get_onTapId()?null:"Select One")); 
					?>
				</td>
			</tr>
            <?php if($keg->get_onTapId()) { ?>
			<tr>
				<td>
                	<?php 
						$tap = $tapManager->GetByID($keg->get_onTapId());
						if($tap){
							echo "On Tap ".$tap->get_tapNumber().":";
		                    echo '<input type="hidden" name="onTapId" value="'.$tap->get_id().'" />';
		                    echo '<input type="hidden" name="tapNumber" value="'.$tap->get_tapNumber().'" />';
						}
					?>
				</td>
				<td>
					<input name="kickKeg" type="submit" class="btn" value="Kick Keg" />
				</td>
			</tr>
            <?php } ?>
			<tr>
				<td>
					Status: <b><font color="red">*</font></b>
				</td>
				<td>
					<?php echo $htmlHelper->ToSelectList("kegStatusCode", "kegStatusCode", $kegStatusList, "name", "code", $keg->get_kegStatusCode(), "Select One"); ?>
				</td>
			</tr>
			<tr>
				<td>
					Type: <b><font color="red">*</font></b>
				</td>
				<td>
					<?php echo $htmlHelper->ToSelectList("kegTypeId", "kegTypeId", $kegTypeList, "name", "id", $keg->get_kegTypeId(), "Select One"); ?>
				</td>
			</tr>	
			<tr>
				<td>
					Make: 
				</td>
				<td>
					<input type="text" id="make" class="mediumbox" name="make" value="<?php echo $keg->get_make() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Model: 
				</td>
				<td>
					<input type="text" id="model" class="mediumbox" name="model" value="<?php echo $keg->get_model() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Serial: 
				</td>
				<td>
					<input type="text" id="serial" class="mediumbox" name="serial" value="<?php echo $keg->get_serial() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Stamped Owner: 
				</td>
				<td>
					<input type="text" id="stampedOwner" class="mediumbox" name="stampedOwner" value="<?php echo $keg->get_stampedOwner() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Stamped Location: 
				</td>
				<td>
					<input type="text" id="stampedLoc" class="mediumbox" name="stampedLoc" value="<?php echo $keg->get_stampedLoc() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Current Weight:<br>(not &lt; Empty Weight)
				</td>
				<td>
					<input type="text" id="currentWeight" class="mediumbox" name="weight" value="<?php echo $keg->get_weight() ?>" onchange="updateCurrentAmount()"/>
				</td>
			</tr>
			<tr>
				<td>
					Empty Weight: 
				</td>
				<td>
					<input type="text" id="emptyWeight" class="mediumbox" name="emptyWeight" value="<?php echo $keg->get_emptyWeight() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Max Volume: 
				</td>
				<td>
					<input type="text" id="maxVolume" class="mediumbox" name="maxVolume" value="<?php echo $keg->get_maxVolume() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Start Amount: 
				</td>
				<td>
					<input type="text" id="startAmount" class="mediumbox" name="startAmount" value="<?php echo $keg->get_startAmount() ?>" />
				</td>
			</tr>
			<tr>
				<td>
					Current Amount: 
				</td>
				<td>
					<input type="text" id="currentAmount" class="mediumbox" name="currentAmount" value="<?php echo $keg->get_currentAmount() ?>" onchange="updateCurrentWeight()" />
        			<?php if($config[ConfigNames::UseDefWeightSettings]){?>
            			<input type="hidden" id="fermentationPSI" class="mediumbox" name="fermentationPSI" value="<?php echo $config[ConfigNames::DefaultFermPSI] ?>" />
        				<input type="hidden" id="keggingTemp" class="mediumbox" name="keggingTemp" value="<?php echo $config[ConfigNames::DefaultKeggingTemp] ?>" />
    				<?php } ?>
				</td>
			</tr>
			<?php if(!$config[ConfigNames::UseDefWeightSettings]){?>
    			<tr>
    				<td>
    					Fermentation PSI: 
    				</td>
    				<td>
    					<input type="text" id="fermentationPSI" class="mediumbox" name="fermentationPSI" value="<?php echo $keg->get_fermentationPSI() ?>" />
    				</td>
    			</tr>
    			<tr>
    				<td>
    					Kegging<br/>Temperature: 
    				</td>
    				<td>
    					<input type="text" id="keggingTemp" class="mediumbox" name="keggingTemp" value="<?php echo $keg->get_keggingTemp() ?>" />
    				</td>
    			</tr>
			<?php } ?>
			<tr>
				<td>
					Notes: 
				</td>
				<td>
					<textarea id="notes" class="text-input textarea" name="notes" style="width:500px;height:100px"><?php echo $keg->get_notes() ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<input name="save" type="submit" class="btn" value="Save" />
					<input type="button" class="btn" value="Cancel" onclick="window.location='keg_list.php'" />
				</td>
			</tr>								
		</table>
		<br />
		<div align="right">			
			&nbsp; &nbsp; 
		</div>

	</form>
	</div>
	<!-- End On Tap Section -->

	<!-- Start Footer -->   
<?php 
include 'footer.php';
?>

	<!-- End Footer -->
		
	</div>
	<!-- Right Side/Main Content End -->
	<!-- Start Left Bar Menu -->   
<?php 
include 'left_bar.php';
?>
	<!-- End Left Bar Menu -->  
	<!-- Start Js  -->
<?php
include 'scripts.php';
?>

<script>
    updateCurrentWeight();
	function updateCurrentWeight(){
		var emptyKegWeight = document.getElementById("emptyWeight").value
		var beerSelArr = document.getElementById("beerId").value.split("~");
		var fg = 1.000;
		if(beerSelArr.length > 1 && beerSelArr[1] != "")
		{
			fg = beerSelArr[1];
		}
		weight = getWeightByVol(document.getElementById("currentAmount").value,  
																				emptyKegWeight, 
																		     	document.getElementById("keggingTemp").value, 
																		     	<?php echo $config[ConfigNames::BreweryAltitude] ?>,
																		     	document.getElementById("fermentationPSI").value, 
																		     	true, 
																		     	fg, 
																		     	false).toFixed(5);
		if(!isNaN(weight))document.getElementById("currentWeight").value = weight;
	}
	function updateCurrentAmount(){
		var emptyKegWeight = document.getElementById("emptyWeight").value;
		var beerSelArr = document.getElementById("beerId").value.split("~");
		var fg = 1.000;
		if(beerSelArr.length > 1 && beerSelArr[1] != "")
		{
			fg = beerSelArr[1];
		}
		var volume = getVolumeByWeight(document.getElementById("currentWeight").value, 
																				emptyKegWeight,
																		     	document.getElementById("keggingTemp").value, 
																		     	<?php echo $config[ConfigNames::BreweryAltitude] ?>, 
																		     	document.getElementById("fermentationPSI").value, 
																				true, 
																				fg, 
																				false).toFixed(5);
		if(!isNaN(volume))document.getElementById("currentAmount").value = volume;
	}

	$(function() {		
		
		$('#keg-form').validate({
			rules: {
				label: { required: true },
				kegTypeId: { required: true },
				kegStatusCode: { required: true },
				beerId: { required: false },
				make: { required: false },
				model: { required: false },
				serial: { required: false },
				stampedOwner: { required: false },
				stampedLoc: { required: false },
				weight: { required: false },
				notes: { required: false }
			}
		});
		
	});
</script>

	<!-- End Js -->
	<!--[if IE 6]>
	<script type='text/javascript' src='scripts/png_fix.js'></script>
	<script type='text/javascript'>
	DD_belatedPNG.fix('img, .notifycount, .selected');
	</script>
	<![endif]--> 
	
</body>
</html>
