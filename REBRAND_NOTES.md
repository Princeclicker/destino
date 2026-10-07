# Destino Access Hub Ltd - rebrand notes

Company: Destino Access Hub Ltd | Managing Director: RWIHIMBA Liliane | Tel: +250 786 362 004 (0786 362 004)
Email used: info@destinoaccesshub.com (was already in the header - change in squelettes/_entete.html, _pied.html, body-62.html if wrong)

## Done
- Language dropdown removed from the menu (squelettes/_entete.html).
- The three slogans appear ONLY on the home page slides (removed from footer, logo and page titles).
- Menu order: Home, About Us, Tour Packages, Destinations, Blog, Contact Us (header and footer).
- New logo (IMG/theme/images/logo-black.png, logo-white.png, favicon.png) built from your gorilla artwork, renamed to "Access Hub Ltd".
- All text rewritten for Rwanda: destinations, 9 tour packages, services, blog, About, Contact, footer.
- Managing Director portrait + name on the About page (IMG/theme/images/team-1.jpg).
- Removed fake testimonials, fake partner logos, invented team members and fake ratings/prices.

## You must do
1. Images: all photos now come from your images.zip (no illustrations left). Most of your files are 400-900 px wide, so they are sharp in cards and thumbnails; the home slides, page banner and background strips use your largest files (gorilla, Kigali sign, tea, dance, Nyungwe waterfall). For even sharper slides send 1920 px wide versions.
2. SPIP admin > Configuration > Identity of the site: set site name "Destino Access Hub Ltd", slogan and description (the templates are hard-coded, but the admin and e-mails use these).
3. Tour durations ("3 Days" etc.) and "Price: On request" are suggestions - edit in squelettes/body-60.html.
4. The contact form stores messages only; the server cannot send e-mail yet (see squelettes/formulaires/contact.php).
5. Delete tmp/cache and local/cache-* after uploading so SPIP rebuilds pages.
