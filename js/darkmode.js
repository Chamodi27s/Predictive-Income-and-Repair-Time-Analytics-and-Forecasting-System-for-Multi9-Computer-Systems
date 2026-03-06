/* ============================================================
   ANIMATED COUNTERS & THEME MANAGEMENT LOGIC
   ============================================================ */

/**
 * 1. Animated Counters Function
 * ඉලක්කම් බිංදුවේ සිට අදාළ අගය දක්වා ඇනිමේට් කරයි.
 */
function animateCounters() {
    const counters = document.querySelectorAll(".counter");

    counters.forEach(counter => {
        // පවතින අගය Number එකක් ලෙස ලබා ගැනීම
        const target = +counter.innerText;
        let count = 0;

        const update = () => {
            // වේගය පාලනය කිරීම සඳහා increment එකක් සකසා ගැනීම
            const increment = target / 40;

            if (count < target) {
                count += increment;
                counter.innerText = Math.floor(count);
                setTimeout(update, 30);
            } else {
                counter.innerText = target;
            }
        };

        update();
    });
}

/**
 * 2. Theme Management Function
 * LocalStorage පරීක්ෂා කර අදාළ Mode එක Apply කරයි.
 */
function applyCurrentTheme() {
    const body = document.body;
    const isDarkModeEnabled = localStorage.getItem("darkMode") === "enabled";

    if (isDarkModeEnabled) {
        body.classList.add("dark-mode");
    } else {
        body.classList.remove("dark-mode");
    }
}

/**
 * 3. Event Listeners
 */

// පිටුව Load වන විට ක්‍රියාත්මක වන දේවල්
window.addEventListener("load", () => {
    applyCurrentTheme(); // Default White mode ද නැද්ද යන්න තීරණය කරයි
    animateCounters();   // ඉලක්කම් ඇනිමේට් කරයි
});

// වෙනත් Tab එකක හෝ Navbar එකේ Switch එකකින් Theme එක වෙනස් කළහොත් 
// පිටුව Refresh නොකර වර්ණ වෙනස් වීමට මෙය උපකාරී වේ.
window.addEventListener('storage', (e) => {
    if (e.key === 'darkMode') {
        applyCurrentTheme();
    }
});

/**
 * 4. Force Sync (Optional)
 * Navbar එකේ Switch එක ක්ලික් කරන විටම ක්‍රියාත්මක වීමට 
 * පහත ක්‍රමය භාවිතා කළ හැක.
 */
setInterval(applyCurrentTheme, 500);