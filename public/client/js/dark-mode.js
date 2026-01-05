// Dark Mode Toggle Functionality

// CRITICAL: Sahifa yuklashdan OLDIN dark mode tekshirish
(function() {
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode === 'enabled') {
        document.documentElement.classList.add('dark-mode');
        if (document.body) {
            document.body.classList.add('dark-mode');
        }
    }
})();

$(document).ready(function() {
    const darkModeToggle = $('#darkModeToggle');
    const body = $('body');
    const icon = darkModeToggle.find('i');
    const modeText = $('#modeText');
    
    // LocalStorage dan rejimni yuklash
    const darkMode = localStorage.getItem('darkMode');
    if (darkMode === 'enabled') {
        enableDarkMode();
    }
    
    // Toggle button click event
    darkModeToggle.on('click', function(e) {
        e.preventDefault();
        const isDarkMode = body.hasClass('dark-mode');
        
        if (isDarkMode) {
            disableDarkMode();
        } else {
            enableDarkMode();
        }
    });
    
    // Dark mode ni yoqish
    function enableDarkMode() {
        body.addClass('dark-mode');
        document.documentElement.classList.add('dark-mode');
        icon.removeClass('fa-moon').addClass('fa-sun');
        modeText.text('Kunduzgi rejim');
        localStorage.setItem('darkMode', 'enabled');
    }
    
    // Dark mode ni o'chirish
    function disableDarkMode() {
        body.removeClass('dark-mode');
        document.documentElement.classList.remove('dark-mode');
        icon.removeClass('fa-sun').addClass('fa-moon');
        modeText.text('Tungi rejim');
        localStorage.setItem('darkMode', 'disabled');
    }
});