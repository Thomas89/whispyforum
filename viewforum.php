<?php
/* WhispyForum CMS-forum portálrendszer
   http://code.google.com/p/whispyforum/
*/

/* viewforum.php
   fórumok listázása
*/
 
 include('includes/common.php');
 Inicialize('viewforum.php');
 SetTitle("Fórum");
 
 print("<div align='center'><center><table class='forum'>
 <tr>
	<th class='forumheader'>Fórum neve</th>
	<th class='forumheader'>Témák</th>
	<th class='forumheader'>Hozzászólások</th>
	<th class='forumheader'>Utolsó hozzászólás</th>
 </tr>"); // Fejléc
 
 global $cfg, $sql;
 $adat = $sql->Lekerdezes("SELECT * FROM " .$cfg['tbprf']."forum"); // Fórumok betöltése
 while ($sor = mysql_fetch_assoc($adat)) { // Fórumok listázása
	// Felhasználó nevének betöltése
	$adat2 = mysql_fetch_assoc($sql->Lekerdezes("SELECT id, username FROM " .$cfg['tbprf']. "user WHERE id='" .$sor['lastuser']. "'"));
		
	print("<tr>
		<td class='forumlist'><p><a href='viewtopics.php?id=" .$sor['id']. "'>" .$sor['name']. "</a><br>" .$sor['description']. "</p></td>
		<td class='forumlist'>" .$sor['topics']. "</td>
		<td class='forumlist'>" .$sor['posts']. "</td>
		<td class='forumlist'><p>" .Datum("normal","m","d","H","i","s", $sor['lastpostdate']). "<br><a href='profile.php?id=" .$adat2['id']. "'>" .$adat2['username']. "</a><a href='viewtopic.php?id=" .$sor['lpTopic']. "#pid" .$sor['lpId']. "'><img src='themes/" .$_SESSION['themeName']. "/lastpost.gif' border='0' alt='Ugrás a legutolsó hozzászóláshoz'></a></p></td>
		</tr>"); // Fórum sor
	}
	print("</table></center></div>"); // Táblázat zárása
 DoFooter();
?>