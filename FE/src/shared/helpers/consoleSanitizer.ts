/**
 * Console Sanitizer - Filters out noisy logs from external sources (e.g., Antivirus scripts, CSP violations)
 */
export const initConsoleSanitizer = () => {
    const originalError = console.error;

    const noiseKeywords = [
        'statInfo',
        'onHashChange',
        'Kaspersky',
        'Content Security Policy',
        'CSP',
        'from?get',
        'main.js?attr=',
        '401 (Unauthorized)',
        '/api/v1/me',
        'AxiosError'
    ];

    const isNoise = (args: any[]) => {
        const message = args.map(arg => String(arg)).join(' ');
        return noiseKeywords.some(keyword => message.includes(keyword));
    };

    console.error = (...args: any[]) => {
        if (!isNoise(args)) {
            originalError.apply(console, args);
        }
    };
};
