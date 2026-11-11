# S1 Sports kodändringar

- Behåll svenska på s1sports.se och engelska på s1sports.co via WPML.
- Ändra endast lokala filer. Publicera eller ladda aldrig upp till produktions-WordPress utan en separat uttrycklig instruktion.
- Utgå från WordPress-temat i `s1sports-theme` och pluginet i `s1sports-content`.
- Sidornas nuvarande design finns i `snapshots/` och `assets/css/`. Lägg begripliga, egna CSS-justeringar i `assets/mirror.css`; undvik att ändra de minifierade fångade CSS-filerna.
- Idrottare kommer från `atleter` och ACF. Ändra inte inläggstypens namn eller befintliga WPML-kopplingar.
- Formulärinskickningar lagras som `s1_inquiry` och skickas via `wp_mail` till max@arvinge.se.
- Kör `node scripts/verify.mjs`, `node --check s1sports-theme/assets/mirror.js` och PHP-syntaxkontroll efter ändringar.
- Jämför ändringar mot båda språkversionerna och kontrollera mobilbredd innan en release.
