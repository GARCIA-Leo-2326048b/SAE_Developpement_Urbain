// Importer le fichier JavaScript contenant la logique (à adapter selon ton organisation)
import './graphique.js'; // Assurez-vous que la logique est exportée ou accessible pour les tests

describe('Fonctions de manipulation des données', () => {
    let chartData;

    beforeEach(() => {
        // Initialiser les données de test avant chaque test
        chartData = {
            labels: ['label1', 'label2', 'label3'],
            dataSim: [10, 20, 30],
            dataVer: [5, 15, 25],
        };
        window.chartData = chartData; // Simuler la variable globale chartData
    });

    test('Test de la normalisation des données', () => {
        const normalizeChecked = true;

        // Normalisation des données
        const normalizedData = applyNormalization(chartData, normalizeChecked);

        expect(normalizedData.dataSim).toEqual([1, 1, 1]);
        expect(normalizedData.dataVer).toEqual([0.5, 0.75, 0.8333333333333334]);
    });

    test('Test de la création de datasets', () => {
        const selectedOptions = ['areaMean', 'shapeIndexMax'];
        const showSimulation = true;
        const showVeriteTerrain = true;

        // Appliquer un filtre basé sur les options
        const filteredData = getFilteredData(selectedOptions);

        // Créer les datasets pour un graphique
        const datasets = createDatasets(filteredData, 'bar', showSimulation, showVeriteTerrain);

        // Vérifier qu'un dataset pour chaque donnée a été créé
        expect(datasets).toHaveLength(2);
        expect(datasets[0].label).toBe('Simulation');
        expect(datasets[1].label).toBe('Vérité terrain');
        expect(datasets[0].data).toEqual(filteredData.dataSim);
        expect(datasets[1].data).toEqual(filteredData.dataVer);
    });

    test('Test de la récupération des données filtrées', () => {
        const selectedOptions = ['areaMean', 'shapeIndexMax'];

        // Récupérer les données filtrées
        const filteredData = getFilteredData(selectedOptions);

        expect(filteredData.labels).toEqual(['label1', 'label2']);
        expect(filteredData.dataSim).toEqual([10, 30]);
        expect(filteredData.dataVer).toEqual([5, 25]);
    });

    test('Test de la création d\'un graphique (sans DOM)', () => {
        // Créer un dataset factice
        const filteredData = {
            labels: ['Label1', 'Label2'],
            dataSim: [10, 20],
            dataVer: [5, 15],
        };

        const datasets = createDatasets(filteredData, 'bar', true, true);

        // S'assurer que les datasets sont bien formés
        expect(datasets).toHaveLength(2);
        expect(datasets[0].label).toBe('Simulation');
        expect(datasets[1].label).toBe('Vérité terrain');
        expect(datasets[0].data).toEqual(filteredData.dataSim);
        expect(datasets[1].data).toEqual(filteredData.dataVer);
    });

    // Test pour appliquer la normalisation et obtenir les valeurs normalisées
    test('Vérification de la normalisation des valeurs', () => {
        const filteredData = {
            dataSim: [10, 20, 30],
            dataVer: [5, 15, 25],
        };

        const normalizedData = applyNormalization(filteredData, true);

        expect(normalizedData.dataSim).toEqual([1, 1, 1]);
        expect(normalizedData.dataVer).toEqual([0.5, 0.75, 0.8333333333333334]);
    });
});
