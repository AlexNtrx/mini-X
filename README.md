# Mini-X – Pienimuotoinen sosiaalisen median sovellus (Opintoprojekti)

> **Huomautus:**  
> Tämä projekti on kehitetty **oppimistehtävänä osana tieto- ja viestintätekniikan perustutkintoa (ohjelmistokehittäjä)**. Projektin tavoitteena on osoittaa modernin web-ohjelmoinnin, asynkronisen arkkitehtuurin (AJAX), relaatiotietokantojen, sovellusturvallisuuden ja järjestelmällisen testauksen hallintaa ilman raskaita valmiskehyksiä.

Mini-X on selaimessa toimiva täysiverinen mikroblogipalvelu (inspiraationa X / Twitter, Pantip-keskusteluhuoneet ja Hi5-profiilikustomointi), joka on toteutettu puhtaalla PHP:llä, MySQL-tietokannalla ja modernilla asynkronisella JavaScriptillä (Fetch API / AJAX).

---

## Tekijän ja projektin tiedot
- **Tekijä:** Nithitorn Kaosa-ard
- **Tutkinto:** Tieto- ja viestintätekniikan perustutkinto (Ohjelmistokehittäjä)
- **Ajankohta:** Syyskuu – Lokakuu 2026
- **Versio:** 1.3.1 (Production Release / Final Version)

---

## Käytetyt teknologiat
- **Backend:** PHP 8.2+ (Oma MVC-henkinen modulaarinen arkkitehtuuri, REST/JSON API -päätepisteet ja Dual-mode käsittelijät)
- **Tietokanta:** MySQL / MariaDB (InnoDB, utf8mb4-merkistö, viite-eheydet ja kaskadoituvat poistot)
- **Frontend:** HTML5, CSS3 (Moderni tumma teema, CSS-muuttujat, Flexbox/Grid, täysi mobiiliresponsiivisuus)
- **JavaScript:** Moderni Vanilla JavaScript (ES6+, Fetch API, FormData, DOM Event Delegation, History API `pushState` / `popstate`)
- **Palvelinympäristö:** WampServer tai XAMPP (Apache + PHP + MySQL)
- **Kirjastot & Työkalut:** PHPMailer, JustValidate

---

## Tärkeimmät toiminnot ja arkkitehtuuri

### 1. Täysi AJAX-arkkitehtuuri (100% Asynkroninen käyttökokemus)
Sovelluksen käyttöliittymä on uudelleenrakannettu toimimaan ilman häiritseviä koko sivun uudelleenlatauksia:
- **Asynkroninen viestintä (Fetch API):** Kaikki lomakkeet ja käyttäjätoiminnot kommunikoivat taustajärjestelmän kanssa JSON-muotoisilla pyynnöillä ja vastauksilla.
- **Reaaliaikaiset käyttöliittymäpäivitykset (DOM & Toast):** Onnistuneet toiminnot ja virheilmoitukset viestitään käyttäjälle välittömästi kevyillä Toast-ilmoituksilla ja suorilla DOM-muutoksilla.
- **Keskitetty HTML-mallipohja (Single Source of Truth):** Uusien julkaisujen ja kommenttiketjujen HTML-rakenne luodaan palvelinpäässä (`renderPostCardHtml`, `renderCommentsListHtml`), mikä estää käyttöliittymän koodin hajaantumisen.
- **Globaali tapahtumankuuntelu (Event Delegation):** Tapahtumankuuntelijat on sidottu `document`-tasolle, joten dynaamisesti luodut julkaisut ja vastaukset toimivat välittömästi ilman skriptien uudelleensidontaa.
- **Dual-Mode Fallback:** Järjestelmä tunnistaa automaattisesti AJAX-pyynnöt (`isAjaxRequest()`), mutta tukee täysin myös perinteisiä HTTP POST- ja uudelleenohjauspyyntöjä varmuuskäytössä.
- **Automaattinen välimuistinohitus:** Kaikki skriptit ja tyylitiedostot latautuvat dynaamisella aikaleimalla (`filemtime`), jolloin päivitykset tulevat käyttäjille voimaan ilman selaimen välimuistin tyhjennystä.

### 2. Käyttäjätilit ja tietoturva
- **Turvallinen todennus:** Rekisteröityminen ja kirjautuminen AJAX-vahvistuksella sekä salasanojen vahva suojaus (`password_hash` / BCRYPT).
- **Injektioiden ja haavoittuvuuksien esto:** Kaikki tietokantakyselyt suoritetaan parametroiduilla lausekkeilla (`prepared statements`) ja tulosteet suojataan XSS-hyökkäyksiltä (`htmlspecialchars`).
- **Pehmeä poisto ja tilin palautus (Soft Delete & Reactivation):** Käyttäjä voi sulkea tilinsä väliaikaisesti. Kirjautuessa järjestelmä tunnistaa passiivisen tilin ja tarjoaa automaattisen palautusdialogin (30 päivän suoja-aika).
- **Salasanan palautus:** Sähköpostitse toimitettava turvallinen, aikasidottu palautustoken ja salasanan vaihtolomake.
- **Istunnon valvonta (AJAX 401 Interceptor):** Jos käyttäjän istunto vanhenee, taustakutsut palauttavat JSON 401 -koodin ja ohjaavat kirjautumissivulle hallitusti.
- **GDPR-yhteensopivuus:** Kattava tietosuojaseloste ja omien henkilötietojen sekä julkaisujen poistomahdollisuus.

### 3. Julkaisujen ja keskustelujen hallinta (CRUD)
- **Julkaisujen luonti:** Tekstin ja kuvien julkaiseminen `FormData`-tiedostolatauksella ilman sivulatausta. Uusi julkaisu ilmestyy syötteen kärkeen välittömästi.
- **Aihekohtaiset julkaisutyypit:** Tavalliset tilapäivitykset sekä kysymysjulkaisut (`post_type = 'question'`) aihealueiden keskustelun herättämiseksi.
- **Julkaisujen muokkaus ja poisto:** Sisällön muokkaaminen suoraan syötteessä (inline edit) ja poistaminen vahvistusdialogin kautta pehmeällä häivytyksellä.
- **Reaaliaikaiset tykkäykset:** Tykkää/peruuta-toiminto päivittää sydänkuvakkeen ja laskurin reaaliajassa.
- **Kommentointi ja ketjutetut vastaukset (Threaded Replies):** Julkaisuihin voi kommentoida ja kommentteihin vastata suoraan sisäkkäin.
- **Kuvien Lightbox:** Täysikokoinen kuvankatselu suoraan syötteestä.
- **Suhteelliset aikaleimat:** Automaattisesti päivittyvät X/Twitter-tyyliset aikaleimat (`nyt`, `15 s`, `5 min`, `2 t`, `3 pv`).

### 4. Edistynyt profiilin kustomointi (Hi5 / Retro -tyyli)
- **Interaktiivinen muokkaustyökalu (Profile Modal):** Profiilinäkymän muokkaus suoraan omalta sivulta AJAX-tallennuksella.
- **Avatarin pyöreä rajaustyökalu (Canvas Cropper):** Käyttäjä voi rajata ja zoomata profiilikuvansa haluamaansa kohtaan ennen tallennusta.
- **Profiilibanneri ja asemointi:** Bannerikuvan lataus ja pystysuuntaisen kohdistuksen säätö liukusäätimellä (reposition slider).
- **Live-taustakuvat ja teemavärit:** Valmiit animoidut live-taustat, omat taustakuvat sekä korostusvärin (Accent Color) valinta, joka päivittyy dynaamisesti CSS-muuttujina koko sivulle.
- **Retro 8-bit -musiikkisoitin:** Profiiliin voi lisätä oman tunnusmusiikin, joka soi autenttisella retro-soittimella profiilissa vierailtaessa.

### 5. Huoneet, haku ja selainhistoria (Navigation)
- **Pantip-tyyliset aihealuehuoneet:** Syötteen suodatus huoneittain (esim. Yleinen, Ohjelmointi, Pelit) yhdellä klikkauksella ilman sivulatausta.
- **Selaa-sivun reaaliaikainen haku:** Käyttäjähaku suodattaa käyttäjäkortit ja julkaisut lennosta.
- **Browser History Integration:** Suodatukset ja haut päivittävät selaimen osoiterivin (`history.pushState`), joten selaimen Edellinen/Seuraava-painikkeet (`popstate`) toimivat saumattomasti.

### 6. Ylläpito ja moderointi (Admin Dashboard & RBAC)
- **Minimalistinen ylläpitoportaali (Dedicated Standalone Admin Portal):** Selkeä, skandinaavisen minimalistinen ja häiriötön tumma käyttöliittymä (`admin.php`) ilman ylimääräisiä värikorostuksia tai emojeja, erillisellä tilanneilmaisimella ja selkeällä navigoinnilla.
- **Roolipohjainen pääsynhallinta (RBAC):** `admin`- ja `user`-roolit tiukalla palvelintason suojauksella.
- **Käyttäjien moderointi:** Käyttäjätunnusten ja nimimerkkien auditointi, profiilikuvien pakkonollaus, tilien jäädytys tai poisto.
- **Huoneiden dynaaminen hallinta:** Uusien aihealueiden luonti, piilottaminen ja poistaminen.
- **Ilmiantojärjestelmä (Report Modal):** Käyttäjät voivat ilmiantaa sopimattomia julkaisuja tai kommentteja, ja ylläpito käsittelee ilmoitukset paneelistaan.
- **Palautekanava (Palaute):** Käyttäjien buginraportit ja kehitysehdotukset ylläpidon vastausmahdollisuudella.
- **Kiinnitetyt ilmoitukset & Huoltotila (Maintenance Mode):** Sivuston asettaminen huoltotilaan ja ylläpitotiedotteiden kiinnitys syötteen kärkeen.

---

## Asennus- ja käynnistysohje

### 1. Esivaatimukset
- Paikallinen palvelin: **XAMPP** tai **WampServer** (Apache, MySQL/MariaDB, PHP 8.2+).

### 2. Projektikansion sijoitus
Kloonaa tai siirrä tämä projekti palvelimen juurikansioon:
- XAMPP: `C:\xampp\htdocs\mini-X`
- WampServer: `C:\wamp64\www\mini-X`

### 3. Tietokannan tuonti
1. Avaa selaimessa phpMyAdmin: `http://localhost/phpmyadmin/`
2. Valitse yläpalkista **Tuo** (Import).
3. Valitse projektin juuresta tiedosto: `minisome.sql` ja klikkaa **Suorita** (Go / Import).  
   *(Tiedosto luo automaattisesti `minisome`-tietokannan, taulut, suhteet sekä valmiin ylläpitäjän).*

### 4. Konfiguraatio (Asetustiedostot)
Projektissa on valmiit esimerkkikonfiguraatiot:
- Kopioi `config/database.example.php` nimelle `config/database.php` ja tarkista tietokantatunnukset.
- Kopioi `config/mail.example.php` nimelle `config/mail.php` sähköpostilähetyksiä varten (valinnainen).

### 5. Sovelluksen avaaminen
- **Paikallinen ympäristö:** Avaa selain ja siirry osoitteeseen:  
  **`http://localhost/mini-X/`**
- **Verkkoympäristö (Live Demo):**  
  **[https://mini-x.infinityfree.io/](https://mini-x.infinityfree.io/)**

---

## Testitunnukset

| Rooli | Käyttäjätunnus | Salasana | Oikeudet ja kuvaus |
|---|---|---|---|
| **Ylläpitäjä (Admin)** | `admin` | `admin123` | Täysi pääsy erilliseen ylläpitopaneeliin (`admin.php` tai `?page=admin`), moderointityökaluihin, huoneiden hallintaan ja huoltotilaan. |
| **Tavallinen käyttäjä** | *(Luo uusi tunnus)* | *(Oma salasana)* | Tavallisen käyttäjän perustoiminnot: syöte, julkaisut, kommentointi, profiilin kustomointi ja asetukset. |

---

## Testaus ja dokumentaatio

Sovellukselle on laadittu ammatillisten vaatimusten mukainen testausdokumentaatio, joka löytyy kansiosta `testaus/`:
- **Testausraportti:** [`testaus/MiniX_Testausraportti.docx`](testaus/MiniX_Testausraportti.docx)
- **Testitapaukset ja tulokset:** [`testaus/MiniX_Testitapaukset.xlsx`](testaus/MiniX_Testitapaukset.xlsx)
- **Käytettävyystestauksen materiaalit:** Kansiossa `testaus/` ja kuvakoosteet kansiossa `screenshots/`.
