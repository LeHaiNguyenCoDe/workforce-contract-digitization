/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#6366f1',
                    light: '#818cf8',
                    dark: '#4f46e5',
                },
                secondary: {
                    DEFAULT: '#ec4899',
                    light: '#f472b6',
                },
                success: '#10b981',
                warning: '#f59e0b',
                error: '#ef4444',
                info: '#0ea5e9',
                dark: {
                    900: '#0f0f23',
                    800: '#1a1a2e',
                    700: '#252542',
                    600: '#2f2f4f',
                }
            },
            fontFamily: {
                body: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'sans-serif'],
                display: ['Outfit', 'Inter', 'sans-serif'],
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
                'slide-up': 'slideUp 0.5s ease-out',
                'glow': 'glow 2s infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                glow: {
                    '0%, 100%': { boxShadow: '0 0 20px rgba(99, 102, 241, 0.3)' },
                    '50%': { boxShadow: '0 0 40px rgba(99, 102, 241, 0.6)' },
                },
            },
            backgroundImage: {
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                'gradient-primary': 'linear-gradient(135deg, #6366f1 0%, #ec4899 100%)',
                'gradient-glow': 'radial-gradient(ellipse at top, rgba(99, 102, 241, 0.15) 0%, transparent 50%)',
            },
        },
    },
    plugins: [],
}
