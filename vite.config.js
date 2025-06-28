import {defineConfig} from 'vite';
import twirlBuildPlugin from "./resources/js/VitePlugins/twirl-build";
import laravel from "laravel-vite-plugin";
import checker from "vite-plugin-checker";

export default defineConfig({
    plugins: [
        twirlBuildPlugin(),
        laravel({
            input: [
                'resources/ts/twirl.ts',
            ],
            refresh: true,
        }),
        checker({ typescript: true })
    ],
    build: {
        emptyOutDir: false,
        manifest: true,
        outDir: 'public',
        rollupOptions: {
            output: {
                entryFileNames: `js/[name].js`,
                assetFileNames: file => {
                    let ext = file.name.split('.').pop()

                    if (ext === 'css') {
                        return 'css/[name].css'
                    }

                    if (ext === 'woff2') {
                        return 'fonts/[name].[ext]'
                    }

                    return 'assets/[name].[ext]'
                }
            }
        },
    },
});
