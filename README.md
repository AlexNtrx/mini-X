# Mini-X – Pienimuotoinen sosiaalisen median sovellus (Opintoprojekti)

>  **Huomautus:**  
> Tämä projekti on kehitetty **oppimistehtävänä osana tieto- ja viestintätekniikan perustutkintoa (ohjelmistokehittäjä)**. Projektin tavoitteena on osoittaa web-ohjelmoinnin, relaatiotietokantojen, sovellusturvallisuuden ja järjestelmällisen testauksen osaamista ilman raskaita valmiskehyksiä.

Mini-X on selaimessa toimiva mikroblogipalvelu (inspiraationa X / Twitter), joka on toteutettu PHP:llä ja MySQL-tietokannalla.

---

##  Tekijän ja projektin tiedot
- **Tekijä:** Nithitorn Kaosa-ard
- **Tutkinto:** Tieto- ja viestintätekniikan perustutkinto (Ohjelmistokehittäjä)
- **Ajankohta:** Syyskuu 2026
- **Versio:** 1.2.0
---

##  Käytetyt teknologiat
- **Backend:** PHP 8.2+
- **Tietokanta:** MySQL / MariaDB (minisome, InnoDB, utf8mb4)
- **Palvelinympäristö:** WampServer tai XAMPP
- **Frontend:** HTML5, CSS3 (tumma teema, responsiivinen mobiilinäkymä), JavaScript (Vanilla JS)
- **Kirjastot:** PHPMailer, JustValidate

---

##  Tärkeimmät toiminnot

1. **Käyttäjätilit ja tietoturva:**
   - Rekisteröityminen ja kirjautuminen.
   - Salasanojen turvallinen suojaus (`password_hash` / BCRYPT).
   - SQL-injektioiden esto parametroiduilla kyselyillä (`prepared statements`).
   - XSS-hyökkäysten esto tulosteissa (`htmlspecialchars`).
   - Profiilikuvan lataus, Lightbox-katselu, nimikirjain-avatar sekä näyttönimi (display name).
   - Tilin asettaminen tauolle (soft delete) ja mahdollisuus palauttaa tili kirjautumalla sisään 30 päivän kuluessa ennen tietojen pysyvää poistamista.
   - EU:n GDPR-asetuksen mukainen tietosuojaseloste ja omien tietojen hallinta.

2. **Julkaisujen hallinta (CRUD):**
   - Teksti- ja kuvajulkaisujen luominen (teksti, kuva tai molemmat), muokkaaminen ja poisto.
   - Kuvien automaattinen koon rajoitus syötteessä ja Lightbox-täysikokoinen katselu.
   - X/Twitter-tyyliset suhteelliset aikaleimat julkaisuissa ja ilmoituksissa (`nyt`, `15 s`, `5 min`, `2 t`, `3 pv`).
   - Julkaisujen haku käyttäjätunnuksen perusteella.

3. **Vuorovaikutus ja reaaliaikaisuus:**
   - Julkaisuista tykkääminen ja kommentointi.
   - Reaaliaikaiset ilmoitukset ilman sivun uudelleenlatausta (JavaScript Fetch API).

---

##  Asennus- ja käynnistysohje 

### 1. Esivaatimukset
- Paikallinen palvelin: **WampServer** tai **XAMPP** (Apache, MySQL, PHP 8.2+).

### 2. Projektikansion sijoitus
Kloonaa tai siirrä tämä projekti palvelimen juurikansioon:
- WampServer: `C:\wamp64\www\mini-X`
- XAMPP: `C:\xampp\htdocs\mini-X`

### 3. Tietokannan tuonti
1. Avaa selaimessa phpMyAdmin: `http://localhost/phpmyadmin/`
2. Valitse yläpalkista **Tuo** (Import).
3. Valitse projektin juuresta tiedosto: `minisome.sql` ja klikkaa **Suorita** (Go / Import).  
   *(Tiedosto luo automaattisesti `minisome`-tietokannan, taulut sekä valmiin `admin`-testikäyttäjän).*

### 4. Konfiguraatio (Asetustiedostot)
Kopioi esimerkkikonfiguraatiot ja aseta omat tietokanta- ja sähköpostitunnuksesi:
- Kopioi `config/database.example.php` nimelle `config/database.php`
- Kopioi `config/mail.example.php` nimelle `config/mail.php`

### 5. Sovelluksen avaaminen
Avaa selain ja mene osoitteeseen:  
 **`http://localhost/mini-X/`**

---

##  Testitunnukset

Voit rekisteröidä uuden käyttäjän tai kirjautua sisään valmiilla testitilillä:
- **Käyttäjätunnus:** `admin`
- **Salasana:** `admin123`

---

##  Testaus ja dokumentaatio

Sovellukselle on suoritettu kattava testaus ja dokumentointi, jonka materiaalit löytyvät kansiosta `testaus/`:
- **Testausraportti:** [`testaus/MiniX_Testausraportti.docx`](testaus/MiniX_Testausraportti.docx)
- **Testitapaukset ja -tulokset:** [`testaus/MiniX_Testitapaukset.xlsx`](testaus/MiniX_Testitapaukset.xlsx)
- **Käytettävyystestauksen kuvat:** Kansiossa `testaus/` (`kaytettavyystestaus_1.jpg` – `kaytettavyystestaus_4.jpg`)

