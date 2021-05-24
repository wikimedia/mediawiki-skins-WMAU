/* eslint-env node */
module.exports = function ( grunt ) {
	grunt.loadNpmTasks( 'grunt-banana-checker' );
	grunt.loadNpmTasks( 'grunt-eslint' );
	grunt.loadNpmTasks( 'grunt-stylelint' );

	grunt.initConfig( {
		eslint: {
			options: {
				cache: true
			},
			all: [
				'*.{js,json}',
				'**/*.{js,json}',
				'!resources/libraries/**',
				'!node_modules/**',
				'!vendor/**'
			]
		},
		banana: {
			all: 'i18n/'
		},
		stylelint: {
			all: [
				'**/*.css',
				'**/*.less',
				'!resources/libraries/**',
				'!node_modules/**',
				'!vendor/**'
			]
		}
	} );

	grunt.registerTask( 'default', [ 'eslint', 'banana', 'stylelint' ] );
};
