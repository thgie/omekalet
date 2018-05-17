javascript:(function(){
    omeka = {
        url: 'https://your.omeka-domain.com',
        api_key: 'SUPER_LONG_API_KEY',
        omekalet_url: 'https://your.omekalet-domain.com',
        screenshot_url: 'SCREENSHOT_SERVICE_URL'
    }
    script=document.createElement('script');
    script.src=omeka.omekalet_url+'/omekalet.js?';
    document.querySelector('body').appendChild(script);
})();