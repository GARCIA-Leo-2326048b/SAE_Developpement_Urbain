let globalParentFolder = "root";
$(document).ready(function() {
//Cree le projet
    $('#create-project-form').on('submit', function (e) {
        e.preventDefault(); // Empêche le rechargement de la page

        let formData = new FormData(this); // Crée un objet FormData à partir du formulaire

        console.log("Début de l'envoi du fichier...");

        $.ajax({
            url: 'index.php?action=create_project', // URL de votre contrôleur
            type: 'POST', // Utilisez POST pour les fichiers
            data: formData,
            processData: false, // Nécessaire pour éviter que jQuery ne traite les données
            contentType: false, // Nécessaire pour permettre l'envoi multipart/form-data
            dataType: 'json',
            success: function (response) {
                console.log("Réponse du serveur :", response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message,
                    }).then(() => {
                        // Recharger ou mettre à jour l'affichage sans recharger la page
                        updateProjectload();  // Appeler ta fonction pour mettre à jour l'historique
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Erreur AJAX :", status, error);
                console.log("Réponse brute :", xhr.responseText); // Affichez la réponse brute
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors du téléchargement.',
                });
            },
        });
    });

    // Tab switching logic
    $('.tab-button').on('click', function () {
        const targetId = $(this).attr('id').replace('-tab', '-content');

        // Remove active state from buttons
        $('.tab-button').removeClass('active');
        $(this).addClass('active');

        // Switch content
        $('.tab-content').removeClass('active');
        $('#' + targetId).addClass('active');
    });


//Recharger les options
    $('.folder-selector').on('change', function() {
        let folderName = $(this).val(); // Récupérer la valeur du dossier sélectionné
        globalParentFolder = folderName;
        let $select = $(this); // Référence à la liste déroulante modifiée

        $.ajax({
            url: `index.php?action=get_subfolders&folderName=${folderName}`, // URL vers votre script côté serveur
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log("Options mises à jour pour :", folderName);
                $('#dossier_parent').val(folderName);

                $select.empty();// Effacer les anciennes options
                $select.append(`<option value="${folderName}" selected>${folderName}</option>`); // Ajouter le parent actuel


                // Ajouter les nouvelles options
                $.each(data, function(index, folder) {
                    $select.append('<option value="' + folder.folder_name + '">' + folder.folder_name + '</option>');
                });
            },
            error: function() {
                alert('Échec de la récupération des options.');
            }
        });
    });


    $('#createFolderButton').on('click', function () {
        const folderName = $('#dossier_name').val().trim(); // Enlève les espaces
        const parentFolder = globalParentFolder;

        // Vérifie si folderName est vide après avoir supprimé les espaces
        if (folderName === "") {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Le nom du dossier est requis.',
            });
            return; // Sortir si le nom du dossier est vide
        }

        console.log("Nom du dossier:", folderName); // Affiche la valeur nettoyée du dossier
        console.log("Dossier parent:", parentFolder); // Affiche la valeur du parent du dossier

        // Envoyer les données au contrôleur via AJAX (en POST)
        $.ajax({
            url: 'index.php?action=create_folder',
            type: 'POST',
            contentType: 'application/json', // Spécifie le format des données
            data: JSON.stringify({
                dossier_name: folderName,
                dossier_parent: parentFolder
            }),
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: data.message,
                    }).then(() => {
                        // Recharger ou mettre à jour l'affichage sans recharger la page
                        updateHistory(); // Appeler ta fonction pour mettre à jour l'historique
                        updateFolderOptions();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: data.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Statut :", status);
                console.error("Erreur :", error);
                console.error("Réponse du serveur :", xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur inattendue est survenue.',
                });
            }
        });
    });


    document.getElementById('createFolderButton').addEventListener('click', function() {
        document.getElementById('createFolderForm').reset();
        document.getElementById('createFolderForm').style.display = 'none';
    });


    $('#vectorForm').on('submit', function (e) {
        e.preventDefault(); // Empêche le rechargement de la page

        let formData = new FormData(this); // Crée un objet FormData à partir du formulaire
        formData.append('shapefile_name', $('#shapefile_name').val()); // Ajouter des données supplémentaires si nécessaire

        console.log("Début de l'envoi du fichier...");

        $.ajax({
            url: 'index.php?action=upload', // URL de votre contrôleur
            type: 'POST', // Utilisez POST pour les fichiers
            data: formData,
            processData: false, // Nécessaire pour éviter que jQuery ne traite les données
            contentType: false, // Nécessaire pour permettre l'envoi multipart/form-data
            dataType: 'json',
            success: function (response) {
                console.log("Réponse du serveur :", response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message,
                    }).then(() => {
                        // Recharger ou mettre à jour l'affichage sans recharger la page
                        updateHistory();  // Appeler ta fonction pour mettre à jour l'historique
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: response.message,
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Erreur AJAX :", status, error);
                console.log("Réponse brute :", xhr.responseText); // Affichez la réponse brute
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Une erreur est survenue lors du téléchargement.',
                });
            },
        });
    });




});
let selectedFolderName = null;
let selectedFiles = [];
let simulationSelectedFiles = [];
let currentMode = 'simulation';
let actualpopup = null;

const toggleButton = document.getElementById('toggle-create-form');
const createForm = document.getElementById('create-project-form');
const cancelButton = document.getElementById('cancel-create-form');


toggleButton.addEventListener('click', () => {
    createForm.classList.toggle('hidden');
    toggleButton.querySelector('i').classList.toggle('fa-plus');
    toggleButton.querySelector('i').classList.toggle('fa-times');
});

cancelButton.addEventListener('click', () => {
    createForm.classList.add('hidden');
    toggleButton.querySelector('i').classList.add('fa-plus');
    toggleButton.querySelector('i').classList.remove('fa-times');
});

function updateProjectload(){

    fetch('index.php?action=get_all_projects')
        .then(response => response.text()) // Récupère le contenu HTML sous forme de texte
        .then(data => {
            console.log(data); // Vérifie la réponse HTML dans la console

            const $select = $('#project');


            $select.empty(); // Vide le contenu actuel du select
            $select.append(data); // Insère directement les options reçues du serveur

            console.log($select.html());
        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour de l'historique :", error);
        });
}
function switchMode(mode) {
    // Met à jour le mode courant
    currentMode = mode;

    // Affiche ou masque la section de comparaison
    document.getElementById('compare-section').style.display = (mode === 'comparaison') ? 'block' : 'none';

    // Change le texte du bouton d'action
    document.getElementById('actionButton').textContent = (mode === 'simulation') ? 'Simuler' : 'Sélectionner';

    // Réinitialise les fichiers sélectionnés
    selectedFiles = [];
    updateCompareButtonState();

    // Gère la classe active pour les boutons
    const buttons = document.querySelectorAll('#mode-switch button');
    buttons.forEach(button => {
        if (button.textContent.includes(mode.charAt(0).toUpperCase() + mode.slice(1))) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}


function showForm(type) {
    if (type === 'vector') {
        document.getElementById('vectorForm').style.display = 'block';
        document.getElementById('rasterForm').style.display = 'none';
    } else if (type === 'raster') {
        document.getElementById('vectorForm').style.display = 'none';
        document.getElementById('rasterForm').style.display = 'block';
    }
}


function closeCreateFolderForm() {
    document.getElementById('createFolderForm').style.display = 'none';
}


function createNewFolder() {

    document.getElementById('createFolderForm').style.display = 'block';

}

function toggleFolder(folderId) {
    const filesElement = document.getElementById(`${folderId}-files`);
    const childrenElement = document.getElementById(`${folderId}-children`);

    // Basculer l'affichage des fichiers
    if (filesElement) {
        filesElement.style.display = filesElement.style.display === 'none' ? 'block' : 'none';
    }

    // Basculer l'affichage des enfants
    if (childrenElement) {
        childrenElement.style.display = childrenElement.style.display === 'none' ? 'block' : 'none';
    }

    // Basculer l'icône de dossier
    const button = document.querySelector(`[data-folder-id="${folderId}"]`);
    if (button) {
        const icon = button.querySelector('.icon-folder');
        if (icon) {
            icon.textContent = icon.textContent === '📁' ? '📂' : '📁';
        }
    }
}


function updateHistory() {
    fetch('index.php?action=reloading')
        .then(response => response.text()) // Change to .text() to handle HTML response
        .then(data => {
            console.log(data);
            const historyFiles = document.getElementById('history-files');
            historyFiles.innerHTML = data; // Update the history with the new HTML
        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour de l'historique :", error);
        });
}
function updateHistoryExp() {
    fetch('index.php?action=reloadingExp')
        .then(response => response.text()) // Change to .text() to handle HTML response
        .then(data => {
            console.log(data);
            const historyFiles = document.getElementById('exphistory');
            historyFiles.innerHTML = data; // Update the history with the new HTML
        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour de l'historique :", error);
        });
}

function updateFolderOptions() {
    globalParentFolder = 'root';
    fetch('index.php?action=get_all_folders')
        .then(response => response.text()) // Récupère le contenu HTML sous forme de texte
        .then(data => {
            console.log(data); // Vérifie la réponse HTML dans la console

            const $select = $('#dossier_parent1');
            const $select2 = $('#dossier_parent');
            $select2.empty(); // Vide le contenu actuel du select
            $select2.append('<option value="root">Choisir..</option>'); // Ajoute l'option "Racine"
            $select2.append(data); // Insère directement les options reçues du serveur
            $select.empty(); // Vide le contenu actuel du select
            $select.append('<option value="root">Choisir..</option>'); // Ajoute l'option "Racine"
            $select.append(data); // Insère directement les options reçues du serveur

            console.log($select.html());
        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour de l'historique :", error);
        });
}

function showPopup(fileName) {
    const popup1 = document.getElementById('popup');
    const popupFileName1 = document.getElementById('popup-file-name');
    const popup2 = document.getElementById('popup2');
    const popupFileName2 = document.getElementById('popup-file-nameS');

    if (popup1 && popupFileName1) { // Vérifie si le premier popup et son élément de texte existent
        popupFileName1.textContent = fileName;
        popup1.style.display = 'block';
    } else if (popup2 && popupFileName2) { // Sinon, vérifie si le second popup et son élément de texte existent
        popupFileName2.textContent = fileName;
        popup2.style.display = 'block';
    } else {
        console.error('Aucun popup disponible pour afficher : ' + fileName);
    }
}

function showExperimentPopup(fileName){
    document.getElementById('popup-file-nameExp').textContent = fileName;
    document.getElementById('popupExp').style.display = 'block';
}

function closePopup(element) {
    // Remonter jusqu'au parent avec la classe 'popup'
    const popup = element.closest('.popup');
    actualpopup = element;
    if (popup) {
        popup.style.display = 'none';
    } else {
        console.error('Aucun élément parent avec la classe "popup" trouvé.');
    }
}


function performAction() {
    const fileId = document.getElementById('popup-file-name').textContent;

        window.location.href = 'index.php?action=affichage&house=' + encodeURIComponent(fileId);

}

function selectFile() {
    const fileName = document.getElementById('popup-file-name').textContent;
    if (selectedFiles.length < 2 && !selectedFiles.includes(fileName)) {
        selectedFiles.push(fileName);
        alert(fileName + " a été sélectionné.");
    } else if (selectedFiles.includes(fileName)) {
        alert("Ce fichier est déjà sélectionné.");
    } else {
        alert("Vous ne pouvez sélectionner que deux fichiers au maximum.");
    }
    updateCompareButtonState();
}

function deleteFile() {
    const fileName = document.getElementById('popup-file-name').textContent;
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`index.php?action=deletFile&fileName=${fileName}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({fileName: fileName})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            icon: "success"
                        });
                        updateHistory();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Erreur lors de la suppression de " + fileName,
                            footer: '<a href="#">Why do I have this issue?</a>'
                        });

                    }
                });
        }
    });
}
function deleteFileExp() {
    const fileName = document.getElementById('popup-file-nameExp').textContent;
    console.log(fileName);
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`index.php?action=deletFileExp&fileName=${fileName}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({fileName: fileName})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Deleted!",
                            text: "Your file has been deleted.",
                            icon: "success"
                        });
                        updateHistoryExp();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Erreur lors de la suppression de " + fileName,
                            footer: '<a href="#">Why do I have this issue?</a>'
                        });

                    }
                });
        }
    });
    closePopup(actualpopup);
}



function showContextMenu(event, folderName) {

    event.preventDefault(); // Empêche le menu contextuel natif
    selectedFolderName = folderName; // Initialisation correcte de selectedFolderName
    console.log(selectedFolderName);
    // Vérifier si selectedFolderName est défini avant de l'utiliser
    if (!selectedFolderName) {
        console.error('Aucun dossier sélectionné.');
        return; // Si la variable est indéfinie, on arrête la fonction
    }

    // Cache tout autre menu contextuel
    hideContextMenu();

    const contextMenu = document.getElementById('context-menu');
    if (!contextMenu) {
        console.error('Menu contextuel introuvable!');
        return;
    }

    // Positionne et affiche le menu contextuel
    contextMenu.style.display = 'block';
    contextMenu.style.left = `${event.pageX}px`;
    contextMenu.style.top = `${event.pageY}px`;

    // Ajoute un écouteur pour masquer le menu quand on clique ailleurs
    document.addEventListener('click', hideContextMenu, { once: true });
}


function hideContextMenu() {
    const contextMenu = document.getElementById('context-menu');
    if (contextMenu) {
        contextMenu.style.display = 'none';
    }
}


function updateCompareButtonState() {
    const compareButton = document.getElementById('compareButton');
    if (selectedFiles.length === 2) {
        compareButton.disabled = false;
        compareButton.classList.add('enabled'); // Ajouter la classe 'enabled'
    } else {
        compareButton.disabled = true;
        compareButton.classList.remove('enabled'); // Retirer la classe 'enabled'
    }
}


function compare() {
    if (selectedFiles.length === 2) {
        alert("Comparaison entre " + selectedFiles[0] + " et " + selectedFiles[1] + " lancée !");
        // Ajouter la logique de comparaison ici
    } else {
        alert("Veuillez sélectionner exactement deux fichiers.");
    }
}

function deleteFolder() {
    Swal.fire({
        title: "Êtes-vous sûr ?",
        text: `Voulez-vous supprimer le dossier "${selectedFolderName}" ?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, supprimer !"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`index.php?action=deleteFolder&folderName=${selectedFolderName}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ folderName: selectedFolderName })
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        Swal.fire({
                            title: "Supprimé !",
                            text: `Le dossier "${selectedFolderName}" a été supprimé.`,
                            icon: "success"
                        });
                        updateHistory();
                        updateFolderOptions();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Erreur...",
                            text: `Impossible de supprimer le dossier "${selectedFolderName}".`,
                        });
                    }
                });
        }
    });
    hideContextMenu();
}

function closeForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.style.display = 'none';
    }
}

function addToSelection() {
    const fileId = document.getElementById('popup-file-name').textContent;
    if (!simulationSelectedFiles.includes(fileId)) {
        simulationSelectedFiles.push(fileId);
        updateSelectedFilesUI();
        closePopup();
    }
}

function removeFromSelection() {
    const fileId = document.getElementById('popup-file-name').textContent;
    simulationSelectedFiles = simulationSelectedFiles.filter(file => file !== fileId);
    updateSelectedFilesUI();
}

function updateSelectedFilesUI() {
    const list = document.getElementById('selected-files-list');
    list.innerHTML = '';
    simulationSelectedFiles.forEach(file => {
        const listItem = document.createElement('li');
        listItem.textContent = file;
        list.appendChild(listItem);
    });

    // Enable or disable the simulate button
    const simulateButton = document.getElementById('simulate-button');
    simulateButton.disabled = simulationSelectedFiles.length === 0;
}

function simulateSelectedFiles() {
    // const fileId = document.getElementById('popup-file-name').textContent;
    house = simulationSelectedFiles[0];
    road = simulationSelectedFiles[1];
    // Call the backend or perform actions with the selected files
    window.location.href = 'index.php?action=affichage&house=' + encodeURIComponent(house) + '&road=' + encodeURIComponent(road);

}

//reload
function reloadExp(){


}