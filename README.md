# S1 Sports – WordPress-kodbas

Det här repot innehåller det egna WordPress-temat och ett separat innehållsplugin. Det finns ingen automatisk produktionsdriftsättning.

## Struktur

- `s1sports-theme/`: tema, lokala designresurser, språkväxlare och sidmallar.
- `s1sports-content/`: inläggstyp för idrottare i lokala installationer, ACF-fält och sparade mötesförfrågningar.
- `scripts/verify.mjs`: kontrollerar att sidkopior och resurser finns.
- `.github/workflows/check.yml`: syntax- och resurskontroll vid push och pull request.

## Krav

WordPress 6.4 eller senare och PHP 8.0 eller senare. Installera ACF, WPML och ACF Multilingual. Nuvarande produktionsinstallation använder dessutom CPT UI för `atleter` och Post SMTP för mejl. Temat fungerar utan Breakdance i den lokala installationen.

## Lokal installation

1. Installera och aktivera `s1sports-content` som plugin och `s1sports-theme` som tema.
2. Importera WordPress-sidorna och mediefilerna. Sidorna måste behålla sina befintliga svenska och engelska sluggar.
3. Ange svenska som huvudspråk och engelska som översättning i WPML. Behåll s1sports.se och s1sports.co som respektive språkdomän.
4. Koppla idrottarposter till rätt WPML-översättning. Kontrollera utvald bild och ACF-fälten för idrott, status, startsida och ordning.
5. Verifiera formulärets mejlleverans till max@arvinge.se i en stagingmiljö med Post SMTP.

Temat renderar 18 fångade sidversioner. Designen är lokalt speglad, men sidtexter och bilder ligger fortfarande huvudsakligen i `snapshots/`. Idrottarna är dynamiska WordPress-poster. Ändringar i sidornas design görs i temakoden; en kodagent bör ändra relevanta mallar och `assets/mirror.css` och kontrollera båda språkversionerna.

## Före produktion

Testa temat med WPML och ACF Multilingual på en stagingkopia av samma installation, med Breakdance avstängt. Verifiera båda språkdomänerna, alla sidpar, mobilmeny, slidrar, formulär, SMTP, SEO-metadata och omdirigeringar. Gör en visuell kontroll av samtliga sidor i mobil och desktop. Ta en återställningsbar backup före byte av aktivt tema.

Ingen GitHub Actions-workflow i detta repo publicerar kod.

