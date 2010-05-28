<?php
header('Content-type: text/html; charset=iso-8859-2'); // Sz�ks�ges

 function DecodeSize( $bytes ) // F�jlm�ret dek�dol�sa emberileg �rtelmezhet� form�tumba
 {
   $types = array( 'B', 'KB', 'MB', 'GB', 'TB' );
   for( $i = 0; $bytes >= 1024 && $i < ( count( $types ) -1 ); $bytes /= 1024, $i++ );
   return( round( $bytes, 2 ) . " " . $types[$i] );
 } 

// Fejl�c, t�bl�zatkezd�s rajzol�sa
?>

<html>

<head> 
<title>CSS bemutat� oldal</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="index.php"><< Megjelen�s</a>
<br>
<table>
	<tr>
		<th>K�p</th>
		<th>F�jln�v</th>
		<th>F�jlm�ret</th>
		<th>Le�r�s</th>
	</tr>

<?php
	function RajzKep($kepName, $leiras = '')
	{
		
		print("
		<tr>
			<td><img src='" .$kepName. "'></td>
			<td><a href='" .$kepName. "'>" .$kepName. "</a></td>
			<td>" .DecodeSize(filesize($kepName)). "</td>
			<td>" .$leiras. "</td>
		</tr>"); // Egy k�p ki�r�sa-rajzol�sa
	}
	
	RajzKep('warning.png', 'Hiba�zenet ablakban figyelmeztet�s szimb�lum');
	RajzKep('error.png', 'Hiba�zenet ablakban hiba/kritikus hiba szimb�lum');
	RajzKep('x.bmp', '�res semmi');
	RajzKep('lastpost.gif', 'F�rumban az utols� hozz�sz�l�shoz ugr�s linkj�nek k�pe');
// Z�r�s
?>
</table>
</body>

</html>