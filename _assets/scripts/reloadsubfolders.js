$(document).ready(function() {
    $('#dossier_parent').on('change', function() {
        let folderName = $(this).val(); // Assurez-vous que folderName est défini
        $.ajax({
            url: `index.php?action=get_subfolders&folderName=${folderName}`, // URL to your server-side script
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                let $select = $('#dossier_parent');
                $select.empty(); // Clear existing options
                $select.append(`<option value="${folderName}"> </option>`); // Add default option with current value

                // Populate new options
                $.each(data, function(index, folder) {
                    $select.append('<option value="' + folder.folder_name + '">' + folder.folder_name + '</option>');
                });
            },
            error: function() {
                alert('Failed to fetch options.');
            }
        });
    });




});