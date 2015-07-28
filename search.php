<?PHP

	function getmicrotime() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
	

	//initializing connection to the database 
	$connection_string = dirname(__FILE__) . "/connectionstring.php";
	require_once($connection_string);
	//selecting table
	//mysql_select_db("finalproj") or die ( 'Unable to select database.' );
	//max number of results on the page
	$RESULTS_LIMIT=10;

	if(isset($_GET['search_term']) && isset($_GET['search_button'])) {
	   $search_term = $_GET['search_term'];

	   if(!isset($first_pos)) {
        	$first_pos = "0";
	   }

	   $start_search = getmicrotime();
	   //initializing MySQL Quary  
	   $sql_query = mysql_query("SELECT * FROM users WHERE MATCH(email_address) AGAINST('$search_term')");

	   //additional check. Insurance method to re-search the database again in case of too many matches (too many matches cause returning of 0 results)
	   if($results = mysql_num_rows($sql_query) != 0) {
            $sql =  "SELECT * FROM users WHERE MATCH(email_address) AGAINST('$search_term') LIMIT $first_pos, $RESULTS_LIMIT";
            $sql_result_query = mysql_query($sql);         
        }
	   else {
            $sql = "SELECT * FROM users WHERE (email_address LIKE '%".mysql_real_escape_string($search_term)."%' OR email_address LIKE '%".$search_term."%') ";
            $sql_query = mysql_query($sql);
            $results = mysql_num_rows($sql_query);
            $sql_result_query = mysql_query("SELECT * FROM users WHERE (email_address LIKE '%".$search_term."%' OR email_address LIKE '%".$search_term."%') LIMIT $first_pos, $RESULTS_LIMIT ");
        }
	   $stop_search = getmicrotime();
	     //calculating the search time
	   $time_search = ($stop_search - $start_search);
	}
?>
<?PHP
	if($results != 0) {
?>   
	  <!-- Displaying of the results -->
		<table border="0" cellspacing="2" cellpadding="2">
		 	<tr>
		   	<td width="47%">
		   		Results for <?PHP echo "<i><b><font color=#000000>".$search_term."</font></b></i> "; ?>
	   		</td>
		   	<td width="53%" align="right" height="22">Results <b>
		     		<?PHP echo ($first_pos+1)." - ";
			     	if(($RESULTS_LIMIT + $first_pos) < $results) echo ($RESULTS_LIMIT + $first_pos);
			     	else echo $results ; ?>
			   	</b>
			     	out of <b><?PHP echo $results; ?></b>
			     	for(<b><?PHP echo sprintf("%01.2f", $time_search); ?></b>)
			     	seconds
		     	</td>
		 	</tr>
		 	<tr>
		   	<form action="" method="GET">
			     	<td colspan="2" align="center"> <input name="search_term" type="text" value="<?PHP echo $search_term; ?>" size="40">
			       	<input name="search_button" type="submit" value="Search"> 
		       	</td>
		   	</form>
		 	</tr>
		 <?PHP   
		   while($row = mysql_fetch_array($sql_result_query))
		   {
		   ?>
	     	<tr align="left">
	       	<td colspan="2"><?PHP echo $row['title']; ?></td>
	     	</tr>
		 <?PHP
		   }
		   ?>
		</table>
		<?PHP
	}
	//if nothing is found then displays a form and a message that there are nor results for the specified term
	elseif($sql_query)
	{
	?>
	<table border="0" cellspacing="2" cellpadding="0">
	   <tr>
	       <td align="center">No results for   <?PHP echo "<i><b><font color=#000000>".$search_term."</font></b></i> "; ?></td>
	   </tr>
	   <tr>
	       <form action="" method="GET">
	       <td colspan="2" align="center">
	           <input name="search_term" type="text" value="<?PHP echo $search_term; ?>">
	           <input name="search_button" type="submit" value="Search">
	       </td>
	       </form>
	   </tr>
	</table>
	<?PHP
	}
	?>
	<table width="300" border="0" cellspacing="0" cellpadding="0">
	     <?php
	     if (!isset($_GET['search_term'])) { ?>
	   <tr>
	       <form action="" method="GET">
	       <td colspan="2" align="center">
	           <input name="search_term" type="text" value="<?PHP echo $search_term; ?>">
	           <input name="search_button" type="submit" value="Search">
	       </td>
	       </form>
	   </tr>
	   <?php
	     }
	     ?>
	 <tr>
	   <td align="right">
	<?PHP
	//displaying the number of pages where the results are sittuated
	if($first_pos > 0)
	{
	 $back=$first_pos-$RESULTS_LIMIT;
	 if($back < 0)
	 {
	   $back = 0;
	 }
	 echo "<a href='search.php?search_term=".stripslashes($search_term)."&first_pos=$back' ></a>";
	}
	if($results>$RESULTS_LIMIT)
	{
	 $sites=intval($results/$RESULTS_LIMIT);
	 if($results%$RESULTS_LIMIT)
	 {
	   $sites++;
	 }
	}
	for ($i=1;$i<=$sites;$i++)
	{
	 $fwd=($i-1)*$RESULTS_LIMIT;
	 if($fwd == $first_pos)
	 {
	     echo "<a href='search.php?search_term=".stripslashes($search_term)."&first_pos=$fwd '><b>$i</b></a> | ";
	 }
	 else
	 {
	     echo "<a href='search.php?search_term=".stripslashes($search_term)."&first_pos=$fwd '>$i</a> | ";   
	 }
	}
	if(isset($first_pos) && $first_pos < $results-$RESULTS_LIMIT)
	{
	 $fwd=$first_pos+$RESULTS_LIMIT;
	 echo "<a href='search.php?search_term=".stripslashes($search_term)."&first_pos=$fwd ' > >></a>";
	 $fwd=$results-$RESULTS_LIMIT;
	}
	?>
	   </td>
	 </tr>
	</table>
	
