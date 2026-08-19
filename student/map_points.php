<?php
require_once __DIR__ . '/../includes/functions.php';
require_student();
header('Content-Type: application/json');

$campus = ['lat' => 5.6505, 'lng' => -0.1863];

$stmt = $pdo->query("SELECT id, name, latitude, longitude, distance_to_campus_km FROM hostels");
$rows = $stmt->fetchAll();

$points = array_map(function ($r) {
    return [
        'id' => (int)$r['id'],
        'name' => $r['name'],
        'lat' => (float)$r['latitude'],
        'lng' => (float)$r['longitude'],
        'distance' => (float)$r['distance_to_campus_km'],
    ];
}, $rows);

echo json_encode(['campus' => $campus, 'points' => $points]);
