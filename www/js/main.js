requirejs.config({
    baseUrl: 'app',
    paths: {
        app: '.'
    }
});

requirejs(['app/init']); // initialize the app
