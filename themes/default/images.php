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
<title>T�m�hoz el�rhet� k�pek list�z�sa</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<a href="index.php"><< Megjelen�s</a>
<a href="emoticons.php">Hangulatjelek >></a>
<br>
<table border="1">
	<tr>
		<th></th>
		<th>K�p</th>
		<th>F�jln�v</th>
		<th>F�jlm�ret</th>
		<th>Le�r�s</th>
	</tr>

<?php
	function RajzKep($kepNum, $kepName, $leiras = '')
	{
		
		print("
		<tr>
			<td>" .$kepNum. "</td>
			<td><img src='" .$kepName. "'></td>
			<td><a href='" .$kepName. "'>" .$kepName. "</a></td>
			<td>" .DecodeSize(@filesize($kepName)). "</td>
			<td>" .$leiras. "</td>
		</tr>"); // Egy k�p ki�r�sa-rajzol�sa
	}
	
	// K�plista kinyer�se a le�r�f�jlb�l
	$data = @file_get_contents('images.lst');
	$sorok = explode("\r\n", $data);
	
	foreach ($sorok as &$sor)
	{
		$kep = explode(",", $sor);
		
		if ( $kep[0] != "")
			RajzKep($i, $kep[0], $kep[1]);
	}
	
	
// Z�r�s
?>
</table>
	
</body>

</html>