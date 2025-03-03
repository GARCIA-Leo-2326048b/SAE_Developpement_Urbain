let globalParentFolder = "root";
$(document).ready(function() {
//Cree le projet
    $('#create-project-form').on('submit', function (e) {
        e.preventDefault(); // Empêche le rechargement de la page

        let formData = new FormData(this); // Crée un objet FormData à partir du formulaire

        $.ajax({
            url: 'index.php?action=create_project', // URL de votre contrôleur
            type: 'POST', // Utilisez POST pour les fichiers
            data: formData,
            processData: false, // Nécessaire pour éviter que jQuery ne traite les données
            contentType: false, // Nécessaire pour permettre l'envoi multipart/form-data
            dataType: 'json',
            success: function (response) {
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


        $.ajax({
            url: 'index.php?action=upload', // URL de votre contrôleur
            type: 'POST', // Utilisez POST pour les fichiers
            data: formData,
            processData: false, // Nécessaire pour éviter que jQuery ne traite les données
            contentType: false, // Nécessaire pour permettre l'envoi multipart/form-data
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message,
                    }).then(() => {
                        // Recharger ou mettre à jour l'affichage sans recharger la page
                        updateHistory();  // Appeler ta fonction pour mettre à jour l'historique
                        updateFolderOptions();
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
let selectionFiles = [];
let simulationFiles = []; // Variable globale pour stocker les fichiers sélectionnés
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

/**
 * Fonction pour mettre à jour les options de chargement de projet.
 * Récupère la liste de tous les projets depuis le serveur et met à jour la liste déroulante des projets.
 */
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

/**
 * Fonction pour afficher le formulaire approprié en fonction du type.
 * @param {string} type - Le type de formulaire à afficher ('vector' ou 'raster').
 */
function showForm(type) {
    if (type === 'vector') {
        document.getElementById('vectorForm').style.display = 'block';
        document.getElementById('rasterForm').style.display = 'none';
    } else if (type === 'raster') {
        document.getElementById('vectorForm').style.display = 'none';
        document.getElementById('rasterForm').style.display = 'block';
    }
}

/**
 * Fonction pour fermer le formulaire de création de dossier.
 * Masque le formulaire de création de nouveau dossier.
 */
function closeCreateFolderForm() {
    document.getElementById('createFolderForm').style.display = 'none';
}

/**
 * Fonction pour afficher le formulaire de création de nouveau dossier.
 * Affiche le formulaire pour créer un nouveau dossier.
 */
function createNewFolder() {

    document.getElementById('createFolderForm').style.display = 'block';

}

/**
 * Fonction pour basculer l'affichage des fichiers et sous-dossiers dans un dossier.
 * @param {string} folderId - L'ID du dossier à basculer.
 */
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

/**
 * Fonction pour mettre à jour l'historique des fichiers.
 * Récupère les fichiers depuis le serveur et met à jour l'affichage de l'historique.
 */
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

/**
 * Fonction pour mettre à jour l'historique des expérimentations.
 * Récupère les expérimentations depuis le serveur et met à jour l'affichage de l'historique.
 */
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

/**
 * Fonction pour mettre à jour les options de dossier.
 * Récupère la liste de tous les dossiers depuis le serveur et met à jour les listes déroulantes des dossiers.
 */
function updateFolderOptions() {
    globalParentFolder = 'root';
    fetch('index.php?action=get_all_folders')
        .then(response => response.text()) // Récupère le contenu HTML sous forme de texte
        .then(data => {
            console.log(data); // Vérifie la réponse HTML dans la console

            const $select = $('#dossier_parent1');
            const $select2 = $('#dossier_parent');
            const $select3 = $('#folderSelect');
            $select2.empty(); // Vide le contenu actuel du select
            $select2.append('<option value="root">Choisir..</option>'); // Ajoute l'option "Racine"
            $select2.append(data); // Insère directement les options reçues du serveur
            $select.empty(); // Vide le contenu actuel du select
            $select.append('<option value="root">Choisir..</option>'); // Ajoute l'option "Racine"
            $select.append(data); // Insère directement les options reçues du serveur
            $select3.empty(); // Vide le contenu actuel du select
            $select3.append('<option value="root">Choisir..</option>'); // Ajoute l'option "Racine"
            $select3.append(data); // Insère directement les options reçues du serveur

        })
        .catch(error => {
            console.error("Erreur lors de la mise à jour de l'historique :", error);
        });
}

/**
 * Fonction pour afficher une fenêtre contextuelle avec le nom du fichier.
 * @param {string} fileName - Le nom du fichier à afficher dans la fenêtre contextuelle.
 */
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

/**
 * Fonction pour afficher une fenêtre contextuelle avec le nom de l'expérimentation.
 * @param {string} fileName - Le nom de l'expérimentation à afficher dans la fenêtre contextuelle.
 * @param {string} id - L'ID de l'expérimentation.
 */
function showExperimentPopup(fileName,id){
    document.getElementById('popup-file-nameExp').textContent = fileName;
    document.getElementById('experimentId').value = id;
    document.getElementById('popupExp').style.display = 'block';
}

/**
 * Fonction pour fermer une fenêtre contextuelle.
 * @param {HTMLElement} element - L'élément déclencheur à partir duquel remonter pour trouver la fenêtre contextuelle.
 */
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

/**
 * Fonction pour fermer la fenêtre contextuelle principale.
 */
function closePopupS(){
    document.getElementById('popup').style.display = 'none';
}

/**
 * Fonction pour récupérer le nom du fichier depuis la fenêtre contextuelle et rediriger l'utilisateur vers une nouvelle URL avec le nom du fichier en tant que paramètre de requête.
 */
function performAction() {
    const fileId = document.getElementById('popup-file-name').textContent;

        window.location.href = 'index.php?action=affichage&house=' + encodeURIComponent(fileId);

}

/**
 * Fonction pour supprimer un fichier.
 * Affiche une boîte de dialogue de confirmation avant de supprimer le fichier.
 */
function deleteFile() {
    const fileName = document.getElementById('popup-file-name').textContent;
    Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui !"
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
                            title:"Supprimé !",
                            text: "Votre fichier a été supprimé avec succès.",
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

/**
 * Fonction pour supprimer une expérimentation.
 * Affiche une boîte de dialogue de confirmation avant de supprimer l'expérimentation.
 */
function deleteFileExp() {
    const fileName = document.getElementById('popup-file-nameExp').textContent;
    console.log(fileName);
    Swal.fire({
        title: "Êtes-vous sûr ?",
        text: "Vous ne pourrez pas revenir en arrière !",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Oui, supprimez-le !"
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
                            title: "Supprimé !",
                            text: "Votre fichier a été supprimé avec succès.",
                            icon: "success"
                        });
                        updateHistoryExp();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Oups...",
                            text: "Erreur lors de la suppression de " + fileName,
                            footer: '<a href="#">Pourquoi ai-je ce problème ?</a>'
                        });
                    }
                });
        }
    });
    closePopup(actualpopup);
}



/**
 * Fonction pour afficher un menu contextuel avec le nom du dossier.
 * @param {Event} event - L'événement déclencheur.
 * @param {string} folderName - Le nom du dossier à afficher dans le menu contextuel.
 */
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

/**
 * Fonction pour masquer le menu contextuel.
 */
function hideContextMenu() {
    const contextMenu = document.getElementById('context-menu');
    if (contextMenu) {
        contextMenu.style.display = 'none';
    }
}

/**
 * Fonction pour supprimer un dossier.
 * Affiche une boîte de dialogue de confirmation avant de supprimer le dossier.
 */
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

/**
 * Fonction pour fermer un formulaire.
 * Masque le formulaire spécifié par son identifiant.
 * @param {string} formId - L'identifiant du formulaire à fermer.
 */
function closeForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.style.display = 'none';
    }
}

/**
 * Fonction pour ajouter un fichier à la sélection.
 * Ajoute le fichier sélectionné à la liste des fichiers sélectionnés et met à jour l'interface utilisateur.
 */
function addToSelection() {
    const fileId = document.getElementById('popup-file-name').textContent;

    // Si c'est le premier fichier, le traiter comme un building, sinon comme une couche
    if (!selectionFiles.some(file => file.name === fileId)) {
        selectionFiles.push({ name: fileId });

        // Mettre à jour l'UI en fonction du type de page
        if (window.location.search.includes('action=new_simulation')) {
            updateSimulationSelectedFilesUI();
        } else {
            updateComparisonSelectedFilesUI();
        }

        closePopup(document.getElementById('popup'));
    }
}

/**
 * Fonction pour retirer un fichier de la sélection.
 * Supprime le fichier sélectionné de la liste des fichiers sélectionnés et met à jour l'interface utilisateur.
 */
function removeFromSelection() {
    const fileId = document.getElementById('popup-file-name').textContent;

    // Filtrer pour retirer le fichier avec le nom spécifique
    selectionFiles = selectionFiles.filter(file => file.name !== fileId);

    // Mettre à jour l'UI selon la page actuelle
    if (window.location.search.includes('action=new_simulation')) {
        updateSimulationSelectedFilesUI();
    } else {
        updateComparisonSelectedFilesUI();
    }
}

/**
 * Fonction pour mettre à jour l'interface utilisateur des fichiers sélectionnés pour la simulation.
 * Réinitialise la liste des fichiers sélectionnés et met à jour l'affichage.
 */
function updateSimulationSelectedFilesUI() {
    const list = document.getElementById('selected-files-list');
    list.innerHTML = ''; // Réinitialiser la liste à chaque mise à jour

    selectionFiles.forEach(file => {
        const listItem = document.createElement('li');
        listItem.textContent = `${file.name} `; // Affichage du nom et du type
        list.appendChild(listItem);
    });

    // Trouver le bouton et vérifier si la sélection est vide ou non
    const simulateButton = document.getElementById('simulate-button');

    if (simulateButton) {
        // Activer ou désactiver le bouton en fonction de la longueur de la sélection
        simulateButton.disabled = selectionFiles.length === 0 ? true : false;
    }
}

/**
 * Fonction pour mettre à jour l'interface utilisateur des fichiers sélectionnés pour la comparaison.
 * Réinitialise la liste des fichiers sélectionnés et met à jour l'affichage.
 */
function updateComparisonSelectedFilesUI() {
    const list = document.getElementById('selected-files-list');
    list.innerHTML = ''; // Réinitialiser la liste à chaque mise à jour

    selectionFiles.forEach(file => {
        const listItem = document.createElement('li');
        listItem.textContent = `${file.name} `; // Affichage du nom et du type
        list.appendChild(listItem);
    });

    // Trouver le bouton et vérifier si la sélection est vide ou non
    const compareButton = document.getElementById('compare-button');

    if (compareButton) {
        // Activer ou désactiver le bouton en fonction de la longueur de la sélection
        compareButton.disabled = selectionFiles.length === 0 ? true : false;
    }
}


/**
 * Fonction pour simuler les fichiers sélectionnés.
 * Stocke les fichiers sélectionnés et affiche la fenêtre contextuelle des paramètres de simulation.
 */
function simulateSelectedFiles() {
    // Stocker les fichiers sélectionnés et afficher la popup
    simulationFiles = selectionFiles;
    showParamPopup();
}
/**
 * Affiche la pop-up pour les paramètre de la  simulation.
 *
 */
function showParamPopup() {
    document.getElementById('simulationParamPopup').style.display = 'block';
}

/**
 * Ferme la pop-up des paramètre de la  simulation.
 */
function closeParamPop() {
    document.getElementById('simulationParamPopup').style.display = 'none';
}

/**
 * Exécute la simulation avec les paramètres spécifiés.
 * Récupère les paramètres de simulation, affiche une fenêtre contextuelle de chargement,
 * envoie les données au serveur et redirige l'utilisateur vers la page de résultats.
 */
function executeSimulationP(){
    document.getElementById('simulationParamPopup').style.display = 'none';
    document.getElementById('popupNameSim').style.display = 'block';
}

/**
 * Exécute la simulation avec les paramètres spécifiés.
 * Récupère les paramètres de simulation, affiche une fenêtre contextuelle de chargement,
 * envoie les données au serveur et redirige l'utilisateur vers la page de résultats.
 */
function executeSimulationPY() {
    document.getElementById('popupNameSim').style.display = 'none';
    // Récupération des paramètres depuis les sliders et champs
    const params = {
        neighbours_l_min: document.getElementById('neighbours_l_min').value,
        neighbours_l_0: document.getElementById('neighbours_l_0').value,
        neighbours_l_max: document.getElementById('neighbours_l_max').value,
        neighbours_w: document.getElementById('neighbours_w').value,
        roads_l_min: document.getElementById('roads_l_min').value,
        roads_l_0: document.getElementById('roads_l_0').value,
        roads_l_max: document.getElementById('roads_l_max').value,
        roads_w: document.getElementById('roads_w').value,
        paths_l_min: document.getElementById('paths_l_min').value,
        paths_l_max: document.getElementById('paths_l_max').value,
        paths_w: document.getElementById('paths_w').value,
        slope_l_min: document.getElementById('slope_l_min').value,
        slope_l_max: document.getElementById('slope_l_max').value,
        slope_w: document.getElementById('slope_w').value
    };

    // Récupération des dates et du building_delta
    const starting_date = document.getElementById('starting_date').value || '1994';
    const validation_date = document.getElementById('validation_date').value || '2002';
    const building_delta = document.getElementById('building_delta').value || 22;

    const sim_name = document.getElementById('sim_name').value || `simulation_${Date.now()}`;
    const sim_folder = document.getElementById('sim_folder').value || 'root';


    // Afficher un indicateur de chargement
    Swal.fire({
        title: 'Simulation en cours',
        html: 'Veuillez patienter...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Création de l'objet JSON à envoyer
    const requestData = {
        params: params,
        files: simulationFiles.map(f => f.name), // Fichiers sélectionnés
        starting_date: starting_date,
        validation_date: validation_date,
        building_delta: building_delta,
        sim_name: sim_name,
        sim_folder: sim_folder
    };

    console.log("Données envoyées :", requestData); // Debugging

    // Envoyer la requête `POST` en JSON
    fetch('index.php?action=run_simulation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                window.location.href = `index.php?action=affichage&files=${encodeURIComponent(requestData.sim_name)}`;
            } else {
                Swal.fire('Erreur', data.message || 'Une erreur est survenue', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Erreur', 'Connexion au serveur échouée', 'error');
            console.error('Erreur:', error);
        });
}


/**
 * Fonction pour afficher un fichier sélectionné.
 * Récupère le nom du fichier depuis la fenêtre contextuelle et redirige l'utilisateur vers une nouvelle URL avec le nom du fichier en tant que paramètre de requête.
 */
function afficher(){
    // Récupérer l'élément et vérifier qu'il existe
    let file = document.getElementById('popup-file-nameS').textContent;
    // Rediriger vers l'URL
    window.location.href = 'index.php?action=affichage&files=' + file;
}

/**
 * Recharge l'expérimentation spécifiée.
 * Récupère l'ID de l'expérimentation depuis le DOM, vérifie sa validité,
 * et redirige l'utilisateur vers l'URL de rechargement avec l'ID encodé.
 */
function reloadExp() {
    // Récupérer l'élément et vérifier qu'il existe
    const experimentElement = document.getElementById('experimentId');
    if (!experimentElement) {
        console.error("L'élément 'experimentId' est introuvable dans le DOM.");
        return;
    }

    // Récupérer la valeur de l'ID
    const experimentId = experimentElement.value.trim();
    if (!experimentId) {
        console.error("L'ID de l'expérimentation est vide.");
        alert("Impossible de recharger : l'ID de l'expérimentation est manquant.");
        return;
    }

    // Rediriger vers l'URL avec l'ID encodé
    window.location.href = 'index.php?action=reloadExp&id=' + encodeURIComponent(experimentId);

    console.log("Reloading experiment with ID:", experimentId);
}

/**
 * Compare les fichiers sélectionnés.
 * Récupère la liste des fichiers sélectionnés, génère une chaîne de noms de fichiers,
 * et redirige l'utilisateur vers l'URL de comparaison avec les noms de fichiers en tant que paramètre de requête.
 */
function compareSelectedFiles(){
    // On prend la liste des fichiers sélectionnés
    let files = selectionFiles; // Ceci peut être un tableau de fichiers

    let fileNames = files.map(file => file.name).join(',');

    // Appel à l'URL avec la liste de fichiers (sans encodage)
    window.location.href = 'index.php?action=compare&files=' + fileNames;
}

AOS.init({
    duration: 1000, // Durée de l'animation
    once: true, // Animation unique par session
});

$(document).ready(function () {
    const $backToTop = $('#backToTop');

    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $backToTop.fadeIn();
        } else {
            $backToTop.fadeOut();
        }
    });

    $backToTop.click(function () {
        $('html, body').animate({ scrollTop: 0 }, 800);
    });

    const currentURL = window.location.href;

    // Déterminez si c'est l'accueil (ajustez le critère selon votre URL)
    if (currentURL.includes('index.php?action=accueil') || currentURL.endsWith('/') || currentURL.endsWith('index.php') || currentURL.includes('homepage') ) {
        $('#gobackButton').hide(); // Cache le bouton
    }
});

/**
 * Utilise l'historique du navigateur pour naviguer vers la page précédente.
 */
function goBack() {
    window.history.back();
}




