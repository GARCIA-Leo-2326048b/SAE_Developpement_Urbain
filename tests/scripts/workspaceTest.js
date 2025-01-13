// workspace.test.js

// Mocking jQuery
jest.mock('jquery', () => {
    const m$ = jest.fn(() => m$);
    m$.ajax = jest.fn();
    m$.fn = {
        on: jest.fn(),
        empty: jest.fn(),
        append: jest.fn(),
        val: jest.fn(),
        addClass: jest.fn(),
        removeClass: jest.fn(),
        toggleClass: jest.fn(),
        html: jest.fn(),
        fadeIn: jest.fn(),
        fadeOut: jest.fn(),
        scrollTop: jest.fn(),
        animate: jest.fn()
    };
    return m$;
});

const $ = require('jquery');
const Swal = require('sweetalert2');
const {
    updateProjectload, showForm, closeCreateFolderForm, createNewFolder, toggleFolder, updateHistory,
    updateHistoryExp, updateFolderOptions, showPopup, showExperimentPopup, closePopup, closePopupS,
    performAction, deleteFile, deleteFileExp, showContextMenu, hideContextMenu, deleteFolder, closeForm,
    addToSelection, removeFromSelection, updateSimulationSelectedFilesUI, updateComparisonSelectedFilesUI,
    simulateSelectedFiles, reloadExp, compareSelectedFiles, goBack
} = require('./workspace');

describe('workspace.js tests', () => {
    beforeEach(() => {
        document.body.innerHTML = `
            <div id="project"></div>
            <div id="createFolderForm"></div>
            <div id="popup"></div>
            <div id="popup2"></div>
            <div id="popup-file-name"></div>
            <div id="popup-file-nameS"></div>
            <div id="popup-file-nameExp"></div>
            <div id="experimentId"></div>
            <div id="context-menu"></div>
            <div id="selected-files-list"></div>
            <button id="simulate-button"></button>
            <button id="compare-button"></button>
            <div id="history-files"></div>
            <div id="exphistory"></div>
        `;
    });

    test('updateProjectload should update project options', async () => {
        fetch.mockResponseOnce('<option value="1">Project 1</option>');
        await updateProjectload();
        expect($('#project').html()).toBe('<option value="1">Project 1</option>');
    });

    test('showForm should display the correct form', () => {
        showForm('vector');
        expect(document.getElementById('vectorForm').style.display).toBe('block');
        expect(document.getElementById('rasterForm').style.display).toBe('none');
    });

    test('closeCreateFolderForm should hide the create folder form', () => {
        closeCreateFolderForm();
        expect(document.getElementById('createFolderForm').style.display).toBe('none');
    });

    test('createNewFolder should display the create folder form', () => {
        createNewFolder();
        expect(document.getElementById('createFolderForm').style.display).toBe('block');
    });

    test('toggleFolder should toggle the display of folder contents', () => {
        const folderId = 'testFolder';
        document.body.innerHTML += `
            <div id="${folderId}-files" style="display: none;"></div>
            <div id="${folderId}-children" style="display: none;"></div>
            <button data-folder-id="${folderId}"><span class="icon-folder">📁</span></button>
        `;
        toggleFolder(folderId);
        expect(document.getElementById(`${folderId}-files`).style.display).toBe('block');
        expect(document.getElementById(`${folderId}-children`).style.display).toBe('block');
        expect(document.querySelector(`[data-folder-id="${folderId}"] .icon-folder`).textContent).toBe('📂');
    });

    test('updateHistory should update the history', async () => {
        fetch.mockResponseOnce('<div>History</div>');
        await updateHistory();
        expect(document.getElementById('history-files').innerHTML).toBe('<div>History</div>');
    });

    test('updateHistoryExp should update the experiment history', async () => {
        fetch.mockResponseOnce('<div>Experiment History</div>');
        await updateHistoryExp();
        expect(document.getElementById('exphistory').innerHTML).toBe('<div>Experiment History</div>');
    });

    test('updateFolderOptions should update folder options', async () => {
        fetch.mockResponseOnce('<option value="1">Folder 1</option>');
        await updateFolderOptions();
        expect($('#dossier_parent').html()).toContain('<option value="1">Folder 1</option>');
    });

    test('showPopup should display the correct popup', () => {
        showPopup('testFile');
        expect(document.getElementById('popup').style.display).toBe('block');
        expect(document.getElementById('popup-file-name').textContent).toBe('testFile');
    });

    test('showExperimentPopup should display the experiment popup', () => {
        showExperimentPopup('testFile', 1);
        expect(document.getElementById('popup-file-nameExp').textContent).toBe('testFile');
        expect(document.getElementById('experimentId').value).toBe('1');
        expect(document.getElementById('popupExp').style.display).toBe('block');
    });

    test('closePopup should hide the popup', () => {
        closePopup(document.getElementById('popup'));
        expect(document.getElementById('popup').style.display).toBe('none');
    });

    test('closePopupS should hide the first popup', () => {
        closePopupS();
        expect(document.getElementById('popup').style.display).toBe('none');
    });

    test('performAction should redirect to the correct URL', () => {
        document.getElementById('popup-file-name').textContent = 'testFile';
        delete window.location;
        window.location = { href: '' };
        performAction();
        expect(window.location.href).toBe('index.php?action=affichage&house=testFile');
    });

    test('deleteFile should call fetch with the correct URL', () => {
        document.getElementById('popup-file-name').textContent = 'testFile';
        Swal.fire = jest.fn().mockResolvedValue({ isConfirmed: true });
        fetch.mockResponseOnce(JSON.stringify({ success: true }));
        deleteFile();
        expect(fetch).toHaveBeenCalledWith('index.php?action=deletFile&fileName=testFile', expect.any(Object));
    });

    test('deleteFileExp should call fetch with the correct URL', () => {
        document.getElementById('popup-file-nameExp').textContent = 'testFile';
        Swal.fire = jest.fn().mockResolvedValue({ isConfirmed: true });
        fetch.mockResponseOnce(JSON.stringify({ success: true }));
        deleteFileExp();
        expect(fetch).toHaveBeenCalledWith('index.php?action=deletFileExp&fileName=testFile', expect.any(Object));
    });

    test('showContextMenu should display the context menu', () => {
        const event = { preventDefault: jest.fn(), pageX: 100, pageY: 100 };
        showContextMenu(event, 'testFolder');
        expect(document.getElementById('context-menu').style.display).toBe('block');
        expect(document.getElementById('context-menu').style.left).toBe('100px');
        expect(document.getElementById('context-menu').style.top).toBe('100px');
    });

    test('hideContextMenu should hide the context menu', () => {
        hideContextMenu();
        expect(document.getElementById('context-menu').style.display).toBe('none');
    });

    test('deleteFolder should call fetch with the correct URL', () => {
        selectedFolderName = 'testFolder';
        Swal.fire = jest.fn().mockResolvedValue({ isConfirmed: true });
        fetch.mockResponseOnce(JSON.stringify({ success: true }));
        deleteFolder();
        expect(fetch).toHaveBeenCalledWith('index.php?action=deleteFolder&folderName=testFolder', expect.any(Object));
    });

    test('closeForm should hide the specified form', () => {
        closeForm('createFolderForm');
        expect(document.getElementById('createFolderForm').style.display).toBe('none');
    });

    test('addToSelection should add file to selection and update UI', () => {
        document.getElementById('popup-file-name').textContent = 'testFile';
        addToSelection();
        expect(selectionFiles).toContainEqual({ name: 'testFile' });
    });

    test('removeFromSelection should remove file from selection and update UI', () => {
        selectionFiles = [{ name: 'testFile' }];
        document.getElementById('popup-file-name').textContent = 'testFile';
        removeFromSelection();
        expect(selectionFiles).not.toContainEqual({ name: 'testFile' });
    });

    test('updateSimulationSelectedFilesUI should update the UI with selected files', () => {
        selectionFiles = [{ name: 'testFile' }];
        updateSimulationSelectedFilesUI();
        expect(document.getElementById('selected-files-list').innerHTML).toContain('testFile');
    });

    test('updateComparisonSelectedFilesUI should update the UI with selected files', () => {
        selectionFiles = [{ name: 'testFile' }];
        updateComparisonSelectedFilesUI();
        expect(document.getElementById('selected-files-list').innerHTML).toContain('testFile');
    });

    test('simulateSelectedFiles should redirect to the correct URL', () => {
        selectionFiles = [{ name: 'testFile' }];
        delete window.location;
        window.location = { href: '' };
        simulateSelectedFiles();
        expect(window.location.href).toBe('index.php?action=affichage&files=testFile');
    });

    test('reloadExp should redirect to the correct URL', () => {
        document.getElementById('experimentId').value = '1';
        delete window.location;
        window.location = { href: '' };
        reloadExp();
        expect(window.location.href).toBe('index.php?action=reloadExp&id=1');
    });

    test('compareSelectedFiles should redirect to the correct URL', () => {
        selectionFiles = [{ name: 'testFile' }];
        delete window.location;
        window.location = { href: '' };
        compareSelectedFiles();
        expect(window.location.href).toBe('index.php?action=compare&files=testFile');
    });

    test('goBack should call window.history.back', () => {
        window.history.back = jest.fn();
        goBack();
        expect(window.history.back).toHaveBeenCalled();
    });
});