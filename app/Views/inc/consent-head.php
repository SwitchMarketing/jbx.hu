<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link
  rel="stylesheet"
  id="silktide-consent-manager-css"
  href="https://cdn.jsdelivr.net/gh/silktide/consent-manager@v2.0.1/silktide-consent-manager.css"
  integrity="sha384-EdMq+R+YOnsbelo08wPenoTlnxbAyxI11NMIxzugx/qAsbh64KcOkqxYqq6pfvO/"
  crossorigin="anonymous"
>
<style id="silktide-consent-manager-overrides">
  #stcm-wrapper {
    --boxShadow: -5px 5px 10px 0px #00000012, 0px 0px 50px 0px #0000001a;
    --fontFamily: Helvetica Neue, Segoe UI, Arial, sans-serif;
    --primaryColor: #f2d22e;
    --backgroundColor: #f5f3ef;
    --textColor: #111111;
    --backdropBackgroundColor: #00000033;
    --backdropBackgroundBlur: 1px;
    --iconColor: #f5f3ef;
    --iconBackgroundColor: #f2d22e;
  }
</style>
<script
  src="https://cdn.jsdelivr.net/gh/silktide/consent-manager@v2.0.1/silktide-consent-manager.js"
  integrity="sha384-5Pt34uiIbCsvfiiZXoLi4HRf/YBXjr9c8e+gYeVo9smUaInNHYVtc8NZ8wUnXJIq"
  crossorigin="anonymous"
></script>
<script>
  window.silktideConsentManager.init({
    backdrop: {
      show: true
    },
    icon: {
      position: 'bottomLeft'
    },
    prompt: {
      position: 'center'
    },
    consentTypes: [
      {
        id: 'essential',
        label: 'Szükséges',
        description: '<p>Ezek a cookie-k a weboldal megfelelő működéséhez elengedhetetlenek, ezért nem lehet őket kikapcsolni. Segítségükkel lehet például bejelentkezni és beállítani az adatvédelmi preferenciákat.</p>',
        required: true
      },
      {
        id: 'analytics',
        label: 'Analitika',
        description: '<p>Ezek a cookie-k segítenek nekünk a weboldal fejlesztésében azáltal, hogy nyomon követik, mely oldalak a legnépszerűbbek, és hogyan mozognak a látogatók a weboldalon.</p>',
        defaultValue: true,
        gtag: 'analytics_storage'
      },
      {
        id: 'marketing',
        label: 'Marketing',
        description: '<p>Ezeket a cookie-kat mi és hirdetési partnereink használjuk arra, hogy releváns hirdetéseket jelenítsünk meg ezen a weboldalon és máshol, valamint hogy mérjük ezeknek a kampányoknak a teljesítményét.</p>',
        gtag: [
          'ad_storage',
          'ad_user_data',
          'ad_personalization'
        ]
      }
    ],
    text: {
      prompt: {
        description: '<p>Weboldalunkon cookie-kat használunk a felhasználói élmény javítása, személyre szabott tartalom nyújtása, valamint a forgalom elemzése érdekében.</p>',
        acceptAllButtonText: 'Elfogadás',
        acceptAllButtonAccessibleLabel: 'Elfogadás',
        rejectNonEssentialButtonText: 'Elutasít',
        rejectNonEssentialButtonAccessibleLabel: 'Elutasít',
        preferencesButtonText: 'Testreszabás',
        preferencesButtonAccessibleLabel: 'Testreszabás'
      },
      preferences: {
        title: 'Cookie beállítások',
        description: '<p>Tiszteletben tartjuk az Ön adatvédelmi jogát. Dönthet úgy, hogy bizonyos típusú cookie-k használatát nem engedélyezi. A cookie-beállításai weboldalunk egészén érvényesek lesznek.</p>',
        saveButtonText: 'Mentés',
        saveButtonAccessibleLabel: 'Mentés',
        creditLinkText: 'Silktide',
        creditLinkAccessibleLabel: 'Silktide'
      }
    }
  });
</script>
