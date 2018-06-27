<?PHP
////////////////////////////////////////////////////////////////////////////////
// PhotoFrame           http://photoframe.sf.net
   $version = "6.9";
//
// by Martin Dougiamas  http://dougiamas.com
//
// Photoframe displays a directory of images (JPEG, PNG, GIF) simply and easily.
// It will also create thumbnails and display JPEG captions if it finds them.
// Guests can leave comments, and it supports Imagemagick as well as PHP GD.
// I am placing this script in the Public Domain - use it as you wish.
//
// Requirements: A web server with this software (note minimum versions):
//               - PHP 4.0.2   http://www.php.net
//               AND EITHER THESE
//               - GD 1.8.3    http://www.boutell.com/gd
//               - libjpeg 6b  http://www.ijg.org/
//               OR
//               - Imagemagick http://www.imagemagick.org/
// 
// How to use:   1. Store all your images in a directory on your server
//               2. Save this file in that directory as index.php
//               3. Make sure the web server has write permissions so 
//                  that it can write in a sub directory (thumbnails etc).
//                  eg   mkdir thumb ; chown nobody thumb
//   (optional)  4. Change any of the settings below to suit. You can 
//                  also put these in a separate file called config.php
//   (optional)  5. Add an intro.html file if you like.
//   (optional)  6. Add header.html and footer.html files if you want 
//                  to change page colours, layout or styles.
//   (optional)  7. Add a sortfile if you want to control the order of  
//                  the photos, rather than the default alphabetical order.
//                  Just make a simple text file with one filename per line.
//                  eg   ls -1 *.jpg *.png > sortfile  
//                       (and then edit with vi)
//
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
// config.php  - these settings are the default ones.  You can either 
//               change them here or create a file called "config.php"
//               that contains override settings.  The advantage of a 
//               separate file is that you can upgrade the script and 
//               keep all your settings. 

$title        = "Lego Robot Assembly Instructions";   // Title for the overall web page 
$marginsize   =  100;               // Size of the side frame, and thumbnails
$marginside   = "left";             // Margin frame location: "left" or "right"

$thumb        = "thumb";            // Sub-directory where thumbnails are stored 
$imagequality =  70;                // Quality of reduced images, range 0 - 100
$fullwindow   =  false;             // Display images to fill browser window?

$imageresize  =  0;                 // You can rescale all images to fit within 
                                    // a boundary square of this size (eg 800).
                                    // These rescaled images are cached on disk.
                                    // To just use original images, specify 0

$pagecolor    = "#FFFFFF";          // Background color of all pages
$margincolor  = "#FFFFFF";          // Used if set, else margincolor = pagecolor
$textcolor    = "#000000";          // Text color for all pages
$linkcolor    = "#000055";          // Link color for all pages
$vlinkcolor   = "#550055";          // Visited link color for all pages
$background   = "";                 // Background image for all pages
$stylesheet   = "";                 // Full URL to a stylesheet for all pages.
                                    // HTML text tags used: <H1>,<P>,<TD>,<A>

$lang         = "en";               // Language: af/ar/ba/bg/br/ca/de/dk/
                                    //           ee/en/es/fi/fr/gb/gr/hu/is/it/
                                    //           lv/nl/no/pl/pt/ro/ru/se/tr

$introfile    = "intro.html";       // If it exists, will be displayed up front 
$headerfile   = "header.html";      // If it exists, will be included at top
$footerfile   = "footer.html";      // If it exists, will be included at bottom

$sortfile     = "sortfile";         // If it exists, contains filenames in order
$sortreverse  =  false;             // If true, then the sort order is reversed
$datenames    =  false;             // If true, then assumes the filenames have 
                                    // been named starting with the date YYYYMMDD
                                    
$password     = "";                 // This password enables admin mode, for 
                                    // editing captions, deleting guest comments,
                                    // uploading new images, deleting images etc
                                    // If left blank "" then admin is disabled
$moderate     =  false;             // If true, then guest comments will not be 
                                    // automatically added to the pages.  Only 
                                    // the admin user will be able to see them
                                    // and they have a little "approve" link to 
                                    // release them to the public.  This only 
                                    // works if $password and $guestcomment are on

$guestcomment =  false;             // If true, then guests can leave comments
$recentcount  =  10;                // How many 'recent' comments to show
$email        = "";                 // If you put your email address here then
                                    // new guest comments will be mailed to you
                                    // ... PHP must already support mail.
$timeoffset   =  0;                 // By how many hours do you want to offset the 
                                    // the server time when displaying messages.
                                    // 0 = server time, "8.5" = add 8:30, "-4" = subtract 4:00

$imagemethod   = "gd2";             // gd1, gd2, imagemagick, manual, none  
                                    // gd1 - gd 1.* compiled into PHP
                                    // gd2 - gd 2.* compiled into PHP 4.0.6 or later
                                    // imagemagick - use imagemagick "convert" and "identify"
                                    // manual - create your own thumbnails elsewhere
                                    // none - don't use thumbnail images

$captionmethod = "rdjpgcom";        // rdjpgcom, imagemagick, capfile, filename, none
                                    // rdjpgcom - use rdjpgcom program, part of libjpeg
                                    // imagemagick - use imagemagick program "identify"
                                    // capfile - use thumb/filename.jpg.cap files
                                    // filename - just use the filename as the caption
                                    // none - don't use any captions on images

// Depending on what you chose for $imagemethod and $captionmethod, set these:
$convert  = "/usr/bin/convert";     // Path to Imagemagick "convert" program
$identify = "/usr/bin/identify";    // Path to Imagemagick "identify" program
$rdjpgcom = "/usr/local/bin/rdjpgcom";    // Path to rdjpgcom program, if needed.
$wrjpgcom = "/usr/local/bin/wrjpgcom";    // If you are using rdjpgcom, set this too 
                                    // so admin can update (write) image captions

////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
/// Normally, you won't need to change anything below here
////////////////////////////////////////////////////////////////////////////////

if (file_exists("config.php")) {    // Settings in here will override defaults
   include("config.php");
}

if (isset($HTTP_SERVER_VARS["PATH_TRANSLATED"])) {  // Name of this file
   $scriptname = basename($HTTP_SERVER_VARS["PATH_TRANSLATED"]);
} else {
   $scriptname = "index.php";
}

/// Language Strings ///

// English - en
$ss["en"]["next"]    = "Next";
$ss["en"]["prev"]    = "Previous";
$ss["en"]["slide"]   = "Slideshow";
$ss["en"]["comments"]= "Comments";
$ss["en"]["name"]    = "Name";
$ss["en"]["message"] = "Message";
$ss["en"]["add"]     = "Add my message to this page";
$ss["en"]["start"]   = "Back to the start";
$ss["en"]["selectl"] = "Select images from the left";
$ss["en"]["selectr"] = "Select images from the right";
$ss["en"]["from"]    = "From";
$ss["en"]["recent"]  = "most recent guest comments";
$ss["en"]["seconds"] = "seconds";
$ss["en"]["original"]= "Click here to see the full-sized version of this image";

// Deutsch - de
$ss["de"]["next"]    = "Nächstes";
$ss["de"]["prev"]    = "Vorheriges";
$ss["de"]["slide"]   = "Slideshow";
$ss["de"]["comments"]= "Kommentare";
$ss["de"]["name"]    = "Name";
$ss["de"]["message"] = "Kommentar";
$ss["de"]["add"]     = "Meinen Kommentar abschicken";
$ss["de"]["start"]   = "zum Anfang";
$ss["de"]["selectl"] = "Bitte wählen sie links ein Bild aus";
$ss["de"]["selectr"] = "Bitte wählen sie rechts ein Bild aus";
$ss["de"]["from"]    = "Von";
$ss["de"]["recent"]  = "neueste Gastanmerkungen";
$ss["de"]["seconds"] = "Sekunden";
$ss["de"]["original"]= "Click here to see the full-sized version of this image";

// Nederlands - nl
// Thanks to Rob Heijmen!
$ss["nl"]["next"]    = "Volgende";
$ss["nl"]["prev"]    = "Vorige";
$ss["nl"]["slide"]   = "Diashow";
$ss["nl"]["comments"]= "Commentaar";
$ss["nl"]["name"]    = "Naam";
$ss["nl"]["message"] = "Melding";
$ss["nl"]["add"]     = "Voeg mijn melding toe";
$ss["nl"]["start"]   = "Terug naar het begin";
$ss["nl"]["selectl"] = "Selecteer afbeeldingen aan de linker zijde";
$ss["nl"]["selectr"] = "Selecteer afbeeldingen aan de rechter zijde";
$ss["nl"]["from"]    = "Van";
$ss["nl"]["recent"]  = "meest recente commentaar";
$ss["nl"]["seconds"] = "seconden";
$ss["nl"]["original"]= "Klik hier om de foto in z'n originele formaat te zien";

// Francais - fr
// Thanks to Olivier Paul!
$ss["fr"]["next"]    = "Suivant";
$ss["fr"]["prev"]    = "Précédent";
$ss["fr"]["slide"]   = "Présentation";
$ss["fr"]["comments"]= "Commentaires";
$ss["fr"]["name"]    = "Nom";
$ss["fr"]["message"] = "Message";
$ss["fr"]["add"]     = "Ajouter mon message à cette page";
$ss["fr"]["start"]   = "Retour au départ";
$ss["fr"]["selectl"] = "Sélectionnez les images à partir de la gauche";
$ss["fr"]["selectr"] = "Sélectionnez les images à partir de la droite";
$ss["fr"]["from"]    = "De";
$ss["fr"]["recent"]  = "commentaires les plus récents de visiteurs";
$ss["fr"]["seconds"] = "secondes";
$ss["fr"]["original"]= "Click here to see the full-sized version of this image";

// Swedish - se
// Thanks to Jörgen Silverplatz!
$ss["se"]["next"]    = "N&auml;sta";
$ss["se"]["prev"]    = "F&ouml;reg&aring;ende";
$ss["se"]["slide"]   = "Bildvisning";
$ss["se"]["comments"]= "Kommentarer";
$ss["se"]["name"]    = "Namn";
$ss["se"]["message"] = "Meddelande";
$ss["se"]["add"]     = "L&auml;mna ett meddelande";
$ss["se"]["start"]   = "Till b&ouml;rjan";
$ss["se"]["selectl"] = "V&auml;lj bild från v&auml;nster";
$ss["se"]["selectr"] = "V&auml;lj bild från h&ouml;ger";
$ss["se"]["from"]    = "Fr&aring;n";
$ss["se"]["recent"]  = "Senaste g&auml;st kommentarerna";
$ss["se"]["seconds"] = "sekunder";
$ss["se"]["original"]= "Click here to see the full-sized version of this image";
 
// Spanish - es
// Thanks to Eddie Galvez!
$ss["es"]["next"]    = "Próximo";
$ss["es"]["prev"]    = "Previo";
$ss["es"]["slide"]   = "Slideshow";
$ss["es"]["comments"]= "Comentarios";
$ss["es"]["name"]    = "Nombre";
$ss["es"]["message"] = "Mensaje";
$ss["es"]["add"]     = "Agrega mi mensaje a esta página";
$ss["es"]["start"]   = "Volver al comienzo";
$ss["es"]["selectl"] = "Selecciona imagenes a la izquierda";
$ss["es"]["selectr"] = "Selecciona imagenes a la derecha";
$ss["es"]["from"]    = "De";
$ss["es"]["recent"]  = "comentarios mas recientes";
$ss["es"]["seconds"] = "segundos";
$ss["es"]["original"]= "Click here to see the full-sized version of this image";

// Russia - ru
// Thanks to Shamil!
$ss["ru"]["next"]    = "÷ÐÅÒÅÄ";
$ss["ru"]["prev"]    = "îÁÚÁÄ";
$ss["ru"]["slide"]   = "óÌÁÊÄ ÛÏÕ";
$ss["ru"]["comments"]= "ëÏÍÅÎÔÁÒÉÉ";
$ss["ru"]["name"]    = "éÍÑ";
$ss["ru"]["message"] = "óÏÏÂÝÅÎÉÅ";
$ss["ru"]["add"]     = "äÏÂÁ×ÉÔØ ÓÏÏÂÝÅÎÉÅ ÎÁ ÜÔÏÊ ÓÔÒÁÎÉÃÅ";
$ss["ru"]["start"]   = "ë ÎÁÞÁÌÕ";
$ss["ru"]["selectl"] = "÷ÙÂÅÒÉÔÅ ÆÏÔÏÒÇÁÆÉÀ × ÌÅ×ÏÊ ÐÁÎÅÌÉ";
$ss["ru"]["selectr"] = "÷ÙÂÅÒÉÔÅ ÆÏÔÏÒÇÁÆÉÀ × ÐÒÁ×ÏÊ ÐÁÎÅÌÉ";
$ss["ru"]["from"]    = "ïÔ";
$ss["ru"]["recent"]  = "ÐÏÓÌÅÄÎÉÈ ÓÏÏÂÝÅÎÉÊ";
$ss["ru"]["seconds"] = "óÅËÕÎÄ";
$ss["ru"]["original"]= "Click here to see the full-sized version of this image";

// Italiano - it
// Thanks to Sal!
$ss["it"]["next"]    = "Dopo";
$ss["it"]["prev"]    = "Precedente";
$ss["it"]["slide"]   = "Proiezione";
$ss["it"]["comments"]= "Commenti";
$ss["it"]["name"]    = "Nome";
$ss["it"]["message"] = "Messaggio";
$ss["it"]["add"]     = "Aggiungere il mio messaggio a questa pagina";
$ss["it"]["start"]   = "Inizio";
$ss["it"]["selectl"] = "Seleziona le immagini a partire da sinistra";
$ss["it"]["selectr"] = "Seleziona le immagini a partire da destra";
$ss["it"]["from"]    = "Da";
$ss["it"]["recent"]  = "I commenti più recenti";
$ss["it"]["seconds"] = "Secondi";
$ss["it"]["original"]= "Click here to see the full-sized version of this image";

// Polish - pl
// Thanks to Wojtek Fraczak!
$ss["pl"]["next"]    = "Nastepne";
$ss["pl"]["prev"]    = "Poprzednie";
$ss["pl"]["slide"]   = "Prezentacja";
$ss["pl"]["comments"]= "Komentarze";
$ss["pl"]["name"]    = "Imie";
$ss["pl"]["message"] = "Komentarz";
$ss["pl"]["add"]     = "Dorzuc komentarz";
$ss["pl"]["start"]   = "Wroc na poczatek";
$ss["pl"]["selectl"] = "Wybierz zdjecie z lewej strony";
$ss["pl"]["selectr"] = "Wybierz zdjecie z prawej strony";
$ss["pl"]["from"]    = "From";
$ss["pl"]["recent"]  = "Ostatnie komentarze";
$ss["pl"]["seconds"] = "sekund";
$ss["pl"]["original"]= "Click here to see the full-sized version of this image";

// Norwegian - no
// Thanks to Christian Rambjør!
$ss["no"]["next"]    = "Neste";
$ss["no"]["prev"]    = "Forrige";
$ss["no"]["slide"]   = "Bildevisning";
$ss["no"]["comments"]= "Kommentarer";
$ss["no"]["name"]    = "Navn";
$ss["no"]["message"] = "Melding";
$ss["no"]["add"]     = "Legg igjen en melding";
$ss["no"]["start"]   = "Til Begynnelsen";
$ss["no"]["selectl"] = "Velg bilder fra venstre side";
$ss["no"]["selectr"] = "Velg bilder fra høyre side";
$ss["no"]["from"]    = "Fra";
$ss["no"]["recent"]  = "Siste kommentarene";
$ss["no"]["seconds"] = "Sekunder";
$ss["no"]["original"]= "Click here to see the full-sized version of this image";

// Danish - dk
// Thanks to Steffen Meyer!
$ss["dk"]["next"]    = "N&aelig;ste";
$ss["dk"]["prev"]    = "Forrige";
$ss["dk"]["slide"]   = "Diasshow";
$ss["dk"]["comments"]= "Kommentarer";
$ss["dk"]["name"]    = "Navn";
$ss["dk"]["message"] = "Kommentar";
$ss["dk"]["add"]     = "Skriv en kommentar";
$ss["dk"]["start"]   = "Til start";
$ss["dk"]["selectl"] = "V&aelig;lg billeder fra venstre";
$ss["dk"]["selectr"] = "V&aelig; billeder fra h&oslash;jre";
$ss["dk"]["from"]    = "Fra";
$ss["dk"]["recent"]  = "Seneste kommentarer";
$ss["dk"]["seconds"] = "Sekunder";
$ss["dk"]["original"]= "Click here to see the full-sized version of this image";

// Estonian - ee
// Thanks to Margo Poolak!
$ss["ee"]["next"]    = "Järgmine";
$ss["ee"]["prev"]    = "Eelmine";
$ss["ee"]["slide"]   = "Slaidid";
$ss["ee"]["comments"]= "Kommentaarid";
$ss["ee"]["name"]    = "Nimi";
$ss["ee"]["message"] = "Teated";
$ss["ee"]["add"]     = "Lisa";
$ss["ee"]["start"]   = "Tagasi algusesse";
$ss["ee"]["selectl"] = "Vali pildid vasakult";
$ss["ee"]["selectr"] = "Vali pildid paremalt";
$ss["ee"]["from"]    = "Tulnud";
$ss["ee"]["recent"]  = "Viimased kommentaarid";
$ss["ee"]["seconds"] = "Sekundid";
$ss["ee"]["original"]= "Click here to see the full-sized version of this image";

// Greek - gr
// Thanks to Petros Papaioannou!
$ss["gr"]["next"]    = "Åðüìåíï";
$ss["gr"]["prev"]    = "Ðñïçãïýìåíï";
$ss["gr"]["slide"]   = "ÐñïâïëÞ äéáöáíåéþí";
$ss["gr"]["comments"]= "Ó÷üëéá";
$ss["gr"]["name"]    = "¼íïìá";
$ss["gr"]["message"] = "ÌÞíõìá";
$ss["gr"]["add"]     = "Ðñüóèåóå ôï ìÞíõìÜ ìïõ ó'áõôÞí ôç óåëßäá";
$ss["gr"]["start"]   = "Ðßóù óôçí áñ÷Þ";
$ss["gr"]["selectl"] = "ÅðÝëåîå ôéò åéêüíåò áðü ôá áñéóôåñÜ";
$ss["gr"]["selectr"] = "ÅðÝëåîå ôéò åéêüíåò áðü ôá äåîéÜ";
$ss["gr"]["from"]    = "Áðü";
$ss["gr"]["recent"]  = "ðéï ðñüóöáôá ó÷üëéá åðéóêåðôþí";
$ss["gr"]["seconds"] = "äåõôåñüëåðôá";
$ss["gr"]["original"]= "Click here to see the full-sized version of this image";

// Portugese - br
// Thanks to Påulo ©avoto!
$ss["br"]["next"]    = "Próximo";
$ss["br"]["prev"]    = "Anterior";
$ss["br"]["slide"]   = "Slideshow";
$ss["br"]["comments"]= "Comentários";
$ss["br"]["name"]    = "Nome";
$ss["br"]["message"] = "Mensagem";
$ss["br"]["add"]     = "Adicionar meu comentário a essa página";
$ss["br"]["start"]   = "Voltar ao início";
$ss["br"]["selectl"] = "Selecionar imagens pela esquerda";
$ss["br"]["selectr"] = "Selecionar imagens pela right";
$ss["br"]["from"]    = "De";
$ss["br"]["recent"]  = "comentários mais recentes";
$ss["br"]["seconds"] = "segundos";
$ss["br"]["original"]= "Clice aqui para ver a imagem no seu tamanho original";

// Portugese - pt
// Thanks to Carlos Ribeiro!
$ss["pt"]["next"]    = "Próximo";
$ss["pt"]["prev"]    = "Anterior";
$ss["pt"]["slide"]   = "Slideshow";
$ss["pt"]["comments"]= "Comentários";
$ss["pt"]["name"]    = "Nome";
$ss["pt"]["message"] = "Mensagem";
$ss["pt"]["add"]     = "Adicionar a minha mensagem";
$ss["pt"]["start"]   = "Voltar ao início";
$ss["pt"]["selectl"] = "Selecione as imagens da esquerda";
$ss["pt"]["selectr"] = "Selecione as imagens da direita";
$ss["pt"]["from"]    = "De";
$ss["pt"]["recent"]  = "comentários mais recentes dos visitantes";
$ss["pt"]["seconds"] = "segundos";
$ss["pt"]["original"]= "Clice aqui para ver a imagem no seu tamanho original";


// Chinese (Simplified) gb2312
// Thanks to weijh!
$ss["gb"]["next"]    = "ÏÂÒ»ÕÅ";
$ss["gb"]["prev"]    = "Ç°Ò»ÕÅ";
$ss["gb"]["slide"]   = "»ÃµÆÆ¬ÏÔÊ¾";
$ss["gb"]["comments"]= "ÆÀÂÛ";
$ss["gb"]["name"]    = "Ãû×Ö";
$ss["gb"]["message"] = "ÄÚÈÝ";
$ss["gb"]["add"]     = "¼ÓÈëÎÒ¶ÔÍ¼Æ¬µÄÆÀÂÛ";
$ss["gb"]["start"]   = "ÖØÍ·¿ªÊ¼¿´";
$ss["gb"]["selectl"] = "´Ó×ó±ßÑ¡ÔñÍ¼Æ¬";
$ss["gb"]["selectr"] = "´ÓÓÒ±ßÑ¡ÔñÍ¼Æ¬";
$ss["gb"]["from"]    = "¿´¿Í";
$ss["gb"]["recent"]  = "¸ö×îÐÂµÄ¿´¿ÍÁôÑÔ";
$ss["gb"]["seconds"] = "Ãë";
$ss["gb"]["original"]= "Click here to see the full-sized version of this image";

// Bosanski - ba
// Thanks to E C!
$ss["ba"]["next"]    = "Slijedeca";
$ss["ba"]["prev"]    = "Prosla";
$ss["ba"]["slide"]   = "Automatski pregled";
$ss["ba"]["comments"]= "Komentar";
$ss["ba"]["name"]    = "Ime";
$ss["ba"]["message"] = "Poruka";
$ss["ba"]["add"]     = "Proslijedi poruku";
$ss["ba"]["start"]   = "Nazad na pocetak";
$ss["ba"]["selectl"] = "Selektiraj slike na lijevoj strani";
$ss["ba"]["selectr"] = "Selektiraj slike na desnoj strani";
$ss["ba"]["from"]    = "Od";
$ss["ba"]["recent"]  = "Resentni komentar";
$ss["ba"]["seconds"] = "sekundi";
$ss["ba"]["original"]= "Click here to see the full-sized version of this image";

// Turkish - tr
// Thanks to Önder Cardakli!
$ss["tr"]["next"]    = "Ileri";
$ss["tr"]["prev"]    = "Geri";
$ss["tr"]["slide"]   = "Slideshow";
$ss["tr"]["comments"]= "Düsünceleriniz";
$ss["tr"]["name"]    = "Adiniz, Soyadiniz";
$ss["tr"]["message"] = "Kommentar";
$ss["tr"]["add"]     = "Düsüncelerimi yolla";
$ss["tr"]["start"]   = "Anasayfa";
$ss["tr"]["selectl"] = "Lütfen sol tarafdan bir Fotoraf secin";
$ss["tr"]["selectr"] = "Lütfen sag tarafdan bir Fotoraf secin";
$ss["tr"]["from"]    = "Yazar";
$ss["tr"]["recent"]  = "En son eklenen";
$ss["tr"]["seconds"] = "Saniye";
$ss["tr"]["original"]= "büyültmek icin lütfen buraya tikla";

// Catala - ca
// Thanks to Àngel "muzzol" Bosch
$ss["ca"]["next"]    = "Següent";
$ss["ca"]["prev"]    = "Anterior";
$ss["ca"]["slide"]   = "Projecció de diapositives";
$ss["ca"]["comments"]= "Comentaris";
$ss["ca"]["name"]    = "Nom";
$ss["ca"]["message"] = "Missatge";
$ss["ca"]["add"]     = "Afegeix el meu missatge a la pàgina";
$ss["ca"]["start"]   = "Tornar al començament";
$ss["ca"]["selectl"] = "Selecciona imatges a l'esquerra";
$ss["ca"]["selectr"] = "Selecciona imatges a la dreta";
$ss["ca"]["from"]    = "De";
$ss["ca"]["recent"]  = "comentaris mes recents";
$ss["ca"]["seconds"] = "segons";
$ss["ca"]["original"]= "Fes un clic per veure aquesta imatge amb el seu tamany original";

// Bulgarian - bg
// Thanks to George Zlatanov!
$ss["bg"]["next"]    = "Ñëåäâàùà";
$ss["bg"]["prev"]    = "Ïðåäèøíà";
$ss["bg"]["slide"]   = "Àâòîìàòè÷íî";
$ss["bg"]["comments"]= "Êîìåíòàðè";
$ss["bg"]["name"]    = "Èìå";
$ss["bg"]["message"] = "Êîìåíòàð";
$ss["bg"]["add"]     = "Ïðèáàâåòå ìîÿ êîìåíòàð êúì òàçè ñòðàíèöà";
$ss["bg"]["start"]   = "Â íà÷àëîòî";
$ss["bg"]["selectl"] = "Èçáåðåòå êàðòèíà îò ëÿâî";
$ss["bg"]["selectr"] = "Èçáåðåòå êàðòèíà îò äÿñíî";
$ss["bg"]["from"]    = "Îò";
$ss["bg"]["recent"]  = "Ïîñëåäíè êîìåíòàðè";
$ss["bg"]["seconds"] = "Ñåêóíäè";
$ss["bg"]["original"]= "Íàòèñíåòå òóê çà äà âèäèòå êàðòèíàòà â ðåàëåí ðàçìåð";

// Finnish - fi
// Thanks to Ante Mulari and Tuomas Vanhanen!
$ss["fi"]["next"]    = "Seuraava";
$ss["fi"]["prev"]    = "Edellinen";
$ss["fi"]["slide"]   = "Kuvaesitys";
$ss["fi"]["comments"]= "Kommentit";
$ss["fi"]["name"]    = "Nimi";
$ss["fi"]["message"] = "Viesti";
$ss["fi"]["add"]     = "Lisää viestini tälle sivulle";
$ss["fi"]["start"]   = "Takaisin alkuun";
$ss["fi"]["selectl"] = "Valitse kuvat vasemmalta";
$ss["fi"]["selectr"] = "Valitse kuvat oikealta";
$ss["fi"]["from"]    = "Lähettäjä";
$ss["fi"]["recent"]  = "viimeisintä kommentia kävijöiltä";
$ss["fi"]["seconds"] = "sekuntia";
$ss["fi"]["original"]= "Klikkaa tästä nähdäksesi kuvan alkuperäisessä koossa";

// Afrikaans - af
// Thanks to Andreas Pauley!
$ss["af"]["next"]    = "Volgende";
$ss["af"]["prev"]    = "Vorige";
$ss["af"]["slide"]   = "Skyfievertoning";
$ss["af"]["comments"]= "Kommentaar";
$ss["af"]["name"]    = "Naam";
$ss["af"]["message"] = "Boodskap";
$ss["af"]["add"]     = "Voeg my boodskap by hierdie bladsy";
$ss["af"]["start"]   = "Terug na die begin";
$ss["af"]["selectl"] = "Kies prentjies van die linkerkant";
$ss["af"]["selectr"] = "Kies prentjies van die regterkant";
$ss["af"]["from"]    = "Van";
$ss["af"]["recent"]  = "onlangse besoeker kommentaar";
$ss["af"]["seconds"] = "sekondes";
$ss["af"]["original"]= "Kliek hier vir die vol-grootte prentjie";

// Magyar - hu
// Thanks to Kovács Zoltán!
$ss["hu"]["next"]    = "Következö";
$ss["hu"]["prev"]    = "Elözö";
$ss["hu"]["slide"]   = "Diavetítés";
$ss["hu"]["comments"]= "Megjegyzés";
$ss["hu"]["name"]    = "Név";
$ss["hu"]["message"] = "Üzenet";
$ss["hu"]["add"]     = "Üzenet elküldése";
$ss["hu"]["start"]   = "Vissza az elejére";
$ss["hu"]["selectl"] = "Válassza ki a képeket a bal oldalról";
$ss["hu"]["selectr"] = "Válassza ki a képeket a jobb oldalról";
$ss["hu"]["from"]    = "Feladó";
$ss["hu"]["recent"]  = "Legutóbbi üzenetek";
$ss["hu"]["seconds"] = "másodperc";
$ss["hu"]["original"]= "Kattintson a kép teljes méretü megjelenítéséhez";

// Arabic - ar
// Thanks to Bingo Baghdad !
$ss["ar"]["next"]    = "ÇáÊøÇáí";
$ss["ar"]["prev"]    = "ÇáÓøÇÈÞ";
$ss["ar"]["slide"]   = "ÇáÊøÚÇÞÈ";
$ss["ar"]["comments"]= "ÊÚáíÞÇÊ";
$ss["ar"]["name"]    = "ÇáÅÓã";
$ss["ar"]["message"] = "ÇáÑøÓÇáÉ";
$ss["ar"]["add"]     = "ÅÖÇÝÉ ÑÓÇáÊí Åáì åÐå ÇáÕøÝÍÉ";
$ss["ar"]["start"]   = "ÇáÚæÏÉ Åáì ÇáÈÏÇíÉ";
$ss["ar"]["selectl"] = "ÅÎÊÑ ÇáÕøæÑ ÈÏÇíÉ ãä ÇáíÓÇÑ";
$ss["ar"]["selectr"] = "ÅÎÊÑ ÇáÕøæÑ ÈÏÇíÉ ãä Çáíãíä";
$ss["ar"]["from"]    = "ÈÞáã";
$ss["ar"]["recent"]  = "ÊÚáíÞÇÊ ÇáÃÎíÑÉ ãä ØÑÝ ÇáÒøæÇÑ";
$ss["ar"]["seconds"] = "ËæÇäí";
$ss["ar"]["original"]= "ÃäÞÑ åäÇ áÑõÄíÉÇáÕøæÑÉ ÈãÞÇííÓåÇ ÇáÃÕáíøÉ";

// Romanian - ro
// Thanks to Barbu Sorin
$ss["ro"]["next"]    = "Urmatoarea";
$ss["ro"]["prev"]    = "Anterioara";
$ss["ro"]["slide"]   = "Slideshow";
$ss["ro"]["comments"]= "Comentarii";
$ss["ro"]["name"]    = "Nume";
$ss["ro"]["message"] = "Mesaj";
$ss["ro"]["add"]     = "Adauga mesajul meu pe aceasta pagina";
$ss["ro"]["start"]   = "Inapoi la start";
$ss["ro"]["selectl"] = "Selecteaza imaginile de la stanga";
$ss["ro"]["selectr"] = "Selecteaza imaginile de la dreapta";
$ss["ro"]["from"]    = "De la";
$ss["ro"]["recent"]  = "cele mai recente comentarii";
$ss["ro"]["seconds"] = "secunde";
$ss["ro"]["original"]= "Apasa aici pentru versiunea marita a acestei imagini"; 

// Icelandic - is
// Thanks to Dagur Hilmarsson
$ss["is"]["next"]    = "Næsta";
$ss["is"]["prev"]    = "Fyrri";
$ss["is"]["slide"]   = "Myndasýning";
$ss["is"]["comments"]= "Ummæli";
$ss["is"]["name"]    = "Nafn";
$ss["is"]["message"] = "Skilaboð";
$ss["is"]["add"]     = "Senda inn ummæli";
$ss["is"]["start"]   = "Aftur á byrjun";
$ss["is"]["selectl"] = "Veldu mynd á vinstri hlið";
$ss["is"]["selectr"] = "Veldu mynd á hægri hlið";
$ss["is"]["from"]    = "Frá";
$ss["is"]["recent"]  = "nýjustu skilaboð frá gestum";
$ss["is"]["seconds"] = "sek.";
$ss["is"]["original"]= "Ýttu hér til að sjá mynd í fullri stærð";

// Latvian - lv
// Thanks to Angelika
$ss["lv"]["next"]    = "Nâkoðais";
$ss["lv"]["prev"]    = "Iepriekðçjais";
$ss["lv"]["slide"]   = "Slideshow";
$ss["lv"]["comments"]= "Komentâri";
$ss["lv"]["name"]    = "Vârds";
$ss["lv"]["message"] = "Vçstule";
$ss["lv"]["add"]     = "Pievienot manu vçstuli";
$ss["lv"]["start"]   = "Uz sâkumu";
$ss["lv"]["selectl"] = "Bildes atlasît no kreisâs puses";
$ss["lv"]["selectr"] = "Bildes atlasît no labâs puses";
$ss["lv"]["from"]    = "No";
$ss["lv"]["recent"]  = "Jaunâkie komentâri";
$ss["lv"]["seconds"] = "sekundes";
$ss["lv"]["original"]= "Klikðíini ðeit, lai bildi redzçtu normâlâ lielumâ";


// Please send me more translations! :-)
////////////////////////////////////////////////////////////////////////////////


/// Convert any external variables into internal ones
/// (avoids warnings on secure installations)

$admin = isset($HTTP_GET_VARS["admin"]) ? $HTTP_GET_VARS["admin"] : "";
if (! $admin) {
    $admin = isset($HTTP_POST_VARS["admin"]) ? $HTTP_POST_VARS["admin"] : "";
}
$editcaption = isset($HTTP_GET_VARS["editcaption"]) ? $HTTP_GET_VARS["editcaption"] : "";
if (! $editcaption) {
    $editcaption = isset($HTTP_POST_VARS["editcaption"]) ? $HTTP_POST_VARS["editcaption"] : "";
}
$newcaption = isset($HTTP_POST_VARS["newcaption"]) ? $HTTP_POST_VARS["newcaption"] : "";
$upload = isset($HTTP_POST_VARS["upload"]) ? $HTTP_POST_VARS["upload"] : "";
$uploadcount = isset($HTTP_POST_VARS["uploadcount"]) ? $HTTP_POST_VARS["uploadcount"] : 0;
$imagefile = isset($HTTP_POST_FILES["imagefile"]) ? $HTTP_POST_FILES["imagefile"] : "";
$logout = isset($HTTP_GET_VARS["logout"]) ? $HTTP_GET_VARS["logout"] : "";
$intro = isset($HTTP_GET_VARS["intro"]) ? $HTTP_GET_VARS["intro"] : "";
$recent = isset($HTTP_GET_VARS["recent"]) ? $HTTP_GET_VARS["recent"] : "";
$margin = isset($HTTP_GET_VARS["margin"]) ? $HTTP_GET_VARS["margin"] : "";
$image = isset($HTTP_GET_VARS["image"]) ? $HTTP_GET_VARS["image"] : "";
$deleteimage   = isset($HTTP_GET_VARS["deleteimage"]) ? $HTTP_GET_VARS["deleteimage"] : "";
$dcomment = isset($HTTP_GET_VARS["dcomment"]) ? $HTTP_GET_VARS["dcomment"] : "";
$ctype = isset($HTTP_GET_VARS["ctype"]) ? $HTTP_GET_VARS["ctype"] : "";
$acomment = isset($HTTP_GET_VARS["acomment"]) ? $HTTP_GET_VARS["acomment"] : "";
$originalimage = isset($HTTP_GET_VARS["originalimage"]) ? $HTTP_GET_VARS["originalimage"] : "";
$slide = isset($HTTP_GET_VARS["slide"]) ? $HTTP_GET_VARS["slide"] : "0";

$comment = isset($HTTP_POST_VARS["comment"]) ? $HTTP_POST_VARS["comment"] : "";
$name = isset($HTTP_POST_VARS["name"]) ? $HTTP_POST_VARS["name"] : "";
$message = isset($HTTP_POST_VARS["message"]) ? $HTTP_POST_VARS["message"] : "";
$PFCOOKIE = isset($HTTP_COOKIE_VARS["PFCOOKIE"]) ? $HTTP_COOKIE_VARS["PFCOOKIE"] : "";


/// Check that Image processing is available ///

switch ($imagemethod) {
   case "imagemagick":
      if (!is_executable("$convert")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the Imagemagick program that
                      you specified to convert images ($convert).<BR>
                      Check the configuration of this script and your server.");
          die; 
      }
      if (!is_executable("$identify")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the Imagemagick program that
                      you specified to identify images ($identify).<BR>
                      Check the configuration of this script and your server.");
          die; 
      }
      break;
   case "gd2": 
      if (!function_exists("ImageCreateTrueColor")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the GD 2.* functions that 
                      I need to convert images.  Check the configuration of
                      this script (imagemethod variable) or your system.
                      Make sure that your copy of PHP has been compiled with 
                      GD support and native JPEG support eg<BR>
                      configure --with-gd --with-jpeg ...");
          die;
      }
      break;
   case "gd1": 
      if (!function_exists("ImageCreate")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the GD 1.* functions that 
                      I need to convert images.  Check the configuration of
                      this script (imagemethod variable) or your system.
                      Make sure that your copy of PHP has been compiled with 
                      GD support and native JPEG support eg<BR>
                      configure --with-gd --with-jpeg ...");
          die;
      }
      break;
}

/// Check that support for reading image captions is available

switch ($captionmethod) {
   case "imagemagick":
      if (!is_executable("$identify")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the Imagemagick program that
                      you specified to read image captions ($identify).<BR>
                      Check the configuration of this script and your server.<BR>
                      Maybe you just want to change the captionmethod variable to \"capfile\"");
          die;
      }
      break;
   case "rdjpgcom":
      if (!is_executable("$rdjpgcom")) {
          PrintHeader();
          PrintError("Sorry, but I couldn't find the program that
                      you specified to read image captions ($rdjpgcom).<BR>
                      Check the configuration of this script and your server.<BR>
                      Maybe you just want to change the captionmethod variable to \"capfile\"");
          die;
      }
      break;
}



/// Functions ///

function RemoveNewLines (&$item, $key) {
   $item = chop($item);
}

function GetFileList( $dirname="." ) {   // Finds all the images
   // First check to see if there's a file called $sortfile
   // that contains a sorted list of filenames, one per line
   // otherwise, will default to all files in alphabetical order
   global $sortfile, $sortreverse;

   $files = array(); 

   if (file_exists($sortfile)) {
      $files = file($sortfile);
      array_walk($files, 'RemoveNewLines');
   } else {
      $dir = opendir( $dirname );
      while( $file = readdir( $dir ) ) {
         if (eregi("\.jpe?g$", $file) || 
             eregi("\.gif$", $file) ||  
             eregi("\.png$", $file)) {
             $files[] = $file; 
         }
      }
      if ($sortreverse) {
          rsort($files);
      } else {
          sort($files);
      }
   }
   return $files; 
} 

function GetNeighbours($imagelist, $currimage, &$previmage, &$nextimage) {
   // For a given image, return the next and previous ones
   $lastimage = count($imagelist) - 1;
   for ( $i=0; $i<=$lastimage; $i++) {
       if ($imagelist[$i] == $currimage) {
           if ($i == 0) { 
               $nextimage = $imagelist[$i+1];
               $previmage = NULL;
               return;
           } else if ($i == $lastimage) {
               $previmage = $imagelist[$i-1];
               $nextimage = NULL;
               return;
           } else {
               $previmage = $imagelist[$i-1];
               $nextimage = $imagelist[$i+1];
               return;
           }
       }
   }
   $previmage = NULL;
   $nextimage = NULL;
   return;

}

function PrintError($message) {
   echo "<P ALIGN=CENTER><FONT COLOR=RED>Error: $message</FONT></P>";
}

function PrintHeader($pagetitle="", $meta="") {
// Special case when meta = "margin"
   global $headerfile;
   global $stylesheet;
   global $lang;
   global $pagecolor, $margincolor, $textcolor, $linkcolor, $vlinkcolor, $background;

   if ($lang == "ar") {   /// Hack for Arabic, Hebrew and other right-to-left languages
      echo "<HTML dir=RTL>";
   }
   echo "<HEAD>\n";
   echo "<TITLE>$pagetitle</TITLE>\n";
   echo "<META HTTP-EQUIV=\"content-language\" CONTENT=\"$lang\">\n";
   if ($meta and $meta != "margin") {
       echo "$meta\n";
   }
   if ($stylesheet) { 
       echo "<LINK REL=\"stylesheet\" HREF=\"$stylesheet\">";
   }
   echo "</HEAD>\n";

   if ($meta == "margin" and $margincolor) {
       echo "<BODY BGCOLOR=\"$margincolor\" TEXT=\"$textcolor\" ";
   } else {
       echo "<BODY BGCOLOR=\"$pagecolor\" TEXT=\"$textcolor\" ";
   }
   echo " LINK=\"$linkcolor\" VLINK=\"$vlinkcolor\" BACKGROUND=\"$background\" BGPROPERTIES=\"fixed\">\n";
   
   if ($meta != "margin" && file_exists($headerfile)) { 
       include($headerfile); 
   }
}


function PrintFooter() {
   global $footerfile;
   global $title;

   if (file_exists($footerfile)) { 
       include($footerfile); 
   } else {
       echo "<CENTER><BR><P><FONT SIZE=-1>";
       echo "<A TITLE=\"Back to the introduction page\" TARGET=_parent HREF=\".\" >$title</A></P>\n";
   }
   echo "</BODY>";
}


function PrintRecentComments($recent) {
   global $thumb, $marginsize, $marginside, $scriptname;
   global $ss, $lang, $timeoffset;

   if (! $recent) {
      return;
   }

   $imagelist = GetFileList();

   $comments = array();

   foreach ($imagelist as $filename) {
      $commentfile = "$thumb/$filename.txt";
      if (file_exists($commentfile)) {
         $file = file($commentfile);
         foreach ($file as $line) {
            $line = chop($line)."###$filename";
            $comments[] = $line;
         }
      }
   }
   rsort($comments);
   $count = 0;

   echo "</CENTER></P>";   // Just in case;
   echo "<BR><H3 ALIGN=CENTER>$recent ".$ss[$lang]["recent"].":</H3>";
   foreach ($comments as $comment) {
      $comm = explode ("###", $comment);
      $image = $comm[3];
      $caption = GetImageCaption($image, $clean=true);
      $thumbimage = "$thumb/$image";
      $image      = rawurlencode($image);

      echo "<TABLE ALIGN=CENTER WIDTH=\"90%\"><TR><TD COLSPAN=2><HR></TD></TR>";
      echo "<TR>";
      if ($marginside != "right") {
         echo "<TD VALIGN=TOP WIDTH=\"100%\">";
         echo "<FONT SIZE=2>";
         echo $ss[$lang]["from"].": <B>".$comm[1]."</B>, <I>".date("l, j F Y, g:i A", $comm[0]+($timeoffset*3600))."</I><BR>";
         echo "<UL>".$comm[2]."</UL></FONT>";
         if (isadmin()) {
            echo "<P ALIGN=right><FONT SIZE=1><A HREF=\"$scriptname?image=$image&dcomment=".$comm[0]."\">delete</A></FONT></P>";
         }
         echo "</TD>";
      }

      echo "<TD WIDTH=\"$marginsize\">";
      echo "<A TITLE=\"$caption\" HREF=\"$scriptname?image=$image&d=d.html\" TARGET=imain>"; 
      if (file_exists($thumbimage)) {
         echo "<IMG HSPACE=10 ALT=\"$caption\" SRC=\"$thumb/$image\" BORDER=0>";
      } else {
         echo "<FONT SIZE=1>$caption</FONT>";
      }
      echo "</A>";
      echo "</TD>";

      if ($marginside == "right") {
         echo "<TD VALIGN=TOP WIDTH=\"100%\">";
         echo "<FONT SIZE=2>";
         echo $ss[$lang]["from"].": <B>".$comm[1]."</B>, <I>".date("l, j F Y, g:i A", $comm[0]+($timeoffset*3600))."</I><BR>";
         echo "<UL>".$comm[2]."</UL></FONT>";
         if (isadmin()) {
            echo "<P ALIGN=right><FONT SIZE=1><A HREF=\"$scriptname?image=$image&dcomment=".$comm[0]."\">delete</A></FONT></P>";
         }
         echo "</TD>";
      }

      echo "</TR></TABLE>\n";

      $count++;
      if ($count >= $recent) {
         break;
      }
   }
   echo "<HR>\n";
}

function PrintComments($imagefile) {
   // Given an image imagefile, finds, reads and formats the 
   // associated file of guest comments
   global $thumb, $ss, $lang, $timeoffset, $scriptname, $moderate;

   $commentfiles["main"] = "$thumb/$imagefile.txt";
   if ($moderate) {
       $commentfiles["unmoderated"] = "$thumb/$imagefile.mod";
   }

   $filename = rawurlencode($imagefile);

   echo "<BLOCKQUOTE><BLOCKQUOTE><HR>\n";

   foreach ($commentfiles as $commenttype => $commentfile) {
      if ($commenttype == "unmoderated" and !isadmin()) {
          continue;
      }
      if (file_exists($commentfile)) {
         $comments = file($commentfile);
         foreach ($comments as $comment) {
   
            $comm = explode ("###", $comment);
   
            echo "<FONT SIZE=2>";
            echo $ss[$lang]["from"].": <B>".$comm[1]."</B>, <I>".date("l, j F Y, g:i A", $comm[0]+($timeoffset*3600))."</I><BR>";
            echo "<UL>".$comm[2]."</UL></FONT>\n";
            if (isadmin()) {
               echo "<P ALIGN=right><FONT SIZE=1>";
               if ($commenttype == "unmoderated") {
                   echo "<A HREF=\"$scriptname?image=$filename&acomment=".$comm[0]."\">approve</A> ";
                   echo "<A HREF=\"$scriptname?image=$filename&dcomment=".$comm[0]."&ctype=mod\">delete</A>";
               } else {
                   echo "<A HREF=\"$scriptname?image=$filename&dcomment=".$comm[0]."\">delete</A>";
               }
               echo "</FONT></P>";
            }
            echo "<HR SIZE=1>\n";
         }
      }
   }
   echo "</BLOCKQUOTE></BLOCKQUOTE><BR>\n";

}

function DeleteComment($filename, $dcomment, $ctype="") {
   // Given an image filename and a comment ID (time) to 
   // delete, it reads the file and rewrites it, leaving
   // out the specified line.   Returns the deleted line.

   global $thumb, $ss, $lang, $timeoffset, $scriptname;

   if ($ctype == "mod") {
      $commentfile = "$thumb/$filename.mod";
   } else {
      $commentfile = "$thumb/$filename.txt";
   }

   $len = strlen($dcomment);

   if (file_exists($commentfile)) {
      $comments = file($commentfile);
      rename($commentfile, $commentfile.".bak");
      if ($file = fopen ($commentfile, "a")) {
         foreach ($comments as $comment) {
            if (substr($comment, 0, $len) == $dcomment) {
               $deletedcomment = $comment;
            } else {
               fwrite($file, "$comment");
            }
         }
         unlink($commentfile.".bak");
      } else {   // some error occurred ... try to undo the mess
         PrintError("Could not delete comment from file!");
         rename($commentfile.".bak", $commentfile);
      }
   } else {
      PrintError("No comments exist for this image! ($filename)");
   }

   return $deletedcomment;

}


function ApproveComment($filename, $commentid) {
   // Given an image filename and a comment ID (time) to 
   // approve, it moves the comment from the .mod file 
   // to the .txt file

   if ($commentline = DeleteComment($filename, $commentid, "mod")) {
      AddComment($filename, "", "", $commentline);
      echo "<P ALIGN=CENTER>That message has been approved</P>";
   }
}


function PrintCommentForm($filename) {
    global $scriptname, $textcolor, $pagecolor;
    global $ss, $lang;


?>

<FORM ACTION=<?PHP echo $scriptname ?> METHOD=POST>
<INPUT TYPE=hidden NAME=comment VALUE="<?PHP echo $filename?>">
<TABLE BORDER=0 CELLPADDING=2 CELLSPACING=0 ALIGN=CENTER BGCOLOR=#BBBBBB>
<TR><TD><TABLE BORDER=0 CELLPADDING=5 CELLSPACING=0 WIDTH="100%" BGCOLOR="<?PHP echo $pagecolor?>">
   <TR>
       <TD ALIGN=right><FONT SIZE=2 COLOR="<?PHP echo $textcolor?>">
           <B><?PHP echo $ss[$lang]["name"] ?>:</B></FONT></TD>
       <TD><FONT SIZE=2 COLOR="<?PHP echo $textcolor?>">
           <INPUT size=50 type=text name="name"></FONT></TD>
   </TR>
   <TR>
       <TD ALIGN=right valign=top><FONT SIZE=2 COLOR="<?PHP echo $textcolor?>">
           <B><?PHP echo $ss[$lang]["message"] ?>:</B></FONT></TD>
       <TD><FONT SIZE=2 COLOR="<?PHP echo $textcolor?>">
           <TEXTAREA name="message" rows=4 cols=40></TEXTAREA></FONT></TD>
   </TR>
   <TR>
       <TD ALIGN=CENTER COLSPAN=2>
           <FONT SIZE=2 COLOR="<?PHP echo $textcolor?>">
           <INPUT type=submit value="<?PHP echo $ss[$lang]["add"] ?>"></FONT>
       </TD>
   </TR>
</TABLE></TD></TR></TABLE>
</FORM>

<?PHP
}


function AddComment($filename, $name, $message, $messageline="") {
   // Clean up a given comment and add to the appropriate database
   global $thumb, $email, $title, $moderate, $HTTP_SERVER_VARS;

   if (!$REMOTE_HOST = $HTTP_SERVER_VARS["REMOTE_HOST"]) {
       $REMOTE_HOST = $HTTP_SERVER_VARS["REMOTE_ADDR"];
   }
   $SERVER_NAME = $HTTP_SERVER_VARS["SERVER_NAME"];
   $REQUEST_URI = $HTTP_SERVER_VARS["REQUEST_URI"];

   if (ereg( "\\.\\.", $filename)) {  // using ".." in the filename
       PrintError("That filename ($filename) was not secure.");
       return;
   }
            
   if (!$name and !$messageline) {
       PrintError("You need to fill out your name.");
       return;
   }
   if (!$message and !$messageline) {
       PrintError("There was no message to post.");
       return;
   }

   if ($moderate and !$messageline) {
       $commentfile = "$thumb/$filename.mod";
   } else {
       $commentfile = "$thumb/$filename.txt";
   }

   if ($file = fopen ($commentfile, "a") ) {
       if ($messageline) {
          fwrite($file, "$messageline");
           
       } else {
          $timenow = time();
          $name    = strip_tags($name);
          $message = stripslashes(strip_tags($message, "<a><b>"));
   
          if ($email) {   // Try and send the comment via email
              $urlstart = "http://$SERVER_NAME$REQUEST_URI?image=".rawurlencode($filename);
              if ($moderate) {
                 $approveurl = "Approve: $urlstart&acomment=$timenow\n\n";
                 $deleteurl = "Delete: $urlstart&dcomment=$timenow&ctype=mod\n\n";
              } else {
                 $deleteurl = "Delete: $urlstart&dcomment=$timenow\n\n";
              }
              mail($email, "Comment added: $title", 
                   "From: $name ($REMOTE_HOST)\n\n".
                   "$message\n\n".
                   "http://$SERVER_NAME$REQUEST_URI?image=".rawurlencode($filename)."\n\n".
                   $approveurl . $deleteurl,
                   "From: $email");
          }
   
          $message = nl2br($message);
          $message = strtr($message, "\r", " ");
          $message = strtr($message, "\n", " ");
          fwrite($file, "$timenow###$name###$message\n");
       }
       fclose($file);

   } else {
       PrintError("Could not add comment for $filename");
   }
}


function CleanCaption ($caption) {
   $caption = strtr($caption, "\r", " ");
   $caption = strtr($caption, "\n", " ");
   $caption = htmlentities(strip_tags($caption));
   return $caption;
}


function GetImageCaption($image, $clean=false) {
   global $thumb, $captionmethod, $rdjpgcom, $identify;

   $caption = "";

   switch ($captionmethod) {
      case "imagemagick": 
         if (file_exists($image)) {
            Exec("$identify -ping -format \"%c\" \"$image\"", $captionlines);
         } else {
	        return "";
         }
      break;
      case "rdjpgcom": 
         if (file_exists($image)) {
            Exec("$rdjpgcom \"$image\"", $captionlines);
         } else {
	        return "";
         }
      break;
      case "capfile":
         $capfile = "$thumb/$image".".cap";
         if (file_exists($capfile)) {
            $captionlines = file($capfile);
         } else {
	        return "";
         }
      break;
      case "filename":
          return $image;
      break;
      default:
         return "";
      break;
   }

   foreach ($captionlines as $captionline) {
      $caption .= "$captionline ";
   }

   $caption = stripslashes($caption);

   if ($clean) {
      $caption = CleanCaption($caption);
   }

   return $caption;
}

function SetImageCaption($image, $caption) {
   global $thumb, $captionmethod, $wrjpgcom;

   $TEMPFILE = "$thumb/ttttmpfile.jpg";

   if (file_exists($TEMPFILE)) {
      unlink($TEMPFILE);
   }

   $caption = stripslashes($caption);

   switch ($captionmethod) {
      case "rdjpgcom": 
         if (!eregi("\.jpe?g$", $image)) {
            PrintError("$image is probably not a JPEG file");
            return false;
         }
         $command = "$wrjpgcom -replace -comment ".EscapeShellArg($caption)." ".EscapeShellArg($image);
         Exec("$command > $TEMPFILE");
         if (file_exists($TEMPFILE) and filesize($TEMPFILE) > 0) {
            return rename($TEMPFILE, $image);
         }
      break;
      case "capfile":
         $capfile = "$thumb/$image".".cap";
         if (! $file = fopen ($capfile, "w")) {
            PrintError("Could not open the caption file $capfile");
            return false;
         }
         if (! fwrite($file, $caption)) {
            PrintError("Could not write the caption to the caption file $capfile");
            return false;
         }
	     return true;
      break;
   }

   return false;
}

function ReadImageFromFile($filename, $type) {
   $imagetypes = ImageTypes();

   switch ($type) {
      case 1 :
         if ($imagetypes & IMG_GIF)
             return $im = ImageCreateFromGIF($filename);
         break;
      case 2 :
         if ($imagetypes & IMG_JPEG)
             return ImageCreateFromJPEG($filename);
         break;
      case 3 :
         if ($imagetypes & IMG_PNG)
             return ImageCreateFromPNG($filename);
         break;
      default:
         return 0;
   }
}

function WriteImageToFile($im, $filename, $type) {
   global $imagequality;

   switch ($type) {
      case 1 :
         return ImageGIF($im, $filename);
      case 2 :
         return ImageJpeg($im, $filename, $imagequality);
      case 3 :
         return ImagePNG($im, $filename);
      default:
         return false;
   }
}


function ResizeImage($image, $newimage, $newwidth, $newheight=0) {
// Returns true if new image was created, else false
// If newheight is not specified then image is scaled in proportion
// to newwidth.  If newheight is specified, then newheight and newwidth
// represent a bounding box to fit image into
   global $imagemethod;
   
   switch ($imagemethod) {
      case "imagemagick":
         return ResizeImageUsingIM($image, $newimage, $newwidth, $newheight);
      break;
      case "gd1":
      case "gd2":
         return ResizeImageUsingGD($image, $newimage, $newwidth, $newheight);
      break;
      case "manual":
         return true;
      break;
      default:
         return false;
      break;
   }
}

function ResizeImageUsingGD($image, $newimage, $newwidth, $newheight) {
   global $imagemethod;

   $size = GetImageSize($image);
   $width  = $size[0];
   $height = $size[1];
   $type   = $size[2];

   if ($im = ReadImageFromFile($image, $type)) {
      if ($newheight && ($width < $height)) {
         $newwidth = ($newheight / $height) * $width;
      } else {
         $newheight = ($newwidth / $width) * $height;
      }
      if ($imagemethod == "gd2") {
         $im2 = ImageCreateTrueColor($newwidth,$newheight);
      } else {
         $im2 = ImageCreate($newwidth,$newheight);
      }
      if ($imagemethod == "gd2") {
          ImageCopyResampled($im2,$im,0,0,0,0,$newwidth,$newheight,$width,$height);
      } else {
          ImageCopyResized($im2,$im,0,0,0,0,$newwidth,$newheight,$width,$height);
      }

      if (WriteImageToFile($im2, $newimage, $type)) {
          return true;
      }
   }
   return false;
}

function ResizeImageUsingIM($image, $newimage, $newwidth, $newheight) {
   global $identify, $convert, $imagequality;

   Exec("$identify -ping -format \"%w %h\" \"$image\"", $sizeinfo);

   if (! $sizeinfo ) {
      return false;
   }
   $size = explode(" ", $sizeinfo[0]);
   $width  = $size[0];
   $height = $size[1];

   if (!$width) {
      return false;
   }

   if ($newheight && ($width < $height)) {
      $newwidth = ($newheight / $height) * $width;
   } else {
      $newheight = ($newwidth / $width) * $height;
   }

   Exec("$convert -geometry \"$newwidth"."x"."$newheight\" -quality \"$imagequality\" \"$image\" \"$newimage\"");

   return file_exists($newimage);
}


function PrintSlideshowForm ($nextimage, $slide=0) {
   global $scriptname;
   global $ss, $lang;
    
   $common = "$scriptname?image=$nextimage&slide=";
   $options = array (2 => "2 ".$ss[$lang]["seconds"], 
                     5 => "5 ".$ss[$lang]["seconds"], 
                     7 => "7 ".$ss[$lang]["seconds"], 
                     10 => "10 ".$ss[$lang]["seconds"], 
                     20 => "20 ".$ss[$lang]["seconds"], 
                     30 => "30 ".$ss[$lang]["seconds"],
                     40 => "40 ".$ss[$lang]["seconds"], 
                     50 => "50 ".$ss[$lang]["seconds"], 
                     60 => "60 ".$ss[$lang]["seconds"]);

   echo "<FORM NAME=auto>";
   echo "<SELECT NAME=popup onChange=\"window.location=document.auto.popup.options[document.auto.popup.selectedIndex].value\">\n";

   echo "   <OPTION VALUE=\"javascript:void(0)\">".$ss[$lang]["slide"]."...</OPTION>\n";
   foreach ($options as $value => $label) {
      echo "   <OPTION VALUE=\"$common$value\"";
      if ($value == $slide) {
         echo " SELECTED";
      }
      if ($label) {
         echo ">$label</OPTION>\n";
      } else {
         echo ">$value</OPTION>\n";
      }
   }
   echo "</SELECT></FORM>";
}


function isadmin() {
    global $password, $PFCOOKIE;

    if ($password and isset($PFCOOKIE)) {
        if ($PFCOOKIE == md5($password) ) {
            return true;
        }
    }
    return false;
}

function clean_filename($string) {
    $string = eregi_replace("\.\.", "", $string);
    $string = eregi_replace("[^([:alnum:]|\.)]", "_", $string);
    return    eregi_replace("_+", "_", $string);
}



/// Individual pages are created here ///

if ($admin) {
   if (isadmin()) {
       $intro = "true";

   } else if ($admin == $password) {
       $seconds = 60*60*24*354;
       setCookie ('PFCOOKIE', "", time() - 3600, "/");
       setCookie ('PFCOOKIE', md5($admin), time()+$seconds, "/");
       $PFCOOKIE = md5($admin);
       $intro = "true";
       
   } else {
       PrintHeader("Administrator Login");
       echo "<CENTER>";
       echo "<P>&nbsp;</P>";
       echo "<FORM METHOD=POST ACTION=\"$scriptname\">";
       echo "Admin Password: <INPUT NAME=admin TYPE=password SIZE=20>";
       echo "<INPUT TYPE=submit VALUE=\"Enter\">";
       echo "</FORM>";
       exit;
   }
}

if ($logout) {
   if (isadmin()) {
       $seconds = 60*60*24*354;
       setCookie ('PFCOOKIE', "", time() - 3600, "/");
       $PFCOOKIE = NULL;
   }
   $intro = "true";
}

if ($editcaption) {
   PrintHeader("Editing a caption");

   $image = urldecode($editcaption);

   if (!isadmin()) {
       PrintError("This is an admin-only function");
       die;
   }
   if (ereg( "\\.\\.", $image)) {  // using ".." in the filename
       PrintError("That filename ($image) was not secure.");
       die;
   }
   if ($captionmethod == "rdjpgcom" or $captionmethod == "capfile") {
       if ($newcaption) {
           if (! SetImageCaption($image, $newcaption)) {
               PrintError("Could not save the new caption, sorry");
           }
           echo "<CENTER>";
           echo "<P>&nbsp;</P>";
           echo "<P>Caption saved.</P>";
           echo "<P><A HREF=\"$scriptname?image=$editcaption\">Continue</A></P>";
       } else {
           $caption = stripslashes(GetImageCaption($image));
           echo "<CENTER>";
           echo "<P>&nbsp;</P>";
           echo "<P>Caption for \"$image\"</P>";
           echo "<FORM ACTION=$scriptname METHOD=post>";
           echo "<TEXTAREA NAME=newcaption ROWS=3 COLS=50>$caption</TEXTAREA><BR>";
           echo "<INPUT TYPE=hidden NAME=editcaption value=\"$editcaption\">";
           echo "<INPUT TYPE=submit VALUE=\"Save caption\">";
           echo "</FORM>";
       }

   } else {
       PrintError("You can't edit captions unless $captionmethod is rdjpgcom or capfile");
   }
   die;
}


if ($deleteimage) {
   PrintHeader("Deleting an image");

   if (!isadmin()) {
       PrintError("This is an admin-only function");
       die;
   }
   if (ereg( "\\.\\.", $deleteimage)) {  // using ".." in the filename
       PrintError("That filename ($deleteimage) was not secure.");
       die;
   }

   if (! unlink($deleteimage) ) {
       PrintError("An error occurred while deleting the image - try doing it manually.");
   } else {
       echo "<P ALIGN=CENTER>Image deleted</P>";
   }

   if (file_exists("$thumb/$deleteimage")) {
       if (! unlink("$thumb/$deleteimage")) {
           PrintError("An error occurred while deleting the thumbnail ($thumb/$deleteimage)");
       } else {
           echo "<P ALIGN=CENTER>Thumbnail image deleted</P>";
       }
   }
   if (file_exists("$thumb/$imageresize$deleteimage")) {
       if (! unlink("$thumb/$imageresize$deleteimage")) {
           PrintError("An error occurred while deleting the reduced image ($thumb/$imageresize$deleteimage)");
       } else {
           echo "<P ALIGN=CENTER>Reduced size image deleted</P>";
       }
   }
   if (file_exists("$thumb/$deleteimage.txt")) {
       if (! unlink("$thumb/$deleteimage.txt")) {
           PrintError("An error occurred while deleting the comments ($thumb/$deleteimage.txt)");
       } else {
           echo "<P ALIGN=CENTER>Guest comments deleted</P>";
       }
   }
   if (file_exists("$thumb/$deleteimage.cap")) {
       if (! unlink("$thumb/$deleteimage.cap")) {
           PrintError("An error occurred while deleting the caption ($thumb/$deleteimage.cap)");
       } else {
           echo "<P ALIGN=CENTER>Caption file deleted</P>";
       }
   }

   echo "<P ALIGN=CENTER><A TARGET=_parent HREF=\"$scriptname\">Continue</A></P>";
   die;
}

if ($uploadcount) {
   $uploadcount = (int)$uploadcount;
   PrintHeader("Choosing $uploadcount files");
   if (!isadmin()) {
       PrintError("This is an admin-only function");
       die;
   }
   echo "<TABLE ALIGN=CENTER><TR><TD>";
   echo "<FORM ENCTYPE=\"multipart/form-data\" METHOD=\"post\" ACTION=\"$scriptname\">";
   echo "<INPUT TYPE=hidden NAME=MAX_FILE_SIZE value=5000000>";
   echo "<INPUT TYPE=hidden NAME=upload value=true>";
   echo "<DIV ALIGN=RIGHT>";
   for ($i=1; $i<=$uploadcount; $i++) {
       echo " $i. <INPUT NAME=imagefile[] TYPE=file size=30><BR>";
   }
   echo "</DIV><DIV ALIGN=CENTER>";
   echo "<BR><BR><INPUT TYPE=submit NAME=save VALUE=\"Upload these files\">";
   echo "</DIV>";
   echo "</FORM>";
   echo "</TD></TR></TABLE>";
   echo "<P ALIGN=CENTER><A TARGET=_parent HREF=\"$scriptname\">Cancel</A></P>";
   die;
}

if ($upload) {
   PrintHeader("Uploading and storing files");
   if (!isadmin()) {
       PrintError("This is an admin-only function");
       die;
   }
   if (count($imagefile['name'])) {
      foreach ($imagefile['name'] as $key => $name) {
         if (is_uploaded_file($imagefile['tmp_name'][$key]) and $imagefile['size'][$key] > 0) {
            $imagefile_name = clean_filename($name);
            if ($imagefile_name) {
               if (move_uploaded_file($imagefile['tmp_name'][$key], $imagefile_name)) {
                   echo "<P ALIGN=CENTER>Uploaded $imagefile_name [".$imagefile['type'][$key]."]</P>";
               } else {
                   PrintError("Could not store $imagefile_name in this directory");
               }
            } else {
               PrintError("This file had a wierd filename");
            }
         }
      }
      echo "<P ALIGN=CENTER><A TARGET=_parent HREF=\"$scriptname\">Continue</A></P>";
      die;
   }

   $intro = "true";
}


if ($intro) {                       // Print initial info in main frame
   PrintHeader($title);
   if (file_exists($introfile)) { 
       include($introfile);
   } else {
       echo "<CENTER><H1>$title</H1>";
       if ($marginside == "right") {
           echo "<P>". $ss[$lang]["selectr"] ."</P>";
       } else {
           echo "<P>". $ss[$lang]["selectl"] ."</P>";
       }
   }
   if ($guestcomment && $recentcount) {
       echo "<BR>";
       echo "<P align=center><A HREF=\"$scriptname?recent=$recentcount\">";
       echo "$recentcount ". $ss[$lang]["recent"];
       echo "</A></P>";
   }
   if (isadmin()) {
       echo "<HR>";
       echo "<CENTER><P align=center>You are logged in as administrator</P>";
       echo "<FORM METHOD=\"post\" ACTION=\"$scriptname\">";
       echo "Upload <SELECT NAME=uploadcount>\n";

       echo "<OPTION VALUE=\"1\">1 file</OPTION>\n";
       for ($i=2; $i<100; $i++) {
          echo "<OPTION VALUE=\"$i\">$i files</OPTION>\n";
       }
       echo "</SELECT><INPUT TYPE=submit VALUE=\"now\">";

       echo "</FORM>";
       echo "<HR><P><A HREF=\"$scriptname?logout=true\">Log out</A></P>";
       echo "<HR>";
       echo "</CENTER>";
   }

   PrintFooter();
   die;
}

if ($recent) {
   if ($guestcomment) {
      PrintHeader("$title: Recent Comments");
      PrintRecentComments($recent);
   }
   echo "<P ALIGN=CENTER><A HREF=\"$scriptname?intro=true\">".$ss[$lang]["start"]."</A></P>";
   die;
}

if ($margin) {                      // Create a list of thumbnails
   if (!file_exists($thumb)) { 
       if ( ! mkdir($thumb, 0755)) {
           PrintError("Could not create thumb dir - check write permissions");
           die;
       }
   }

   PrintHeader("$title: Thumbnails", "margin");

   $imagelist = GetFileList();

   echo "<P ALIGN=CENTER>\n";
   foreach ($imagelist as $image) {

       $thumbimage = $thumb."/".$image;
       $thumb_exists = file_exists($thumbimage);

       if (!$thumb_exists) {   // Try to create the thumbnail
          set_time_limit(30);
          $thumbwidth = $margin - 20;

          $thumb_exists = ResizeImage($image, $thumbimage, $thumbwidth);
       }

       if (! $caption = GetImageCaption($image, $clean=true)) {
           $caption = $image;
       }
       $image      = rawurlencode($image);
       $thumbimage = $thumb."/".$image;

       if ($datenames) {  // Add date to comments
           $imageyear = substr($image, 0, 4);
           $imagemonth = substr($image, 4, 2);
           $imageday = substr($image, 6, 2);
           $caption = date("j-M-y ", mktime (0, 0, 0, $imagemonth, $imageday, $imageyear)) . $caption;
       }

       // The  d=d.html  was added to help dumb caches
       echo "<A TITLE=\"$caption\" HREF=\"$scriptname?image=$image&d=d.html\" TARGET=imain>"; 
       if ($thumb_exists) {
           echo "<IMG SRC=\"$thumbimage\" BORDER=0 ALT=\"$caption\">";
       } else {
           echo "<FONT SIZE=1>$caption</FONT>";
       }
       echo "</A><BR><BR>\n";
   } 
   echo "</P>\n";

   echo "<P ALIGN=CENTER><FONT SIZE=1><A TARGET=_parent HREF=\".\" >".$ss[$lang]["start"]."</A></FONT></P>\n";

   if ($password) {
       echo "<P ALIGN=CENTER><BR><FONT SIZE=1>";
       if (!isadmin()) {
          echo "<A TITLE=\"Log in to modify this site\" TARGET=imain HREF=\"$scriptname?admin=true\">Admin</A>";
       } else {
          echo "<A TITLE=\"Log out as administrator\" TARGET=imain HREF=\"$scriptname?logout=true\">Logout</A>";
       }
       echo "</FONT></P>\n"; 
   }
   echo "<P ALIGN=CENTER><BR><FONT SIZE=1>Made with<BR>";
   echo "<A TARGET=_top TITLE=\"Free software!\" HREF=\"http://photoframe.sourceforge.net/\">PhotoFrame $version</A></FONT></P>\n";

   die;
}

if ($comment) {
    AddComment($comment, $name, $message);
    if ($moderate) {
       echo "<P ALIGN=CENTER>Your comment was stored and is awaiting moderation</P>";
    }
    $image = $comment;
}

if ($image) {
   if (ereg( "\\.\\.", $image)) {  // using ".." in the filename
       PrintError("That filename ($image) was not secure.");
       die;
   }

   $cleanimage = $image;
   $image = urldecode($image);

   if (!file_exists($image)) { 
      PrintError("Strangely, that picture ($image) doesn't exist!");
      die;
   }

   $imagelist = GetFileList();
   GetNeighbours($imagelist, $image, $previmage, $nextimage);

   $caption = GetImageCaption($image);

   if ($slide && $nextimage) {
      $meta="<META HTTP-EQUIV=REFRESH CONTENT=\"$slide;URL=$scriptname?image=".urlencode($nextimage)."&slide=$slide\">";
   } else {
      $meta = "";
      $slide = "0";
   }

   PrintHeader(CleanCaption($caption), $meta);

   echo "<TABLE ALIGN=RIGHT CELLPADDING=0 CELLSPACING=0><TR>";
   if ($previmage) {
      echo "<TD><FORM ACTION=\"$scriptname\">
            <INPUT TYPE=hidden name=image value=\"$previmage\">
            <INPUT TYPE=submit VALUE=".$ss[$lang]["prev"]."></FORM></TD>";
   }
   if ($nextimage) {
      echo "<TD><FORM ACTION=\"$scriptname\">
            <INPUT TYPE=hidden name=image value=\"$nextimage\">
            <INPUT TYPE=submit VALUE=".$ss[$lang]["next"]."></FORM></TD>";

      echo "<TD>";
      PrintSlideshowForm($nextimage, $slide);
      echo "</TD>";
   }
   echo "</TR></TABLE>\n";

   echo "<H3 align=left>".stripslashes($caption);
   if (isadmin() and ($captionmethod == "rdjpgcom" or $captionmethod == "capfile") ) {
      echo "<FONT SIZE=1>(<A HREF=\"$scriptname?editcaption=$cleanimage\">edit caption</A>)</FONT>";
   }
   echo "</H3>";

   if ($fullwindow) {
      $imagewidth = "WIDTH=100%";
   } else {
	  $imagewidth = "";
   }

   echo "<BR CLEAR=ALL><CENTER><P>\n";

   if ($imageresize && ! $originalimage) {
       $rimage = "$thumb/$imageresize$image";
       $cleanrimage = "$thumb/$imageresize".rawurlencode("$image");
       if (! file_exists("$rimage")) {
           $imageresize = ResizeImage($image, $rimage, $imageresize, $imageresize);
       }
       if ($imageresize) {
          $filesize = (integer) (filesize($image) / 1024);
          echo "<A HREF=\"$scriptname?image=$cleanimage&originalimage=true&d=d.html\" ";
          echo " TITLE=\"".$ss[$lang]["original"]." ($filesize kb)\">";
          echo "<IMG BORDER=0 $imagewidth SRC=\"$cleanrimage\">";
          echo "</A>\n";
       } else {
          echo "<IMG $imagewidth SRC=\"$cleanimage\">\n";
       }

   } else {
       echo "<IMG $imagewidth SRC=\"$cleanimage\">\n";
   }

   if ($datenames) {
       $imageyear = substr($image, 0, 4);
       $imagemonth = substr($image, 4, 2);
       $imageday = substr($image, 6, 2);
       echo "<BR><FONT SIZE=1>";
       echo date("j F, Y", mktime (0, 0, 0, $imagemonth, $imageday, $imageyear));
       echo "</FONT><BR>\n";
   }

   if (isadmin()) {
       echo "<BR><FONT SIZE=1><A TITLE=\"Are you sure? No second chances!\" HREF=\"$scriptname?deleteimage=$cleanimage\">Delete this image and all comments</A></FONT>";
   }

   echo "</P></CENTER>";

   if ($dcomment) {
       DeleteComment($image, $dcomment, $ctype);
   }

   if ($acomment) {
       ApproveComment($image, $acomment);
   }

   if ($guestcomment) {
       echo "<P ALIGN=CENTER><BR><BR><B>".$ss[$lang]["comments"]."</B></P>";
       PrintComments($image);
       PrintCommentForm($image);
   }

   PrintFooter();
   die;
}


?>
<HTML>
<HEAD>
  <TITLE><?PHP echo $title ?></TITLE>
  <META NAME="Description" CONTENT="Made with PhotoFrame <?PHP echo $version?>, http://photoframe.sourceforge.net">
  <LINK REL="SHORTCUT ICON" HREF="http://photoframe.sourceforge.net/pf.ico">
</HEAD>
<?PHP
if ($marginside == "right") {
?>
<FRAMESET COLS="*,<?PHP echo $marginsize?>" BORDER=1>
  <FRAME NAME=imain SRC="<?PHP echo $scriptname?>?intro=1" FRAMEBORDER=0 SCROLLING=AUTO>
  <FRAME NAME=imargin SRC="<?PHP echo $scriptname?>?margin=<?PHP echo $marginsize?>" FRAMEBORDER=0 
         marginwidth="0" marginheight="0" noresize SCROLLING=AUTO>
<?PHP
} else {
?>
<FRAMESET COLS="<?PHP echo $marginsize?>,*" BORDER=1>
  <FRAME NAME=imargin SRC="<?PHP echo $scriptname?>?margin=<?PHP echo $marginsize?>" FRAMEBORDER=0 
         marginwidth="0" marginheight="0" noresize SCROLLING=AUTO>
  <FRAME NAME=imain SRC="<?PHP echo $scriptname?>?intro=1" FRAMEBORDER=0 SCROLLING=AUTO>
<?PHP
}
?>
  <NOFRAMES>
     This site works best with frames, which your browser doesn't support.
     You can still browse this photo album, though, by starting 
     <A HREF="<?PHP echo $scriptname?>?margin=<?PHP echo $marginsize?>">here</A>
  </NOFRAMES>
</FRAMESET>
</HTML>

