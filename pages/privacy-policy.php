<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$backUrl = $isLoggedIn ? 'index.php?page=home' : 'index.php';
$backText = $isLoggedIn ? ' Takaisin etusivulle' : ' Kirjaudu / Rekisteröidy';
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini X:n tietosuojaseloste</title>
    <link rel="stylesheet" href="./css/privacy.css">
</head>
<body class="x-privacy-body">

    <!-- X-tyylinen yläpalkki -->
    <header class="x-privacy-navbar">
        <div class="x-privacy-navbar-inner">
            <!-- Hamburger-painike mobiiliin (vasemmalla) -->
            <button type="button" class="x-privacy-hamburger" id="xPrivacyHamburger" aria-label="Avaa valikko" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Logo (mobiilissa keskellä) -->
            <a href="<?= $backUrl ?>" class="x-privacy-logo" aria-label="Mini X">
                <span>Mini X &bull; tietosuojaseloste</span>
            </a>

            <!-- Desktop toiminnot (oikealla) -->
            <div class="x-privacy-nav-actions">
                <a href="<?= $backUrl ?>" class="x-btn-nav x-btn-outline">
                    <?= htmlspecialchars($backText) ?>
                </a>
            </div>

            <!-- Tasapainottava tyhjä elementti mobiiliin -->
            <div class="x-nav-spacer" aria-hidden="true"></div>
        </div>
    </header>

    <!-- Mobiilin Hamburger Drawer & Overlay -->
    <div class="x-privacy-mobile-overlay" id="xPrivacyOverlay"></div>
    <aside class="x-privacy-mobile-drawer" id="xPrivacyDrawer">
        <div class="x-drawer-header">
            <div class="x-drawer-logo">
                <span>Mini X</span>
            </div>
            <button type="button" class="x-drawer-close" id="xPrivacyClose" aria-label="Sulje valikko">&times;</button>
        </div>

        <div class="x-drawer-body">
            <div class="x-drawer-actions">
                <a href="<?= $backUrl ?>" class="x-drawer-btn x-drawer-btn-outline">
                    <?= htmlspecialchars($backText) ?>
                </a>
            </div>

            <div class="x-drawer-divider"></div>

      
            <ul class="x-drawer-chapters">
                <li><a href="#chapter1" class="x-drawer-link">1. Mitä tietoja keräämme</a></li>
                <li><a href="#chapter2" class="x-drawer-link">2. Miten tietoja käytetään</a></li>
                <li><a href="#chapter3" class="x-drawer-link">3. Tietojen luovuttaminen</a></li>
                <li><a href="#chapter4" class="x-drawer-link">4. Miten kauan säilytämme tietoja</a></li>
                <li><a href="#chapter5" class="x-drawer-link">5. Evästeet ja istunnot</a></li>
                <li><a href="#chapter6" class="x-drawer-link">6. Käytä oikeuksiasi (GDPR)</a></li>
                <li><a href="#chapter7" class="x-drawer-link">7. Tietoturva</a></li>
                <li><a href="#chapter8" class="x-drawer-link">8. Rekisterinpitäjä ja yhteydenotto</a></li>
            </ul>
        </div>
    </aside>

    <!-- Pääasettelu: Sivupalkki  -->
    <div class="x-privacy-container">
        

        <aside class="x-privacy-sidebar">
            <ul class="x-chapter-list">
                <li class="x-chapter-item"><a href="#chapter1">1. Mitä tietoja keräämme</a></li>
                <li class="x-chapter-item"><a href="#chapter2">2. Miten tietoja käytetään</a></li>
                <li class="x-chapter-item"><a href="#chapter3">3. Tietojen luovuttaminen</a></li>
                <li class="x-chapter-item"><a href="#chapter4">4. Miten kauan säilytämme tietoja</a></li>
                <li class="x-chapter-item"><a href="#chapter5">5. Evästeet ja istunnot</a></li>
                <li class="x-chapter-item"><a href="#chapter6">6. Käytä oikeuksiasi</a></li>
                <li class="x-chapter-item"><a href="#chapter7">7. Tietoturva</a></li>
                <li class="x-chapter-item"><a href="#chapter8">8. Rekisterinpitäjä ja yhteydenotto</a></li>
            </ul>
        </aside>

        <!-- Oikea sisältöalue -->
        <main class="x-privacy-content" id="privacyDocumentText">

            <!-- Hero-osio -->
            <div class="x-hero">
                <span class="x-hero-date">Voimassa alkaen: 17. syyskuuta 2026 &bull; Versio 1.2</span>
                <h1 class="x-hero-title">Mini X:n tietosuojaseloste</h1>
                <p class="x-hero-intro">
                    Tämä tietosuojaseloste kertoo, mitä henkilötietoja Mini X -palvelu kerää, miten niitä käytetään ja miten voit hallita omia tietojasi. Lue huolellisesti, jotta ymmärrät oikeutesi ja velvollisuutesi palvelun käyttäjänä.
                </p>
            </div>

            <!-- "Paina mieleesi ainakin nämä" -X-yhteenvetokortit -->
            <div class="x-highlight-box">
                <h2 class="x-highlight-title">Pikaopas tietosuojaan</h2>
                <div class="x-highlight-grid">
                    <div class="x-summary-card">
                        <div>
                            <div class="x-summary-heading">Mini X on julkinen alusta</div>
                            <div class="x-summary-desc">Julkaisusi, kommenttisi ja profiilisi näkyvät muille käyttäjille.</div>
                        </div>
                        <a href="#chapter3" class="x-summary-link">Lue, mikä on julkista →</a>
                    </div>
                    <div class="x-summary-card">
                        <div>
                            <div class="x-summary-heading">Keräämme vain välttämättömät tiedot</div>
                            <div class="x-summary-desc">Vain ne tiedot, joita tarvitaan tilin luomiseen ja palvelun pyörittämiseen.</div>
                        </div>
                        <a href="#chapter1" class="x-summary-link">Lue, mitä keräämme →</a>
                    </div>
                    <div class="x-summary-card">
                        <div>
                            <div class="x-summary-heading">Ei mainosseurantaa</div>
                            <div class="x-summary-desc">Emme myy tietojasi emmekä käytä kolmansien osapuolten mainosevästeitä.</div>
                        </div>
                        <a href="#chapter5" class="x-summary-link">Lue evästeistä →</a>
                    </div>
                    <div class="x-summary-card">
                        <div>
                            <div class="x-summary-heading">Voit hallita kokemustasi</div>
                            <div class="x-summary-desc">Päivitä profiilisi tai poista tilisi omatoimisesti milloin vain.</div>
                        </div>
                        <a href="#chapter6" class="x-summary-link">Näin käytät oikeuksiasi →</a>
                    </div>
                    <div class="x-summary-card">
                        <div>
                            <div class="x-summary-heading">Jos sinulla on kysyttävää, kysy</div>
                            <div class="x-summary-desc">Vastaamme tietosuojaa ja henkilötietojasi koskeviin tiedusteluihin.</div>
                        </div>
                        <a href="#chapter8" class="x-summary-link">Näin tavoitat meidät →</a>
                    </div>
                </div>
            </div>

            <!-- Luku 1 -->
            <section id="chapter1" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">1. Mitä tietoja keräämme</h2>
                    <p class="x-chapter-lead">Mini X:ää käyttäessäsi keräämme tietoja, jotka ovat välttämättömiä tilisi toiminnan ja palvelun tarjoamisen kannalta.</p>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">1.1 Itse meille antamasi tiedot</h3>
                    <p class="x-p">Tilin luominen vaatii tiettyjen perustietojen antamista:</p>
                    <ul class="x-ul">
                        <li><strong>Käyttäjätunnus:</strong> Valitsemasi nimimerkki (3–20 merkkiä), joka yksilöi sinut palvelussa.</li>
                        <li><strong>Näyttönimi:</strong> Vapaaehtoinen profiilinimi (enintään 25 merkkiä), joka näkyy käyttäjätunnuksesi ohella julkaisuissa ja profiilissasi.</li>
                        <li><strong>Sähköpostiosoite:</strong> Käytetään käyttäjätilin varmistamiseen ja salasanan turvalliseen palauttamiseen.</li>
                        <li><strong>Salasana:</strong> Tallennetaan aina vahvasti salattuna. Emme koskaan tallenna tai näe salasanaasi selväkielisenä.</li>
                        <li><strong>Profiilikuva:</strong> Vapaaehtoinen kuva, jonka voit ladata tilisi personoimiseksi.</li>
                    </ul>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">1.2 Tiedot, joita keräämme palvelua käyttäessäsi</h3>
                    <p class="x-p">Kun käytät Mini X -palvelua, järjestelmään tallentuu toimintaasi liittyviä tietoja:</p>
                    <ul class="x-ul">
                        <li><strong>Julkaisut:</strong> Kirjoittamasi viestit (enintään 140 merkkiä), mahdolliset julkaisuihin liittämäsi kuvat ja niiden julkaisuajankohta.</li>
                        <li><strong>Kommentit:</strong> Toisten käyttäjien julkaisuihin kirjoittamasi vastaukset ja niiden aikaleimat.</li>
                        <li><strong>Tykkäykset:</strong> Tiedot siitä, mistä julkaisuista olet tykännyt.</li>
                        <li><strong>Ilmoitukset:</strong> Järjestelmän luomat ilmoitukset julkaisuihisi kohdistuneista tykkäyksistä ja kommenteista.</li>
                        <li><strong>Tilin luontiaika:</strong> Päivämäärä ja kellonaika, jolloin liityit Mini X -palveluun.</li>
                    </ul>
                </div>
            </section>

            <!-- Luku 2 -->
            <section id="chapter2" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">2. Miten tietoja käytetään</h2>
                    <p class="x-chapter-lead">Käytämme keräämiämme tietoja yksinomaan Mini X -palvelun toteuttamiseen, parantamiseen ja turvaamiseen.</p>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">2.1 Palvelun toimintojen toteuttamiseen</h3>
                    <p class="x-p">Tietojasi käytetään palvelun ydintoimintojen tarjoamiseen: tilisi todentamiseen sisäänkirjautumisessa, viestiesi julkaisemiseen syötteessä, muiden käyttäjien julkaisujen selaamiseen, kommentointiin ja tykkäysten rekisteröintiin.</p>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">2.2 Turvallisuuden vaalimiseen ja tilin palauttamiseen</h3>
                    <p class="x-p">Käytämme tietoja tilisi suojaamiseen luvattomalta käytöltä ja väärinkäytöksiltä. Jos unohdat salasanasi, käytämme sähköpostiosoitettasi kertakäyttöisen salasanan nollauslinkin toimittamiseen.</p>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">2.3 Käsittelyn oikeusperuste (GDPR Art. 6)</h3>
                    <div class="x-callout">
                        <div class="x-callout-title">Tietojenkäsittelyn lainmukainen peruste:</div>
                        <p class="x-p" style="margin: 0;">
                            &bull; <strong>Sopimus (Art. 6(1)(b)):</strong> Käsittely on välttämätöntä käyttäjäsopimuksen täyttämiseksi, jotta voimme tarjota sinulle pyytämäsi Mini X -palvelut.<br>
                            &bull; <strong>Suostumus (Art. 6(1)(a)):</strong> Vapaaehtoisten lisätietojen, kuten profiilikuvan, lataaminen perustuu suostumukseesi.<br>
                            &bull; <strong>Oikeutettu etu (Art. 6(1)(f)):</strong> Palvelun tietoturvan ja luotettavan teknisen toiminnan ylläpitäminen.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Luku 3 -->
            <section id="chapter3" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">3. Tietojen luovuttaminen </h2>
                    <p class="x-chapter-lead">Sinun kuuluu tietää tarkalleen, mitkä tiedoistasi näkyvät muille ja mitkä pidetään salassa.</p>
                </div>

                <p class="x-p">
                    Mini X on avoin mikroblogialusta, joten suuri osa luomastasi sisällöstä on tarkoitettu julkiseksi muille käyttäjille. Alla olevasta taulukosta näet selkeän jaon julkisten ja yksityisten tietojen välillä:
                </p>

                <div class="x-table-wrapper">
                    <table class="x-table">
                        <thead>
                            <tr>
                                <th>Tieto</th>
                                <th>Näkyvyys</th>
                                <th>Kuvaus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Käyttäjätunnus</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Näkyy julkaisuissa, kommenteissa, profiilisivullasi ja käyttäjähaussa.</td>
                            </tr>
                            <tr>
                                <td><strong>Näyttönimi</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Näkyy kaikille julkaisujesi ja kommenttiesi yhteydessä sekä profiilissasi käyttäjätunnuksen (@tunnus) rinnalla.</td>
                            </tr>
                            <tr>
                                <td><strong>Profiilikuva</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Näkyy kaikille julkaisujesi ja kommenttiesi vieressä sekä profiilissasi.</td>
                            </tr>
                            <tr>
                                <td><strong>Julkaisut ja kommentit</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Kaikki julkaisemasi viestit, kuvat ja kommentit näkyvät sovelluksen etusivulla ja profiilissasi.</td>
                            </tr>
                            <tr>
                                <td><strong>Tykkäykset</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Julkaisujen tykkäysmäärät näkyvät kaikille, ja viestin julkaisija näkee kuka siitä on tykännyt.</td>
                            </tr>
                            <tr>
                                <td><strong>Liittymispäivämäärä</strong></td>
                                <td><span class="x-badge x-badge-public">Julkinen</span></td>
                                <td>Näkyy profiilissasi tilisi luomisajankohtana.</td>
                            </tr>
                            <tr>
                                <td><strong>Sähköpostiosoite</strong></td>
                                <td><span class="x-badge x-badge-private">Yksityinen</span></td>
                                <td><strong>Ei koskaan näy muille käyttäjille.</strong> Käytetään vain kirjautumiseen ja salasanan nollaukseen.</td>
                            </tr>
                            <tr>
                                <td><strong>Salasana</strong></td>
                                <td><span class="x-badge x-badge-private">Yksityinen</span></td>
                                <td><strong>Ei näy kenellekään.</strong> Salasanat suojataan aina vahvalla salauksella, eikä niitä voi lukea selväkielisinä.</td>
                            </tr>
                            <tr>
                                <td><strong>Salasanan palautuskoodit</strong></td>
                                <td><span class="x-badge x-badge-private">Yksityinen</span></td>
                                <td>Väliaikaiset nollaustunnisteet toimitetaan ainoastaan omaan sähköpostiisi.</td>
                            </tr>
                            <tr>
                                <td><strong>Saapuneet ilmoitukset</strong></td>
                                <td><span class="x-badge x-badge-private">Yksityinen</span></td>
                                <td>Näkyvät ainoastaan omalla Ilmoitukset-sivullasi.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="x-subchapter">
                    <h3 class="x-subchapter-heading">3.1 Tietojen siirrot kolmansille osapuolille</h3>
                    <p class="x-p">
                        Emme myy, vuokraa tai luovuta henkilötietojasi kolmansille osapuolille kaupallisiin tai markkinointitarkoituksiin. Tietoja ei siirretä Euroopan unionin (EU) tai Euroopan talousalueen (ETA) ulkopuolelle.
                    </p>
                </div>
            </section>

            <!-- Luku 4 -->
            <section id="chapter4" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">4. Miten kauan säilytämme tietoja</h2>
                    <p class="x-chapter-lead">Säilytämme henkilötietojasi vain niin kauan kuin se on tarpeellista palvelun tarjoamiseksi.</p>
                </div>

                <ul class="x-ul">
                    <li><strong>Aktiivinen tili:</strong> Säilytämme tilitietosi, julkaisusi ja viestisi niin kauan kuin tilisi on käytössä.</li>
                    <li><strong>Tilin poistaminen:</strong> Kun poistat tilisi Asetukset-sivulta, profiilisi ja julkaisusi piilotetaan välittömästi muilta käyttäjiltä. Voit kuitenkin halutessasi palauttaa tilisi kirjautumalla uudelleen sisään.</li>
                    <li><strong>Tietojen pysyvä poisto:</strong> Mikäli pyydät rekisterinpitäjältä tietojesi lopullista hävittämistä tai poistat yksittäisen julkaisun, poistamme kyseiset tiedot ja niihin mahdollisesti liittyvät kuvatiedostot järjestelmästä pysyvästi.</li>
                    <li><strong>Säilytyspaikka:</strong> Kaikki tiedot, profiilikuvat ja julkaisujen liitekuvat säilytetään palveluntarjoajan suojatuilla palvelimilla.</li>
                </ul>
            </section>

            <!-- Luku 5 -->
            <section id="chapter5" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">5. Evästeet ja istunnot</h2>
                    <p class="x-chapter-lead">Käytämme evästeitä vain siihen, mikä on ehdottoman välttämätöntä.</p>
                </div>

                <p class="x-p">
                    Käytämme sivustolla vain toiminnan kannalta välttämätöntä evästettä:
                </p>
                <ul class="x-ul">
                    <li><strong>Kirjautumiseväste:</strong> Tämä eväste luodaan automaattisesti kirjautuessasi sisään. Sen ainoa tehtävä on pitää sinut kirjautuneena siirtyessäsi sivulta toiselle.</li>
                    <li><strong>Ei mainontaa tai seurantaa:</strong> Emme seuraa liikkumistasi verkossa emmekä käytä mainosverkkoja tai ulkopuolisia kävijäseurantoja.</li>
                    <li><strong>Evästeen poistuminen:</strong> Kirjautumiseväste poistuu laitteeltasi automaattisesti heti, kun kirjaudut ulos palvelusta tai suljet selaimesi.</li>
                </ul>
            </section>

            <!-- Luku 6 -->
            <section id="chapter6" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">6. Käytä oikeuksiasi </h2>
                    <p class="x-chapter-lead">Sinulla on täysi hallinta omiin tietoihisi EU:n yleisen tietosuoja-asetuksen nojalla.</p>
                </div>

                <ul class="x-ul">
                    <li><strong>Pääsy ja tarkastusoikeus (Art. 15):</strong> Sinulla on oikeus nähdä, mitä tietoja sinusta on tallennettu. Näet tietosi suoraan Profiili- ja Asetukset-sivuiltasi.</li>
                    <li><strong>Oikeus tietojen oikaisemiseen (Art. 16):</strong> Voit päivittää ja korjata näyttönimesi, käyttäjänimesi, sähköpostiosoitteesi, salasanasi ja profiilikuvasi milloin tahansa sovelluksen <em>Asetukset</em>- tai <em>Profiili</em>-sivulla.</li>
                    <li><strong>Oikeus tietojen poistamiseen ("oikeus tulla unohdetuksi", Art. 17):</strong> Voit poistaa tilisi itse Asetukset-sivun "Poista tili" -painikkeella tai pyytää rekisterinpitäjää poistamaan kaikki tietosi peruuttamattomasti.</li>
                    <li><strong>Oikeus käsittelyn rajoittamiseen ja vastustamiseen (Art. 18 & 21):</strong> Voit pyytää tietojesi käsittelyn rajoittamista tai vastustaa käsittelyä.</li>
                    <li><strong>Oikeus siirtää tiedot järjestelmästä toiseen (Art. 20):</strong> Sinulla on oikeus saada meille antamasi henkilötiedot jäsennellyssä muodossa.</li>
                    <li><strong>Oikeus tehdä valitus valvontaviranomaiselle (Art. 77):</strong> Mikäli koet henkilötietojesi käsittelyn lainvastaiseksi, sinulla on oikeus tehdä valitus Tietosuojavaltuutetun toimistolle (<a href="https://tietosuoja.fi" target="_blank" rel="noopener" style="color: var(--x-blue);">www.tietosuoja.fi</a>, PL 800, 00531 Helsinki, sähköposti: tietosuoja@om.fi).</li>
                </ul>
            </section>

            <!-- Luku 7 -->
            <section id="chapter7" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">7. Tietoturva</h2>
                    <p class="x-chapter-lead">Miten huolehdimme tietojesi turvallisuudesta.</p>
                </div>

                <p class="x-p">
                    Mini X:ssä noudatetaan nykyaikaisia ja huolellisia turvallisuuskäytäntöjä henkilötietojesi suojaamiseksi luvattomalta pääsyltä, muuttamiselta ja väärinkäytöltä:
                </p>
                <ul class="x-ul">
                    <li><strong>Salasanojen suojaus:</strong> Salasanat tallennetaan aina vahvasti salattuina, eikä kukaan voi nähdä niitä selväkielisenä.</li>
                    <li><strong>Tietokannan suojaus:</strong> Tietokantayhteydet ja kyselyt on suojattu luvatonta manipulointia ja tietomurtoja vastaan.</li>
                    <li><strong>Sisällön turvallisuus:</strong> Kaikki palveluun syötetyt tekstit ja viestit puhdistetaan ja suojataan haitallisen sisällön estämiseksi.</li>
                    <li><strong>Kuvatiedostojen turvallisuus:</strong> Palveluun ladattavien profiili- ja julkaisukuvien turvallisuus ja sallitut tiedostomuodot (JPEG, PNG, WebP, GIF) tarkistetaan ennen tallennusta haittaohjelmien ja turvallisuusriskien välttämiseksi.</li>
                </ul>
            </section>

            <!-- Luku 8 -->
            <section id="chapter8" class="x-chapter-section">
                <div class="x-chapter-header">
                    <h2 class="x-chapter-heading">8. Rekisterinpitäjä ja yhteydenotto</h2>
                    <p class="x-chapter-lead">Kuka vastaa sovelluksesta ja miten tavoitat meidät.</p>
                </div>

                <div class="x-callout" style="background-color: var(--x-bg-card); border-left-color: var(--x-blue);">
                    <div class="x-callout-title" style="font-size: 16px; margin-bottom: 8px;">Rekisterinpitäjän yhteystiedot:</div>
                    <p class="x-p" style="margin: 0; color: #fff;">
                        <strong>Palvelu:</strong> Mini X<br>
                        <strong>Rekisterinpitäjä:</strong> Nithitorn Kaosaard<br>
                        <strong>Sähköposti:</strong> <a href="mailto:alextunder2561@gmail.com" style="color: var(--x-blue);">alextunder2561@gmail.com</a><br>
                        
                    </p>
                </div>
                <p class="x-p" style="margin-top: 14px;">
                    Voit ottaa meihin yhteyttä sähköpostitse kaikissa tietosuojaan ja henkilötietojesi käsittelyyn liittyvissä asioissa. Vastaamme yhteydenottoihin ilman aiheetonta viivytystä ja viimeistään 30 päivän kuluessa.
                </p>
            </section>

        </main>
    </div>

    <!-- Alatunniste -->
    <footer class="x-privacy-footer">
        <div>
            &copy; <?= date('Y') ?> Mini X &bull; 
            <a href="index.php?page=privacy">Tietosuojaseloste</a> &bull; 
            <a href="<?= $backUrl ?>">Takaisin sovellukseen</a>
        </div>
    </footer>


    <script>
    // Hamburger-valikon ohjaus
    const hamburgerBtn = document.getElementById('xPrivacyHamburger');
    const drawer = document.getElementById('xPrivacyDrawer');
    const overlay = document.getElementById('xPrivacyOverlay');
    const closeBtn = document.getElementById('xPrivacyClose');

    function toggleXDrawer(open) {
        const isOpen = open !== undefined ? open : !drawer.classList.contains('open');
        if (drawer) drawer.classList.toggle('open', isOpen);
        if (overlay) overlay.classList.toggle('open', isOpen);
        if (hamburgerBtn) {
            hamburgerBtn.setAttribute('aria-expanded', isOpen);
        }
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    if (hamburgerBtn) hamburgerBtn.addEventListener('click', () => toggleXDrawer(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleXDrawer(false));
    if (overlay) overlay.addEventListener('click', () => toggleXDrawer(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) {
            toggleXDrawer(false);
        }
    });

    // Drawer linkit: sulje valikko ja vieritä
    document.querySelectorAll('.x-drawer-link').forEach(link => {
        link.addEventListener('click', e => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                toggleXDrawer(false);
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    setTimeout(() => {
                        targetEl.scrollIntoView({ behavior: 'smooth' });
                    }, 150);
                }
            }
        });
    });

    // Sujuva vieritys sisällysluettelosta
    document.querySelectorAll('.x-chapter-item a').forEach(link => {
        link.addEventListener('click', e => {
            const targetId = link.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    targetEl.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    </script>
</body>
</html>
