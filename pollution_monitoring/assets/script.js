// assets/script.js

let pollutionChart = null;

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardData();
    fetchChartData();

    // Set up simulate data button
    document.getElementById('simulateBtn').addEventListener('click', simulateData);
    
    // Auto refresh every 30 seconds
    setInterval(() => {
        fetchDashboardData();
        fetchChartData();
    }, 30000);
});

async function simulateData() {
    try {
        const btn = document.getElementById('simulateBtn');
        btn.disabled = true;
        btn.textContent = 'Simulating...';

        // Generate 5 random readings
        const res = await fetch('api/generate_data.php?count=5');
        const data = await res.json();
        
        console.log(data.message);
        
        // Refresh dashboard
        fetchDashboardData();
        fetchChartData();

        btn.disabled = false;
        btn.textContent = 'Simulate New Data';
    } catch (error) {
        console.error("Error simulating data:", error);
    }
}

async function fetchDashboardData() {
    try {
        const res = await fetch('api/get_dashboard_stats.php');
        const data = await res.json();

        // 1. Update Worst Station
        if (data.most_polluted_station) {
            const worst = data.most_polluted_station;
            document.getElementById('worst-station-info').innerHTML = `
                <div>
                    <h2>Highest Pollution Recorded (All Time)</h2>
                    <p>${worst.station_name}, ${worst.city_name} - ${worst.pollutant}</p>
                    <small>Recorded at: ${new Date(worst.recorded_at).toLocaleString()}</small>
                </div>
                <div class="worst-value">${parseFloat(worst.value).toFixed(1)}</div>
            `;
        }

        // 2. Update Rankings Table
        const tbody = document.getElementById('rankings-body');
        tbody.innerHTML = '';
        data.city_rankings.forEach((rank, index) => {
            const tr = document.createElement('tr');
            
            // Determine severity badge
            let badgeClass = 'badge-warning';
            if (rank.avg_pollution > 50) badgeClass = 'badge-danger';
            
            tr.innerHTML = `
                <td>#${index + 1}</td>
                <td>${rank.city_name}</td>
                <td>${rank.pollutant_name}</td>
                <td><span class="badge ${badgeClass}">${rank.avg_pollution}</span></td>
            `;
            tbody.appendChild(tr);
        });

        // 3. Update Alerts
        const alertsContainer = document.getElementById('alerts-container');
        alertsContainer.innerHTML = '';
        if (data.latest_alerts.length === 0) {
            alertsContainer.innerHTML = '<p style="color: var(--text-secondary)">No alerts recently.</p>';
        } else {
            data.latest_alerts.forEach(alert => {
                const div = document.createElement('div');
                div.className = 'alert-item';
                div.innerHTML = `
                    <div class="alert-time">${new Date(alert.created_at).toLocaleString()}</div>
                    <div class="alert-message">${alert.message}</div>
                `;
                alertsContainer.appendChild(div);
            });
        }

    } catch (error) {
        console.error("Error fetching dashboard data:", error);
    }
}

async function fetchChartData() {
    try {
        const res = await fetch('api/get_chart_data.php');
        const data = await res.json();

        const ctx = document.getElementById('trendChart').getContext('2d');
        
        if (pollutionChart) {
            pollutionChart.destroy();
        }

        Chart.defaults.color = '#94a3b8';
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";

        pollutionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#f8fafc'
                        }
                    },
                    title: {
                        display: true,
                        text: 'PM2.5 Moving Average (Window Function)',
                        color: '#f8fafc',
                        font: { size: 16 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#334155'
                        }
                    },
                    x: {
                        grid: {
                            color: '#334155'
                        }
                    }
                }
            }
        });

    } catch (error) {
        console.error("Error fetching chart data:", error);
    }
}
