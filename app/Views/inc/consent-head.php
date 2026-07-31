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
        label: 'Szukseges',
        description: '<p>Ezek a cookie-k a weboldal megfelelo mukodesehez elengedhetetlenek, ezert nem lehet oket kikapcsolni. Segitsegukkel lehet peldaul bejelentkezni es beallitani az adatvedelmi preferenciakat.</p>',
        required: true
      },
      {
        id: 'analytics',
        label: 'Analitika',
        description: '<p>Ezek a cookie-k segitenek nekunk a weboldal fejleszteseben azaltal, hogy nyomon kovetik, mely oldalak a legnepszerubbek, es hogyan mozognak a latogatok a weboldalon.</p>',
        defaultValue: true,
        gtag: 'analytics_storage'
      },
      {
        id: 'marketing',
        label: 'Marketing',
        description: '<p>Ezeket a cookie-kat mi es hirdetesi partnereink hasznaljuk arra, hogy relevans hirdeteseket jelenitsunk meg ezen a weboldalon es mashol, valamint hogy merjuk ezeknek a kampanyoknak a teljesitmenyet.</p>',
        gtag: [
          'ad_storage',
          'ad_user_data',
          'ad_personalization'
        ]
      }
    ],
    text: {
      prompt: {
        description: '<p>Weboldalunkon cookie-kat hasznalunk a felhasznaloi elmeny javitasa, szemelyre szabott tartalom nyujtasa, valamint a forgalom elemzese erdekeben.</p>',
        acceptAllButtonText: 'Elfogadas',
        acceptAllButtonAccessibleLabel: 'Elfogadas',
        rejectNonEssentialButtonText: 'Elutasit',
        rejectNonEssentialButtonAccessibleLabel: 'Elutasit',
        preferencesButtonText: 'Testreszabas',
        preferencesButtonAccessibleLabel: 'Testreszabas'
      },
      preferences: {
        title: 'Cookie beallitasok',
        description: '<p>Tiszteletben tartjuk az On adatvedelmi jogat. Donthet ugy, hogy bizonyos tipuszu cookie-k hasznalatat nem engedelyezi. A cookie-beallitasai weboldalunk egeszen ervenyesek lesznek.</p>',
        saveButtonText: 'Mentes',
        saveButtonAccessibleLabel: 'Mentes',
        creditLinkText: 'Silktide',
        creditLinkAccessibleLabel: 'Silktide'
      }
    }
  });
</script>
