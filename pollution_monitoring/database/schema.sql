-- Database creation
CREATE DATABASE IF NOT EXISTS pollution_db;
USE pollution_db;

-- 1. cities table
CREATE TABLE IF NOT EXISTS cities (
    city_id INT AUTO_INCREMENT PRIMARY KEY,
    city_name VARCHAR(100) NOT NULL UNIQUE
);

-- 2. stations table
CREATE TABLE IF NOT EXISTS stations (
    station_id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    station_name VARCHAR(150) NOT NULL,
    FOREIGN KEY (city_id) REFERENCES cities(city_id) ON DELETE CASCADE
);

-- 3. pollutants table
CREATE TABLE IF NOT EXISTS pollutants (
    pollutant_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    safe_limit DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'µg/m³'
);

-- 4. readings table
CREATE TABLE IF NOT EXISTS readings (
    reading_id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    pollutant_id INT NOT NULL,
    value DECIMAL(10, 2) NOT NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES stations(station_id) ON DELETE CASCADE,
    FOREIGN KEY (pollutant_id) REFERENCES pollutants(pollutant_id) ON DELETE CASCADE
);

-- Indexes for performance (often queried by time and location)
CREATE INDEX idx_recorded_at ON readings(recorded_at);
CREATE INDEX idx_station_pollutant ON readings(station_id, pollutant_id);

-- 5. alerts table
CREATE TABLE IF NOT EXISTS alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    pollutant_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (station_id) REFERENCES stations(station_id) ON DELETE CASCADE,
    FOREIGN KEY (pollutant_id) REFERENCES pollutants(pollutant_id) ON DELETE CASCADE
);

-- 6. Trigger: Auto alert when pollution exceeds safe_limit
DELIMITER //
CREATE TRIGGER after_reading_insert
AFTER INSERT ON readings
FOR EACH ROW
BEGIN
    DECLARE v_safe_limit DECIMAL(10,2);
    DECLARE v_pollutant_name VARCHAR(50);
    DECLARE v_station_name VARCHAR(150);

    -- Get safe limit and name
    SELECT safe_limit, name INTO v_safe_limit, v_pollutant_name
    FROM pollutants WHERE pollutant_id = NEW.pollutant_id;

    -- If value exceeds limit, insert alert
    IF NEW.value > v_safe_limit THEN
        -- Get station name
        SELECT station_name INTO v_station_name
        FROM stations WHERE station_id = NEW.station_id;

        INSERT INTO alerts (station_id, pollutant_id, message, created_at)
        VALUES (
            NEW.station_id, 
            NEW.pollutant_id, 
            CONCAT('ALERT: ', v_pollutant_name, ' level at ', v_station_name, ' is ', NEW.value, ' (Safe limit: ', v_safe_limit, ')'),
            NEW.recorded_at
        );
    END IF;
END; //
DELIMITER ;

-- 7. View: City pollution ranking (using AVG and GROUP BY)
CREATE OR REPLACE VIEW city_pollution_ranking AS
SELECT 
    c.city_name,
    p.name AS pollutant_name,
    AVG(r.value) AS avg_pollution,
    MAX(r.value) AS max_pollution,
    COUNT(r.reading_id) AS total_readings
FROM cities c
JOIN stations s ON c.city_id = s.city_id
JOIN readings r ON s.station_id = r.station_id
JOIN pollutants p ON r.pollutant_id = p.pollutant_id
GROUP BY c.city_id, p.pollutant_id
ORDER BY avg_pollution DESC;

-- 8. Stored Procedure: Generate random reading
DELIMITER //
CREATE PROCEDURE generate_random_reading()
BEGIN
    DECLARE v_station_id INT;
    DECLARE v_pollutant_id INT;
    DECLARE v_value DECIMAL(10,2);
    
    -- Pick random station
    SELECT station_id INTO v_station_id FROM stations ORDER BY RAND() LIMIT 1;
    
    -- Pick random pollutant
    SELECT pollutant_id INTO v_pollutant_id FROM pollutants ORDER BY RAND() LIMIT 1;
    
    -- Generate random value (simulate typical ranges)
    -- E.g. 0 to 200 roughly, some spikes
    SET v_value = RAND() * 200;
    
    -- Insert reading
    INSERT INTO readings (station_id, pollutant_id, value, recorded_at)
    VALUES (v_station_id, v_pollutant_id, v_value, NOW());
END; //
DELIMITER ;

-- Insert initial sample data
INSERT IGNORE INTO cities (city_name) VALUES ('Metropolis'), ('Gotham'), ('Star City');

INSERT IGNORE INTO stations (city_id, station_name) VALUES 
(1, 'Central Park Monitor'),
(1, 'Downtown Station'),
(2, 'Wayne Tower Sensor'),
(2, 'Arkham District'),
(3, 'Queen Consolidated HQ');

INSERT IGNORE INTO pollutants (name, safe_limit, unit) VALUES 
('PM2.5', 35.00, 'µg/m³'),
('PM10', 50.00, 'µg/m³'),
('O3 (Ozone)', 100.00, 'ppb'),
('NO2', 40.00, 'ppb');

-- 9. View: Moving average using Window Functions
CREATE OR REPLACE VIEW station_moving_average AS
SELECT 
    r.reading_id,
    s.station_name,
    p.name AS pollutant_name,
    r.value,
    r.recorded_at,
    AVG(r.value) OVER (
        PARTITION BY r.station_id, r.pollutant_id 
        ORDER BY r.recorded_at 
        ROWS BETWEEN 4 PRECEDING AND CURRENT ROW
    ) AS moving_avg_5
FROM readings r
JOIN stations s ON r.station_id = s.station_id
JOIN pollutants p ON r.pollutant_id = p.pollutant_id;

