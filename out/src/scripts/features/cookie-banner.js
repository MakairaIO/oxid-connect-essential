document.addEventListener('DOMContentLoaded', () => {
    const consentBanner = document.querySelector('#cookieConsentBanner');
    if (consentBanner && !document.cookie.match(/cookie-consent=/)) {
        consentBanner.style.display = 'block';
        document.querySelector('#cookieConsentBanner').addEventListener('click', clickEvent => {
            if (clickEvent.target.dataset.consentDecision) {
                const halfAYear = 60 * 60 * 24 * 182;
                document.cookie = 'cookie-consent=' + clickEvent.target.dataset.consentDecision + ';max-age=' + halfAYear;
                consentBanner.style.display = 'none';
            }
        });

        document.querySelector('.makaira-cookie-banner__buttons-close').addEventListener('click', () => consentBanner.style.display = 'none');
    }
});
