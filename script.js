(function () {
    function setLanguage(lang) {
        localStorage.setItem('cashcue_lang', lang);
        document.documentElement.lang = lang === 'bm' ? 'ms' : 'en';

        document.querySelectorAll('[data-en][data-bm]').forEach(function (element) {
            element.textContent = element.getAttribute('data-' + lang);
        });

        document.querySelectorAll('.lang-toggle button').forEach(function (button) {
            button.classList.toggle('active', button.getAttribute('data-lang') === lang);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var saved = localStorage.getItem('cashcue_lang') || 'en';
        setLanguage(saved);

        document.querySelectorAll('.lang-toggle button').forEach(function (button) {
            button.addEventListener('click', function () {
                setLanguage(button.getAttribute('data-lang'));
            });
        });
    });
})();
