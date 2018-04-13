'use strict';
module.exports = function(grunt) {
    grunt.initConfig({
        svgstore: {
            options: {
                prefix : 'icon-', // This will prefix each ID
                svg: { // will add and overide the the default xmlns="http://www.w3.org/2000/svg" attribute to the resulting SVG
                    viewBox : '0 0 100 100',
                    xmlns: 'http://www.w3.org/2000/svg'
                }
            },
            default : {
                files: {
                    'images/svg-defs.svg': ['images/svgs/*.svg']
                }
            }
        },
        watch: {
            svgs: {
                files: 'images/svgs/*.svg',
                tasks: ['svgstore']
            }
        }
    });

    require('load-grunt-tasks')(grunt);
    grunt.loadNpmTasks('grunt-svgstore');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['watch']);
};
