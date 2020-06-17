<?php
/*
	Input:
        $_GET[ 'id' ] - ???
        $_GET[ 'row' ] - u koji red treba dodati krug
        $_GET[ 'col' ] - u koji stupac treba dodati krug
        $_GET[ 'kraj' ] - je li igra gotova
        $_GET[ 'usernames' ] - string imena igrača koji su upareni
                               (npr. ivan_marko) 

    Output: JSON sa svojstvima (natrag se šalje potez igrača 
                                koji nije pozvao skriptu)
        uspjeh - true/false
        row - u koji red treba dodati krug
        col - u koji stupac treba dodati krug
        mojred - true/false
        (tu možda treba dodati još neke)

    - Treba možda dodati boje??
    - Trebalo bi i username-ove slati da se zna koju datoteku treba obraditi
      (to treba pamtiti na klijentu)
    - Problem: dok jedna skripta ceka u while petlji, druga pokušava
               pristupiti istoj datoteci 
*/

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

$ime = $_GET[ 'ime' ] ?? "server: nemam ime";
$id = $_GET[ 'id' ];
$row = $_GET[ 'row' ];
$col = $_GET[ 'col' ];
$kraj = $_GET[ 'kraj' ];
$usernames = $_GET[ 'usernames' ]

//-------------------------------------------------------------------------
// zapisujemo poslane podatke u datoteku (username1_username2.txt)
// koja čuva zadnji potez
//-------------------------------------------------------------------------

$filename = $usernames . ".txt";
$my_move = implode(";", [$ime, $id, $row, $col, $kraj]);

file_put_contents( $filename, $my_move);

//-------------------------------------------------------------------------
// pokrenemo while petlju koja čeka promjenu u datoteci (to je novi potez)
//-------------------------------------------------------------------------

$change = false;
while( $change === false)
{
    $new_move = file_get_contents($filename);
    if ( $new_move !== $my_move )
        $change = true;
}

//-------------------------------------------------------------------------
// novi potez šaljemo natrag igraču koji je pozvao skriptu
//-------------------------------------------------------------------------

$move_array = explode(";", $new_move);
$new_row = $move_array[2];
$new_col = $move_array[3];

$message = [];
$message[ 'uspjeh' ] = true;
$message[ 'row' ] = $new_row;
$message[ 'col' ] = $new_col;
$message[ 'mojred' ] = true;

//sleep(2);

sendJSONandExit( $message );

?>