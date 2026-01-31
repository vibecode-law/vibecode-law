import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import { sentryVitePlugin } from '@sentry/vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

// Detect if running in GitHub Codespaces
const isCodespaces = !!process.env.CODESPACE_NAME;
const codespacesDomain = isCodespaces
    ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : null;
const codespacesLaravelDomain = isCodespaces
    ? `${process.env.CODESPACE_NAME}-8000.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : null;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        sentryVitePlugin({
            org: 'vibecodelaw',
            project: 'vibecode-law',
            sourcemaps: {
                filesToDeleteAfterUpload: [
                    "./public/build/assets/*.map",
                ],
            },
        }),
    ],

    server: isCodespaces ? {
        // GitHub Codespaces-specific configuration
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: {
            origin: `https://${codespacesLaravelDomain}`,
            credentials: true,
        },
        hmr: {
            protocol: 'wss',
            host: codespacesDomain!,
            clientPort: 443,
        },
        origin: `https://${codespacesDomain}`,
    } : {
        // Standard local development configuration
        host: 'localhost',
        port: 5173,
    },

    esbuild: {
        jsx: 'automatic',
    },

    build: {
        sourcemap: true,
    },

    ssr: {
        noExternal: [/\.css$/]
    }
});
