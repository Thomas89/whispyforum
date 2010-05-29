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
<br>
<a name="images">
<h2>K�pek</h2>
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
	$kepSzam = $sorok[0];
	
	for ($i == 1; $i <= $kepSzam; $i++)
	{
		$kep = explode(",", $sorok[$i]);
		
		if ( $kep[0] != "")
			RajzKep($i, $kep[0], $kep[1]);
	}
	
	
// Z�r�s
?>
</table>
<a name="emoticons">
<h2>Hangulatjelek (emoteiconok)</h2>
<table border="1">
	<tr>
		<th>Megjelen�s</th>
		<th>K�d</th>
	</tr>
<?php
	function RajzSmile($kepName, $kepKod = '')
	{
		
		print("
		<tr>
			<td><img src='emote/" .$kepName. "'></td>
			<td>" .$kepKod. "</td>
		</tr>"); // Egy emoteicon ki�r�sa-rajzol�sa
	}
	
	// Emoteicon lista kinyer�se a le�r�f�jlb�l
	$data2 = @file_get_contents('emotes.lst');
	$sorok2 = explode("\r\n", $data2);
	$kepSzam2 = $sorok2[0];
	
	for ($j == 1; $j <= $kepSzam2; $j++)
	{
		$kep2 = explode(",", $sorok2[$j]);
		
		if ( $kep2[0] != "")
			RajzSmile($kep2[0], $kep2[1]);
	}
// Z�r�s
?>
</table>	
	
</body>

</html>