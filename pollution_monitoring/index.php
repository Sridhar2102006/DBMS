<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intelligent Air Pollution Monitoring & Analysis System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/style.css">
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <header>
        <h1>GovTrack: Air Pollution Monitoring</h1>
        <div class="controls">
            <button id="simulateBtn">Simulate New Data</button>
        </div>
    </header>

    <main class="dashboard-grid">
        
        <!-- Worst Station Card -->
        <div class="card worst-station" id="worst-station-info">
            <div>
                <h2>Highest Pollution Recorded (All Time)</h2>
                <p>Loading...</p>
            </div>
            <div class="worst-value">--</div>
        </div>

        <!-- Trend Chart (Using Window Function Moving Average) -->
        <div class="card chart-container">
            <h2>Pollution Trends (PM2.5)</h2>
            <canvas id="trendChart"></canvas>
        </div>

        <!-- City Rankings -->
        <div class="card rankings-container">
            <h2>City Pollution Rankings</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>City</th>
                        <th>Pollutant</th>
                        <th>Avg Value</th>
                    </tr>
                </thead>
                <tbody id="rankings-body">
                    <tr><td colspan="4">Loading data...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Alerts -->
        <div class="card alerts-container">
            <h2>Recent Alerts (Trigger Generated)</h2>
            <div id="alerts-container">
                <p>Loading alerts...</p>
            </div>
        </div>

    </main>
</div>

<!-- Custom JS -->
<script src="assets/script.js"></script>

</body>
</html>
