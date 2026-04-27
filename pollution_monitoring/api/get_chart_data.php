<?php
// api/get_chart_data.php
require_once '../config/db.php';
header('Content-Type: application/json');

$response = [
    'labels' => [], // Time
    'datasets' => [] // Chart.js datasets
];

// We want to fetch the moving average for a specific pollutant (e.g. PM2.5) across a few stations
$query = "
    SELECT station_name, pollutant_name, recorded_at, moving_avg_5, value
    FROM station_moving_average
    WHERE pollutant_name = 'PM2.5'
    ORDER BY recorded_at ASC
    LIMIT 100
";

$result = $conn->query($query);
$data_by_station = [];
$labels_set = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $station = $row['station_name'];
        $time = date('H:i', strtotime($row['recorded_at']));
        
        if (!in_array($time, $labels_set)) {
            $labels_set[] = $time;
        }

        if (!isset($data_by_station[$station])) {
            $data_by_station[$station] = [];
        }
        
        // Push the moving average instead of raw value to smooth the chart
        $data_by_station[$station][] = [
            'time' => $time,
            'value' => round($row['moving_avg_5'], 2)
        ];
    }
}

$response['labels'] = $labels_set;

// Colors for the chart
$colors = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6'];
$color_idx = 0;

foreach ($data_by_station as $station => $readings) {
    $dataset = [
        'label' => $station . ' (Moving Avg)',
        'data' => [],
        'borderColor' => $colors[$color_idx % count($colors)],
        'fill' => false,
        'tension' => 0.4
    ];

    // Align data with labels
    foreach ($labels_set as $label) {
        $found_value = null;
        foreach ($readings as $r) {
            if ($r['time'] === $label) {
                $found_value = $r['value'];
                break;
            }
        }
        $dataset['data'][] = $found_value;
    }

    $response['datasets'][] = $dataset;
    $color_idx++;
}

echo json_encode($response);
$conn->close();
?>
