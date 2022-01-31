// Register the plugin within the editor.
CKEDITOR.plugins.add( 'step', {

    // Register the icons. They must match command names.
    icons: 'step',

    // The plugin initialization logic goes inside this method.
    init: function ( editor ) {

        // Define the editor command that inserts a step.
        editor.addCommand( 'insertstep', {

            // Define the function that will be fired when the command is executed.
            exec: function ( editor ) {
                var now = new Date();

                // Insert the step into the document.
                editor.insertHtml( "[step]Step Text[/step]" );
            }
        });

        // Create the toolbar button that executes the above command.
        editor.ui.addButton( 'step', {
            label: 'Insert step',
            command: 'insertstep',
            toolbar: 'insert'
        });
    }
});
