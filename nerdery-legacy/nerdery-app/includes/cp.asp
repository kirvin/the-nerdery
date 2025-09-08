<script language="javascript" type="text/javascript">
	var isVisible = false;

	function toggleControlPanel (cp) {
		//alert (cp.className);
		if (isVisible) {
			cp.className = "cp_hidden";
			isVisible = false;
		}
		else {
			cp.className = "cp_visible";
			isVisible = true;
		}
	}
	
	function validatePoints (frm) {
		var valid = true;
		var msg = "Please enter the following information:";
		if (frm.plus_minus.selectedIndex == 0) {
			valid = false;
			msg += "\n* Give\\TakeAway";
		}
		if (frm.number_points.selectedIndex == 0) {
			valid = false;
			msg += "\n* Number Of Points";
		}
		if (frm.userID.selectedIndex == 0) {
			valid = false;
			msg += "\n* User";
		}
		if (frm.reasonFor.value == "") {
			valid = false;
			msg += "\n* Reason";
		}
		
		if (!valid)
			alert (msg);
		return valid;
	}

</script>

<table border="0" width="750" cellpadding="0" cellspacing="0">
	<tr>
		<td width="23"><img height="23" width="23" src="/nerdery/images/cp.closed.w.gif"></td>
		<td width="704" background="/nerdery/images/cp.closed.bg.gif">&nbsp;</td>
		<td width="23"><a href="javascript: toggleControlPanel (document.all.control_panel);"><img border="0" height="23" width="23" src="/nerdery/images/cp.closed.e.gif"></a></td>
	</tr>
</table>

<div id="control_panel" class="cp_hidden">
	<table border="0" width="750" cellpadding="0" cellspacing="0">
	<form name="cp_form" action="assign_points.asp" method="post" onsubmit="return validatePoints (this);">
	<input type="hidden" name="returnTo" value="<%=request.servervariables ("URL")%>">
<%
		Dim x
		for each x in request.form
			response.write "<input type=""hidden"" name=""" & x & """ value=""" & request.form (x) & """>"
		next
%>
		<tr>
			<td width="23"><img height="23" width="23" src="/nerdery/images/cp.open.nw.gif"></td>
			<td width="704" background="/nerdery/images/cp.open.n.gif">&nbsp;</td>
			<td width="23"><img height="23" width="23" src="/nerdery/images/cp.open.ne.gif"></td>
		</tr>
		<tr>
			<td width="23"><img height="25" width="23" src="/nerdery/images/cp.open.w.gif"></td>
			<td width="704" bgcolor="#FFFFFF" align="center">
				<select name="plus_minus">
					<option value="-1">
					<option value="add">Give
					<option value="subtract">Take away
				</select>
				&nbsp;&nbsp;
				<select name="number_points">
					<option value="-1">
					<option value="1">1
					<option value="2">2
					<option value="5">5
					<option value="10">10
					<option value="20">20
					<option value="25">25
					<option value="50">50
				</select>
				&nbsp;&nbsp;
				Cool Points to 
				&nbsp;&nbsp;
				<select name="userID">
					<option value="-1">
				<%
					sql = "SELECT * FROM Users WHERE UserID <> '" & session("UserID") & "' ORDER BY DisplayName"
					execSQL sql, rs
					do until rs.eof
				%>
					<option value="<%=rs("UserID")%>"><%=rs("DisplayName")%>
				<%
						rs.moveNext
					loop
				%>
				</select>
				&nbsp;&nbsp;
				because <input type="text" name="reasonFor" size="10" value="">
				&nbsp;&nbsp;
				<input type="submit" value="Make it so">
				
			</td>
			<td width="23"><img height="25" width="23" src="/nerdery/images/cp.open.e.gif"></td>
		</tr>
		<tr>
			<td width="23"><img height="23" width="23" src="/nerdery/images/cp.open.sw.gif"></td>
			<td width="704" background="/nerdery/images/cp.open.s.gif">&nbsp;</td>
			<td width="23"><img height="23" width="23" src="/nerdery/images/cp.open.se.gif"></td>
		</tr>
	</form>
	</table>
</div>
	
