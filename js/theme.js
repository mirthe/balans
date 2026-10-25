const themeStorageKey = 'centen-theme';
const root = document.documentElement;

try {
    const savedTheme = localStorage.getItem(themeStorageKey);
    if (savedTheme === 'light' || savedTheme === 'dark') {
        root.dataset.theme = savedTheme;
    }
} catch (error) {
    if (!(error instanceof DOMException && error.name === 'SecurityError')) {
        throw error;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.querySelector('.theme-toggle');
    if (!(themeToggle instanceof HTMLButtonElement)) {
        return;
    }

    const isDarkTheme = () => root.dataset.theme
        ? root.dataset.theme === 'dark'
        : window.matchMedia('(prefers-color-scheme: dark)').matches;

    const updateToggle = () => {
        const dark = isDarkTheme();
        themeToggle.setAttribute('aria-pressed', String(dark));
        themeToggle.setAttribute('aria-label', dark ? 'Lichte modus inschakelen' : 'Donkere modus inschakelen');
    };

    updateToggle();

    themeToggle.addEventListener('click', () => {
        const nextTheme = isDarkTheme() ? 'light' : 'dark';
        root.dataset.theme = nextTheme;
        updateToggle();

        try {
            localStorage.setItem(themeStorageKey, nextTheme);
        } catch (error) {
            if (!(error instanceof DOMException && ['SecurityError', 'QuotaExceededError'].includes(error.name))) {
                throw error;
            }
            console.warn('The theme preference could not be saved.', error);
        }
    });
});
