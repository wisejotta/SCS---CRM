import { themeConfig } from '@themeConfig'

const theme = {
  defaultTheme: localStorage.getItem(`${themeConfig.app.title}-theme`) || themeConfig.app.theme.value,
  themes: {
    light: {
      dark: false,
      colors: {
        'primary': localStorage.getItem(`lightThemePrimaryColor`) || '#2563EB',
        'on-primary': '#fff',
        'secondary': '#0EA5A4',
        'on-secondary': '#fff',
        'success': '#16A34A',
        'on-success': '#fff',
        'info': '#0284C7',
        'on-info': '#fff',
        'warning': '#F59E0B',
        'on-warning': '#fff',
        'error': '#DC2626',
        'background': '#F4F8FF',
        'on-background': '#0F172A',
        'on-surface': '#0F172A',
        'grey-50': '#FAFAFA',
        'grey-100': '#F5F5F5',
        'grey-200': '#EEEEEE',
        'grey-300': '#E0E0E0',
        'grey-400': '#BDBDBD',
        'grey-500': '#9E9E9E',
        'grey-600': '#757575',
        'grey-700': '#616161',
        'grey-800': '#424242',
        'grey-900': '#212121',
        'perfect-scrollbar-thumb': '#DBDADE',
      },
      variables: {
        'border-color': '#4B465C',
        'medium-emphasis-opacity': 0.68,

        // Shadows
        'shadow-key-umbra-opacity': 'rgba(var(--v-theme-on-surface), 0.03)',
        'shadow-key-penumbra-opacity': 'rgba(var(--v-theme-on-surface), 0.02)',
        'shadow-key-ambient-opacity': 'rgba(var(--v-theme-on-surface), 0.01)',
      },
    },
    dark: {
      dark: true,
      colors: {
        'primary': localStorage.getItem(`${themeConfig.app.title}-darkThemePrimaryColor`) || '#60A5FA',
        'on-primary': '#fff',
        'secondary': '#2DD4BF',
        'on-secondary': '#fff',
        'success': '#22C55E',
        'on-success': '#fff',
        'info': '#38BDF8',
        'on-info': '#fff',
        'warning': '#FBBF24',
        'on-warning': '#fff',
        'error': '#F87171',
        'background': '#0B1220',
        'on-background': '#E2E8F0',
        'surface': '#111827',
        'on-surface': '#E2E8F0',
        'grey-50': '#26293A',
        'grey-100': '#2F3349',
        'grey-200': '#26293A',
        'grey-300': '#4A5072',
        'grey-400': '#5E6692',
        'grey-500': '#7983BB',
        'grey-600': '#AAB3DE',
        'grey-700': '#B6BEE3',
        'grey-800': '#CFD3EC',
        'grey-900': '#E7E9F6',
        'perfect-scrollbar-thumb': '#4A5072',
      },
      variables: {
        'border-color': '#CFD3EC',
        'medium-emphasis-opacity': 0.68,

        // Shadows
        'shadow-key-umbra-opacity': 'rgba(12, 16, 27, 0.15)',
        'shadow-key-penumbra-opacity': 'rgba(12, 16, 27, 0.01)',
        'shadow-key-ambient-opacity': 'rgba(12, 16, 27, 0.08)',
      },
    },
  },
}

export default theme
