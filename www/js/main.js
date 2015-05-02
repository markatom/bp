Date.prototype.toString = function () {
    function pad(number) {
        return number < 10 ? '0' + number : number;
    }

    return this.getFullYear()
        + '-' + pad(this.getMonth() + 1)
        + '-' + pad(this.getDate());
};

requirejs.config({
    baseUrl: 'app',
    paths: {
        app: '.'
    }
});

requirejs(['app/init']); // initialize the app
