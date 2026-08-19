
document.addEventListener('DOMContentLoaded', () => {
  const goRight = document.getElementById('goRight');
  const goLeft = document.getElementById('goLeft');
  const slideBox = document.getElementById('slideBox');
  const topLayer = document.querySelector('.topLayer');

  // Funktio, joka vaihtaa näkymän rekisteröitymiseen 
  const switchToSignUp = () => {
    if (slideBox) slideBox.style.marginLeft = '0';
    if (topLayer) topLayer.style.marginLeft = '0';
  };
//  Funktio, joka vaihtaa näkymän kirjautumiseen
  const switchToLogin = () => {
    const isDesktop = window.innerWidth > 768;
    if (slideBox) slideBox.style.marginLeft = isDesktop ? '50%' : '0';
    if (topLayer) topLayer.style.marginLeft = '-100%';
  };

//  kirjaudu btn
  goRight?.addEventListener('click', () => {
    switchToSignUp();
  });

  // rekisteröidy btn
  goLeft?.addEventListener('click', () => {
    switchToLogin();
  });

//  Tarkistaa, mikä välilehti on aktiivinen ja vaihtaa näkymän sen mukaan
  if (document.body.dataset.activeTab === 'login') {
    switchToLogin();
  }

// animaatio,joka siirtää näkymän oikealle tai vasemmalle, kun käyttäjä vaihtaa välilehteä
  setTimeout(() => {
    [slideBox, topLayer].forEach(el => {
      if (el) el.style.transition = 'margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
    });
  }, 50);
});