<?php if ($gtmId): ?>
<script>
  (function () {
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }

    function getConsent(key) {
      try {
        return localStorage.getItem(key) === 'true' ? 'granted' : 'denied';
      } catch (e) {
        return 'denied';
      }
    }

    gtag('consent', 'default', {
      analytics_storage: getConsent('stcm.consent.analytics'),
      ad_storage: getConsent('stcm.consent.marketing'),
      ad_user_data: getConsent('stcm.consent.marketing'),
      ad_personalization: getConsent('stcm.consent.marketing'),
      functionality_storage: getConsent('stcm.consent.essential'),
      security_storage: getConsent('stcm.consent.essential')
    });
  })();
</script>
<?php endif; ?>
