# S1 Sports — lokalt WordPress-tema

## Status

Version 0.9.1 är en lokal referensbyggnad. 18 svenska och engelska sidor har egna HTML-ögonblicksbilder i `snapshots/`. Temat laddar kopior av sidornas genererade CSS och hämtar bilder från WordPress Media på s1sports.se, så den visuella grunden kan renderas utan Breakdance-pluginets mallmotor. WordPress och WPML fortsätter välja sida och språk.

Vid flytt till en annan WordPress-installation behöver Media-adresserna i sidkopiorna och `data/media-urls.json` uppdateras.

Språkväxlaren använder WPML:s `wpml_active_languages` för att länka till sidans översättning på `s1sports.se` eller `s1sports.co`. Menyns flaggor, pil och storlek följer originalets WPML-markering och CSS. Swiper-biblioteket används för karuseller.

Startsidan, Om oss och college-sidorna har sliderinställningar per sidtyp och skärmstorlek. Pilarna är kopplade till rätt slider även när originalsidans interna ID skiljer sig från behållarens. Sociala länkar i sidfoten är dolda enligt önskemål tills riktiga profiladresser finns.

## Mötesformulär

Insticksprogrammet `s1sports-content` sparar varje giltig inskickning som en privat `s1_inquiry`-post under **Mötesförfrågningar** i WordPress-admin. Namn, sport, e-post, telefon, språk, meddelande och mejlstatus lagras. Formuläret skickar till `max@arvinge.se` via WordPress `wp_mail`; mottagaren kan ändras med konstanten `S1_CONTACT_RECIPIENT`. Den lokala PHP-miljön saknar fungerande SMTP och testinskickningen sparades men kunde inte mejlas. Verifiera leverans via Post SMTP i staging före driftsättning.

## Idrottare och ACF

Installera och aktivera även insticksprogrammet `s1sports-content` och ACF. Den befintliga inläggstypen `atleter` används på produktionssajten; insticksprogrammet registrerar den bara i en ny lokal installation där den saknas. Varje profil använder WordPress-titel och utvald bild samt ACF-fälten `s1_sport`, `s1_college`, `s1_featured` och `s1_display_order`. Idrottarsidan hämtar publicerade poster och filtrerar dem lokalt efter idrott. Startsidan visar upp till åtta profiler markerade för startsidan. De 25 redan kända profilerna har reservdata och reservbilder i temat tills motsvarande ACF-fält och utvalda bilder har fyllts i i WordPress.

`s1sports-content/wpml-config.xml` anger att `atleter` ska översättas, att idrott och college/status ska översättas och att startsidesmarkering och ordning ska kopieras mellan språken. Koppla befintliga svenska och engelska profiler som översättningar i WPML. WPML har inte kunnat testas i den lokala installationen eftersom licensierad WPML-kod inte finns där.

## Kvar innan skarp installation

- Jämför samtliga 18 sidversioner vid dator- och mobilbredd och rätta varje skillnad.
- Testa navigation, karuseller, språkväxling och formulär i en WordPress stagingmiljö med WPML aktivt och Breakdance avstängt.
- Flytta övriga sidors text och bilder från statiska ögonblicksbilder till redigerbara WordPress-fält eller block utan att ändra utseendet. I nuläget är bara idrottarkorten dynamiska.
- Sociala länkar i sidfoten är avsiktligt dolda tills riktiga profiladresser finns.

Aktivera inte temat på produktionssajten förrän dessa punkter är klara.
