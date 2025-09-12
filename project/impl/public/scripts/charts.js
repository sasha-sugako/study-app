// Initializes charts using the Chart.js library.
function initCharts(totalStudied, totalToStudy, stats, week_stats) {
    const overallData = {
        labels: ["Studované", "Nestudované"],
        datasets: [{
            data: [totalStudied, totalToStudy],
            backgroundColor: ["#a8e4a0", "#dca0e4"],
            borderWidth: 0
        }]
    };
    // Overall progress chart (doughnut)
    new Chart(document.getElementById("overallStatsChart"), {
        type: "doughnut",
        data: overallData
    });
    // Extracting data for deck-based bar chart and or weekly statistics chart
    const deckLabels = stats.map(stat => stat.deck_name);
    const studiedCards = stats.map(stat => stat.studied_cards);
    const toStudyCards = stats.map(stat => stat.cards_to_study);
    const decksRef = stats.map(stat => stat.deck_href);
    const finishedTests = stats.map(stat => stat.tests);
    const dates = week_stats.map(stat => new Date(stat.date.date).toDateString().slice(4));
    const weekStudiedCards = week_stats.map(stat => stat.total_studied);
    const weekTests = week_stats.map(stat => stat.total_tests);

    // Per-deck stats chart (bar)
    new Chart(document.getElementById("deckStatsChart"), {
        type: "bar",
        data: {
            labels: deckLabels,
            datasets: [
                {
                    label: "Studované",
                    data: studiedCards,
                    backgroundColor: "#a8e4a0"
                },
                {
                    label: "Nestudované",
                    data: toStudyCards,
                    backgroundColor: "#dca0e4"
                }
            ]
        },
        options: {
            responsive: true,
            indexAxis: 'x',
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            // Navigate to deck page on bar click
            onClick: (event, elements) => {
                if (elements.length > 0) {
                    const index = elements[0].index;
                    window.location.href = decksRef[index];
                }
            }
        }
    });
    // Test results chart (doughnut), shown only if there are any tests
    if (finishedTests.filter(test => test !== 0).length === 0){
        // If no tests were completed, show a message instead of the chart
        let no_tests = document.createElement('p');
        no_tests.textContent = 'Žádné testy za dnešek';
        no_tests.style.textAlign = 'center';
        document.querySelector('.for_tests').insertAdjacentElement('afterend', no_tests);
        const testChart = document.getElementById("testsStatsChart")
        testChart.style.display = 'none';
        testChart.parentElement.style.display = 'none';
    }
    else {
        // Create the doughnut chart for test results by deck
        new Chart(document.getElementById("testsStatsChart"), {
            type: "doughnut",
            data: {
                labels: deckLabels,
                datasets: [{
                    data: finishedTests,
                    backgroundColor: ["#a8e4a0", "#dca0e4", "#ffd6a5",
                        "#caffbf", "#bdb2ff", "#ffc6ff", "#9bf6ff", "#fdffb6"],
                    borderWidth: 0
                }]
            }
        });
    }
    // Weekly performance chart (bar)
    new Chart(document.getElementById("weekStatsChart"), {
        type: "bar",
        data: {
            labels: dates,
            datasets: [
                {
                    label: "Počet studovaných kartiček",
                    data: weekStudiedCards,
                    backgroundColor: "#a8e4a0"
                },
                {
                    label: "Počet provedených testů",
                    data: weekTests,
                    backgroundColor: "#dca0e4"
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
        }
    });

}