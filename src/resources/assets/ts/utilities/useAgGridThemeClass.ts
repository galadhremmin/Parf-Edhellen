import { useEffect, useState } from 'react';

const THEME_ATTR = 'data-bs-theme';
const AG_THEME_LIGHT = 'ag-theme-balham';
const AG_THEME_DARK = 'ag-theme-balham-dark';

function getThemeFromDOM(): 'light' | 'dark' {
    const theme = document.documentElement.getAttribute(THEME_ATTR);
    if (theme === 'dark' || theme === 'light') {
        return theme;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

/**
 * Returns the AG Grid Balham theme class that matches the app theme (light/dark).
 * Subscribes to ed-theme-changed so all grids update when the user toggles theme.
 */
export function useAgGridThemeClass(): string {
    const [theme, setTheme] = useState<'light' | 'dark'>(getThemeFromDOM);

    useEffect(() => {
        const handleChange = (e: CustomEvent<{ theme: string }>) => {
            const next = e.detail?.theme === 'dark' ? 'dark' : 'light';
            setTheme(next);
        };
        document.documentElement.addEventListener('ed-theme-changed', handleChange as EventListener);
        return () => {
            document.documentElement.removeEventListener('ed-theme-changed', handleChange as EventListener);
        };
    }, []);

    return theme === 'dark' ? AG_THEME_DARK : AG_THEME_LIGHT;
}
