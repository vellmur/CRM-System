CKEDITOR.plugins.add('macro',
    {
        init: function (editor)
        {
            var pluginName = 'macro';

            editor.ui.addButton(pluginName,
                {
                    label: 'Macros',
                    command: 'OpenWindow1',
                    icon: null
                });

            editor.addCommand('OpenWindow1', {
                exec: function (editor) {

                    var macrosModal = $('#macros_modal');
                    macrosModal.modal('toggle');

                    $("#pasteMacros").on("click", function() {
                        var macros = $('input[name="macros[]"]:checked');

                        macros.each(function() {
                            editor.insertHtml(' ' + $(this).val() + ' ');
                        });

                        macros.click();
                        macrosModal.modal('hide');

                        $(this).off('click')
                    });
                }
            });
        }
    });


