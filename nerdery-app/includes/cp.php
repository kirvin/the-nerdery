<?php
function writeCP () {
?>
<style>
	label {
		display:block;
		font-weight: bold;
		/*float:left;*/
		/*width:45%;*/
		/*clear:right;*/
	}
	
	#control_panel {
		display: none;
	}
	
	#control_panel.yui-panel .hd {
		border-color:#308259;
		border-style:solid;
		border-width:0pt 1px;
		margin:0pt -1px;
		border-bottom:1px solid #CCCCCC;
		background:transparent url(../../../../assets/skins/sam/sprite.png) repeat-x scroll 0pt -200px;
		background-color: #89A798;
		color:#FFFFFF;
		font-size:93%;
		font-weight:bold;
		line-height:2;
		padding:0pt 10px;
	}

	#control_panel.yui-panel .bd, #control_panel.yui-panel .ft {
		background-color: #F3D372;
	}

	#control_panel.yui-panel .dialogFormContent {
		width:		60%;
		text-align: left;
		background-color: #F3D372;
	}

</style>

<link rel="stylesheet" type="text/css" href="../../assets/yui.css" >
<link rel="stylesheet" type="text/css" href="../../build/button/assets/skins/sam/button.css" />
<link rel="stylesheet" type="text/css" href="includes/yahoo/container/assets/skins/sam/container.css" />
<script type="text/javascript" src="includes/yahoo/yahoo/yahoo-min.js"></script>
<script type="text/javascript" src="includes/yahoo/event/event-min.js"></script>
<script type="text/javascript" src="includes/yahoo/connection/connection-min.js"></script>
<script type="text/javascript" src="includes/yahoo/dom/dom-min.js"></script>
<script type="text/javascript" src="includes/yahoo/element/element-beta-min.js"></script>
<script type="text/javascript" src="includes/yahoo/button/button-min.js"></script>
<script type="text/javascript" src="includes/yahoo/dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="includes/yahoo/container/container-min.js"></script>

<script language="javascript" type="text/javascript">
	var coolBarIsVisible = false;

	YAHOO.namespace("theirvins.nerdery");

	function init() {
		
		// Define various event handlers for Dialog
		var handleSubmit = function() {
			this.submit();
		};
		var handleCancel = function() {
			this.cancel();
		};
		var handleSuccess = function(o) {
			var response = o.responseText;
			response = response.split("<!")[0];
			if (!response == "" && !response == null)
				alert (response);
			//document.getElementById("resp").innerHTML = response;
		};
		var handleFailure = function(o) {
			alert("Submission failed: " + o.status);
		};

		// Instantiate the Dialog
		YAHOO.theirvins.nerdery.coolBar = new YAHOO.widget.Dialog(
			"control_panel", 
			{
				width : "500px",
				fixedcenter : true,
				visible : false, 
				constraintoviewport : true,
				buttons : [ 
					{ text:"Make It So", handler:handleSubmit },
					{ text:"Cancel", handler:handleCancel } 
				]
			} 
		);

		/**
		 *	Validate that all fields have selections/input.
		 *
		 */
		YAHOO.theirvins.nerdery.coolBar.validate = function() {
			var msg = "Please enter the following information:";
			var valid = true;
			var data = this.getData();
			if (data.plus_minus == null) {
				valid = false;
				msg += "\n * 'Give' or 'Take Away'";
			}
			if (data.number_points < 0) {
				valid = false;
				msg += "\n * # of points";
			}
			if (data.userID < 0) {
				valid = false;
				msg += "\n * User Name";
			}
			if (data.reasonFor == "") {
				valid = false;
				msg += "\n * Reason";
			}

			if (!valid)
				alert (msg);

			return valid;
		};

		// Wire up the success and failure handlers
		YAHOO.theirvins.nerdery.coolBar.callback = {
			success: handleSuccess,
			failure: handleFailure 
		};

		// Render the Dialog
		YAHOO.theirvins.nerdery.coolBar.render();

		// now that we've turned the div into a dialog, we can make it display
		// this is done after the dialog conversion to avoid a ui "flicker"
		document.getElementById("control_panel").style.display = "block";

		YAHOO.util.Event.addListener(
			"showCoolBar", 
			"click", 
			YAHOO.theirvins.nerdery.coolBar.show,
			YAHOO.theirvins.nerdery.coolBar, 
			true
		);
	}

	YAHOO.util.Event.onDOMReady(init);

</script>

<div id="control_panel">
	<div class="hd">Cool Bar</div>
	<div class="bd">
		<div class="dialogFormContent">
		<form name="cp_form" action="/services/cool_points.php" method="post">
			<input type="hidden" name="action" value="assignPoints">
			<input type="hidden" name="given_by" value="<?php echo $_SESSION["UserID"] ?>">

			<label for="plus_minus"></label>
			<input type="radio" name="plus_minus" value="add">Give</input>
			<input type="radio" name="plus_minus" value="subtract">Take away</input>
			<!-- 
			<select name="plus_minus">
				<option value="-1">
				<option value="add">Give
				<option value="subtract">Take away
			</select>
			-->
	
			<select name="number_points">
				<option value="-1">0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="25">25</option>
				<option value="50">50</option>
			</select>

			<label for="userID">Cool Points to</label>
			<select name="userID">
				<option value="-1">
				<?php
					$sql = "SELECT * FROM Users WHERE UserID != '" . $_SESSION["UserID"] . "' ORDER BY DisplayName";
					$rs = mysql_query ($sql) or die ("ERROR: " . mysql_error() . "<br>SQL: " . $sql);
					while ($row = mysql_fetch_array ($rs)) {
						echo "<option value=\"" . $row["UserID"] . "\">" . $row["DisplayName"] . "</option>";
					}
				?>
			</select>

			<label for="reasonFor">because</label>
			<input type="text" name="reasonFor" size="30" value="">
					
		</form>
		</div>
	</div>
</div>
<?php
}
?>
