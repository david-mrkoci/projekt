<?php
/*
	Input:
        $_GET[ 'ime' ] - username igraca

	Output: JSON sa svojstvima
        id - vraca ime igre (npr. username1_username2_idvrijeme)
        mojred - true/false
*/

function sendJSONandExit( $message )
{
    // Kao izlaz skripte pošalji $message u JSON formatu i prekini izvođenje.
    header( 'Content-type:application/json;charset=utf-8' );
    echo json_encode( $message );
    flush();
    exit( 0 );
}

$ime = isset($_GET[ 'ime' ]) ? $_GET[ 'ime' ] : '';
$timestamp = date(); // vrijeme kada zelimo pristupiti datoteci

//--------------------------------------------------------------------------
// Pogledamo datoteku s imenima (usernames.txt).
// Ako postoji vec zapisan id, cekamo da se datoteka ocisti.
// Ako je samo jedno ime u datoteci, zapisujemo id i vracamo poruku.
// Ako je datoteka prazna, zapisjuemo ime i cekamo da se u datoteku zapise id.
//--------------------------------------------------------------------------

$filename = "usernames.txt";
$response = [];

$error = "";
if( !file_exists( $filename ) )
    $error = $error . "Datoteka " . $filename . " ne postoji. ";
else
{
    if( !is_readable( $filename ) )
        $error = $error . "Ne mogu čitati iz datoteke " . $filename . ". ";

    if( !is_writable( $filename ) )
        $error = $error . "Ne mogu pisati u datoteku " . $filename . ". ";
} 

if( $error !== "" )
{
    $response = [];
    $response[ 'error' ] = $error;

    sendJSONandExit( $response );
}

function cekaj_promjenu($filename, $timestamp)
{
    $currentmodif = filemtime( $filename );
    while( $currentmodif <= $timestamp )
    {
        usleep( 10000 );
        clearstatcache();
        $currentmodif = filemtime( $filename );
    }
    return 0;
}

if( $ime != '' )
{   
    $file_content = file_get_contents( $filename );

    //---------------------------------------------------------------------------
    // Je li id vec zapisan u datoteci?
    //---------------------------------------------------------------------------
    
    $pattern = "/^[a-zA-Z0-9]{2,30}_[a-zA-Z0-9]{2,30}_[0-9]{2,30}$/"; // treba dodati regularni izraz za alphanum_aplhanum_num
    if ( preg_match( $pattern, $file_content ) )
    {
        // Sad cekamo promjenu u datoteci
        cekaj_promjenu( $filename, $timestamp );
        // Dogodila se promjena, zapisujemo svoje ime i cekamo promjenu
        file_put_contents( $filename, $ime );
        $timestamp = filemtime( $filename );
        cekaj_promjenu( $filename, $timestamp );
        // U datoteci je zapisan id
        $id = file_get_contents($filename);
        $response [ 'id' ] = $id;
        // prvi smo na red jer smo prvi zapisani
        $response[ 'mojred' ] = true;
        file_put_contents($filename, "");
    }
    elseif
    {
        //---------------------------------------------------------------------------
        // Je li jedno ime vec zapisano u datoteci?
        //---------------------------------------------------------------------------

        $pattern = "/^[a-zA-Z0-9]{2,30}$/"; // treba dodati regularni izraz za SAMO alphanum
        if ( preg_match( $pattern, $file_content ) )
        {
            // Nadopunjujemo file s id-om i saljemo ga natrag
            $idvrijeme = date();
            $id = $file_content . "_" . $ime . "_" . $idvrijeme;
            $response [ 'id' ] = $id;
            // drugi smo na redu jer smo drugi zapisani
            $response[ 'mojred' ] = false;
            file_put_contents( $filename, $id );
        }
    }
    elseif
    {
        //---------------------------------------------------------------------------
        // Je li datoteka prazna?
        //---------------------------------------------------------------------------

        if ( $file_content === "" )
        {
            // Zapisujemo ime u datoteku i cekamo primjenu
            file_put_contents( $filename, $ime );
            $timestamp = filemtime( $filename );
            cekaj_promjenu( $filename, $timestamp );
            // U datoteci je zapisan id
            $id = file_get_contents($filename);
            $response [ 'id' ] = $id;
            // prvi smo na red jer smo prvi zapisani
            $response[ 'mojred' ] = true;
            file_put_contents($filename, "");
        }
    }

    sendJSONandExit( $response );
}
else
{
    $response = [];
    $response[ 'error' ] = "Poruka nema definirano polje ime ili polje msg.";

    sendJSONandExit( $response );
}

?>