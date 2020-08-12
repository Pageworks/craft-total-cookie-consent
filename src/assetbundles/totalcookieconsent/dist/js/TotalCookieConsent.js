var closeButton = document.body.querySelector('.js-total-cookie-consent-close-button');
if (closeButton){
    closeButton.addEventListener('click', function(){
        var banner = document.body.querySelector('.js-total-cookie-consent-banner');
        if (banner){
            document.body.removeChild(banner);
        }
    });
}