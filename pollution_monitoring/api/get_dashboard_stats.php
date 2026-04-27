<?php
// api/get_dashboard_stats.php
require_once '../config/db.php';
header('Content-Type: application/json');

$response = [
    'city_rankings' => [],
    'latest_alerts' => [],
    'most_polluted_station' => null
];

// 1. Get City Rankings from the View
$ranking_query = "SELECT city_name, pollutant_name, avg_pollution FROM city_pollution_ranking ORDER BY avg_pollution DESC LIMIT 10";
$result = $conn->query($ranking_query);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $row['avg_pollution'] = round($row['avg_pollution'], 2);
        $response['city_rankings'][] = $row;
    }
}

// 2. Get Latest Alerts
$alerts_query = "SELECT a.message, a.created_at, s.station_name, c.city_name 
                 FROM alerts a 
                 JOIN stations s ON a.station_id = s.station_id 
                 JOIN cities c ON s.city_id = c.city_id 
                 ORDER BY a.created_at DESC LIMIT 5";
$result = $conn->query($alerts_query);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $response['latest_alerts'][] = $row;
    }
}

// 3. Subquery: Most Polluted Station Overall (ever recorded max value)
$worst_station_query = "
    SELECT s.station_name, c.city_name, p.name as pollutant, r.value, r.recorded_at
    FROM readings r
    JOIN stations s ON r.station_id = s.station_id
    JOIN cities c ON s.city_id = c.city_id
    JOIN pollutants p ON r.pollutant_id = p.pollutant_id
    WHERE r.value = (SELECT MAX(value) FROM readings)
    LIMIT 1
";
$result = $conn->query($worst_station_query);
if ($result && $result->num_rows > 0) {
    $response['most_polluted_station'] = $result->fetch_assoc();
}

echo json_encode($response);
$conn->close();
?>
