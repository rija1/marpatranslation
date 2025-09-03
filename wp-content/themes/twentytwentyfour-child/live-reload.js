const browserSync = require('browser-sync').create();

browserSync.init({
    proxy: 'http://marpatranslation.local/shop/',
    files: [
        '*.css',
        '**/*.css'
    ],
    injectChanges: true,
    open: 'external',
    notify: false,
    port: 3000
});

console.log('🚀 Live reload started!');
console.log('✨ Your site with live reload: http://localhost:3000');
console.log('📁 Original Local site: http://marpatranslation.local/shop/');
console.log('👀 Watching for CSS changes...');