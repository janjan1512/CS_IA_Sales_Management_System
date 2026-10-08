<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


try {
    $weeksAhead = isset($_GET['weeks']) ? max(1, min(8, (int) $_GET['weeks'])) : 8;
    $history    = isset($_GET['history']) ? max(4, min(30, (int) $_GET['history'])) : 16;

    $scriptPath = __DIR__ . '/forecast.py';
    if (!file_exists($scriptPath)) {
        throw new Exception("ML script not found at {$scriptPath}");
    }

    $python = '/Library/Frameworks/Python.framework/Versions/3.11/bin/python3';

    $command = sprintf(
        'cd %s && %s %s %d %d 2>&1',
        escapeshellarg(__DIR__),
        escapeshellarg($python),
        escapeshellarg(__DIR__ . '/forecast.py'),
        $weeksAhead,
        $history
    );

    $output = shell_exec($command);
    if (!$output) {
        throw new Exception('No output from Python script.');
    }

    $payload = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON from Python script: ' . $output);
    }
    if (!empty($payload['error'])) {
        throw new Exception($payload['error']);
    }

    $payload['success'] = true;
    echo json_encode($payload);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
