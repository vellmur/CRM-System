CKEDITOR.plugins.add('autodefault',
    {
        init: function (editor)
        {
            var pluginName = 'autodefault';

            editor.ui.addButton(pluginName,
                {
                    label: 'Default',
                    command: 'OpenWindow2',
                    icon: null
                });

            editor.addCommand('OpenWindow2', {
                exec: function (editor) {

                    var modal = $('#default-email-modal');
                    modal.modal('toggle');

                    $(".btn-continue").on("click", function() {
                        var defaultEmail = $('#' + editor.name + 'Default');

                        var defaultSubject = defaultEmail.find('#defaultSubject').text();
                        var defaultBody = defaultEmail.find('#defaultBody').html();

                        $('#' + editor.name + 'Subject').val(defaultSubject.trim());
                        editor.setData(defaultBody);

                        modal.modal('hide');

                        $(this).off('click')
                    });
                }
            });
        }
    });


