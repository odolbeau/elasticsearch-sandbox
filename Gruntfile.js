module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        jshint: {
            files: ['Gruntfile.js'],
        },

        bower: {
            dev: {
                dest: 'web/assets/vendor'
            }
        },

        concat: {
            js: {
                src: ['web/assets/vendor/*.js', 'web/assets/js/*.js'],
                dest: 'web/assets/build/<%= pkg.name %>.js'
            },
            css: {
                src: ['web/assets/vendor/*.css', 'web/assets/css/*.css'],
                dest: 'web/assets/build/<%= pkg.name %>.css'
            }
        },

        cssmin: {
            combine: {
                files: {
                    'web/assets/build/<%= pkg.name %>.min.css': ['<%= concat.css.dest %>']
                }
            }
        },

        uglify: {
            dist: {
                files: {
                    'web/assets/build/<%= pkg.name %>.min.js': ['<%= concat.js.dest %>']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-bower');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', ['jshint', 'bower', 'concat']);
    grunt.registerTask('deploy', ['default', 'uglify', 'cssmin']);
};
