-- ============================================================================
-- USEP E-PARKING SYSTEM — COMPREHENSIVE DATABASE SCHEMA
-- ============================================================================
-- Organized by: Database Setup → Tables → Views → Triggers
-- All new views for reservations, vehicles, reports, and entry/exit logs integrated
-- Preserved existing views for users and slots (no changes)
-- ============================================================================


-- ============================================================================
-- SECTION 0. DATABASE SETUP
-- ============================================================================

CREATE DATABASE IF NOT EXISTS usep_epark;
USE usep_epark;


-- ============================================================================
-- SECTION 1. TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1.1 users
-- Includes: birthdate, gender, profile_picture
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    user_id          INT AUTO_INCREMENT PRIMARY KEY,
    firstname        VARCHAR(50)   NOT NULL,
    lastname         VARCHAR(50)   NOT NULL,
    email            VARCHAR(100)  UNIQUE NOT NULL,
    contact_number   VARCHAR(20)   NOT NULL,
    role             ENUM('customer','staff','admin') NOT NULL,
    qr_code          VARCHAR(255),
    password_hash    VARCHAR(255)  NOT NULL,
    status           ENUM('active','suspended') NOT NULL DEFAULT 'active',
    user_code        VARCHAR(20)   UNIQUE,
    last_login       DATETIME      NULL,
    birthdate        DATE,
    gender           VARCHAR(50),
    profile_picture  VARCHAR(255)  NULL,
    created_at       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ----------------------------------------------------------------------------
-- 1.2 vehicle
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS vehicle (
    vehicle_id   INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT         NOT NULL,
    plate_number VARCHAR(20) NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- ----------------------------------------------------------------------------
-- 1.3 parking_slots
-- Seeded with 30 slots across Sections A, B, C
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS parking_slots (
    slot_id       INT AUTO_INCREMENT PRIMARY KEY,
    slot_number   VARCHAR(10)  UNIQUE NOT NULL,
    status        ENUM('available','occupied','reserved','maintenance') NOT NULL DEFAULT 'available',
    location_area VARCHAR(50)  NOT NULL
);

-- Seed: Section A — Cars (10 slots)
INSERT IGNORE INTO parking_slots (slot_number, location_area, status) VALUES
('A-01', 'A', 'available'),
('A-02', 'A', 'available'),
('A-03', 'A', 'available'),
('A-04', 'A', 'available'),
('A-05', 'A', 'available'),
('A-06', 'A', 'available'),
('A-07', 'A', 'available'),
('A-08', 'A', 'available'),
('A-09', 'A', 'available'),
('A-10', 'A', 'available');

-- Seed: Section B — Cars (10 slots)
INSERT IGNORE INTO parking_slots (slot_number, location_area, status) VALUES
('B-01', 'B', 'available'),
('B-02', 'B', 'available'),
('B-03', 'B', 'available'),
('B-04', 'B', 'available'),
('B-05', 'B', 'available'),
('B-06', 'B', 'available'),
('B-07', 'B', 'available'),
('B-08', 'B', 'available'),
('B-09', 'B', 'available'),
('B-10', 'B', 'available');

-- Seed: Section C — Motorcycles (10 slots)
INSERT IGNORE INTO parking_slots (slot_number, location_area, status) VALUES
('C-01', 'C', 'available'),
('C-02', 'C', 'available'),
('C-03', 'C', 'available'),
('C-04', 'C', 'available'),
('C-05', 'C', 'available'),
('C-06', 'C', 'available'),
('C-07', 'C', 'available'),
('C-08', 'C', 'available'),
('C-09', 'C', 'available'),
('C-10', 'C', 'available');

-- ----------------------------------------------------------------------------
-- 1.4 reservations
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS reservations (
    reservation_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id            INT      NOT NULL,
    slot_id            INT      NOT NULL,
    time_reserved      DATETIME NOT NULL,
    reservation_expiry DATETIME NOT NULL,
    status             ENUM('active','expired','cancelled','completed') NOT NULL DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id)         ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES parking_slots(slot_id) ON DELETE CASCADE
);

-- ----------------------------------------------------------------------------
-- 1.5 entry_exit_logs
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS entry_exit_logs (
    log_id         INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT,
    vehicle_id     INT             NOT NULL,
    slot_id        INT             NOT NULL,
    time_in        DATETIME        NOT NULL,
    time_out       DATETIME,
    total_duration DECIMAL(10,2),
    parking_fee    DECIMAL(10,2),
    log_status     ENUM('in', 'out', 'denied') NOT NULL DEFAULT 'in',
    FOREIGN KEY (reservation_id) REFERENCES reservations(reservation_id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_id)     REFERENCES vehicle(vehicle_id)          ON DELETE CASCADE,
    FOREIGN KEY (slot_id)        REFERENCES parking_slots(slot_id)       ON DELETE CASCADE
);

-- ----------------------------------------------------------------------------
-- 1.6 payments
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
    payment_id     INT AUTO_INCREMENT PRIMARY KEY,
    log_id         INT            NOT NULL,
    amount         DECIMAL(10,2)  NOT NULL,
    payment_date   DATETIME       NOT NULL,
    method         VARCHAR(50)    NOT NULL,
    receipt_number VARCHAR(100)   NOT NULL,
    FOREIGN KEY (log_id) REFERENCES entry_exit_logs(log_id) ON DELETE CASCADE
);


-- ============================================================================
-- SECTION 2. VIEWS
-- ============================================================================

-- ============================================================================
-- 1. USER ACCESS PAGE
-- ============================================================================

CREATE OR REPLACE VIEW view_users AS
SELECT
    u.user_id,
    u.user_code,
    u.firstname,
    u.lastname,
    u.email,
    u.contact_number,
    u.role,
    u.status,
    u.qr_code,
    u.birthdate,
    u.gender,
    u.profile_picture,
    u.last_login,
    u.created_at
FROM users u;

CREATE OR REPLACE VIEW view_staff_activity AS
SELECT
    user_id,
    user_code,
    firstname,
    lastname,
    email,
    role,
    status,
    last_login,
    DATEDIFF(NOW(), last_login) AS days_since_last_login
FROM users
WHERE role IN ('staff', 'admin')
ORDER BY last_login DESC;

CREATE OR REPLACE VIEW view_user_statistics AS
SELECT
    u.user_id,
    u.user_code,
    u.firstname,
    u.lastname,
    u.email,
    u.role,
    COUNT(DISTINCT v.vehicle_id)   AS total_vehicles,
    COUNT(DISTINCT r.reservation_id) AS total_reservations,
    COUNT(DISTINCT eel.log_id)     AS total_parking_sessions,
    COALESCE(SUM(p.amount), 0)     AS total_amount_paid,
    MAX(eel.time_in)               AS last_parking_date
FROM users u
LEFT JOIN vehicle          v   ON u.user_id      = v.user_id
LEFT JOIN reservations     r   ON u.user_id      = r.user_id
LEFT JOIN entry_exit_logs  eel ON v.vehicle_id   = eel.vehicle_id
LEFT JOIN payments         p   ON eel.log_id     = p.log_id
GROUP BY u.user_id, u.user_code, u.firstname, u.lastname, u.email, u.role
ORDER BY u.user_id;

-- ============================================================================
-- 2. SLOT MONITORING PAGE
-- ============================================================================

CREATE OR REPLACE VIEW view_all_slots_status AS
SELECT
    s.slot_id,
    s.slot_number,
    s.location_area,
    s.status,
    v.plate_number,
    CONCAT(u.firstname, ' ', u.lastname) AS occupant_name,
    e.time_in
FROM parking_slots s
LEFT JOIN entry_exit_logs e ON e.slot_id = s.slot_id AND e.time_out IS NULL
LEFT JOIN vehicle v ON v.vehicle_id = e.vehicle_id
LEFT JOIN users u ON u.user_id = v.user_id;

CREATE OR REPLACE VIEW view_all_slots_status AS
SELECT
    s.slot_id,
    s.slot_number,
    s.location_area,
    s.status,
    v.plate_number,
    CONCAT(u.firstname, ' ', u.lastname) AS occupant_name,
    e.time_in
FROM parking_slots s
LEFT JOIN entry_exit_logs e ON e.slot_id = s.slot_id AND e.time_out IS NULL
LEFT JOIN vehicle v ON v.vehicle_id = e.vehicle_id
LEFT JOIN users u ON u.user_id = v.user_id;

CREATE OR REPLACE VIEW view_slot_availability AS
SELECT
    location_area,
    COUNT(*)                                                                          AS total_slots,
    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END)                            AS available_slots,
    SUM(CASE WHEN status = 'occupied'  THEN 1 ELSE 0 END)                            AS occupied_slots,
    SUM(CASE WHEN status = 'reserved'  THEN 1 ELSE 0 END)                            AS reserved_slots,
    ROUND(SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS availability_percentage
FROM parking_slots
GROUP BY location_area
ORDER BY location_area;

-- ============================================================================
-- 3. VEHICLES PAGE
-- ============================================================================

CREATE OR REPLACE VIEW view_vehicle_stats AS
SELECT
    COUNT(DISTINCT v.vehicle_id)                                                    AS total,
    COUNT(DISTINCT CASE WHEN e.log_status = 'in' THEN e.vehicle_id END)             AS inside,
    SUM(CASE WHEN v.vehicle_type = 'car'        THEN 1 ELSE 0 END)                 AS cars,
    SUM(CASE WHEN v.vehicle_type = 'motorcycle' THEN 1 ELSE 0 END)                 AS motorcycles
FROM vehicle v
LEFT JOIN entry_exit_logs e ON e.vehicle_id = v.vehicle_id AND e.log_status = 'in';

CREATE OR REPLACE VIEW view_vehicle_list AS
SELECT
    v.vehicle_id,
    v.plate_number,
    v.vehicle_type,
    u.user_id,
    u.user_code,
    u.firstname,
    u.lastname,
    u.role,
    CASE WHEN e.log_id IS NOT NULL THEN 'inside' ELSE 'outside' END AS parking_status,
    s.slot_number,
    (
        SELECT MAX(time_in)
        FROM entry_exit_logs
        WHERE vehicle_id = v.vehicle_id
    ) AS last_seen
FROM vehicle v
JOIN users u ON u.user_id = v.user_id
LEFT JOIN entry_exit_logs e 
    ON e.vehicle_id = v.vehicle_id 
    AND e.log_status = 'in'          -- consistent with trigger logic
LEFT JOIN parking_slots s ON s.slot_id = e.slot_id;

-- ============================================================================
-- 4. REPORTS AND ANALYTICS PAGE
-- ============================================================================
CREATE OR REPLACE VIEW view_current_month_summary AS
SELECT
    COUNT(*)                         AS total_entries,
    COALESCE(SUM(parking_fee), 0)    AS revenue,
    COALESCE(AVG(total_duration), 0) AS avg_duration
FROM entry_exit_logs
WHERE log_status = 'out'
  AND YEAR(time_in)  = YEAR(CURDATE())
  AND MONTH(time_in) = MONTH(CURDATE());
  
  CREATE OR REPLACE VIEW view_monthly_revenue_trend AS
SELECT
    YEAR(p.payment_date)                        AS year,
    MONTH(p.payment_date)                       AS month,
    DATE_FORMAT(p.payment_date, '%b')           AS month_label,
    CONCAT(YEAR(p.payment_date), '-',
           LPAD(MONTH(p.payment_date), 2, '0')) AS yearmonth,
    COALESCE(SUM(p.amount), 0)                  AS total_revenue,
    COUNT(DISTINCT p.payment_id)                AS total_transactions
FROM payments p
GROUP BY
    YEAR(p.payment_date),
    MONTH(p.payment_date),
    DATE_FORMAT(p.payment_date, '%b')
ORDER BY year DESC, month DESC
LIMIT 7;

CREATE OR REPLACE VIEW view_current_month_top_vehicles AS
SELECT
    v.plate_number,
    CONCAT(u.firstname, ' ', u.lastname)  AS owner,
    v.vehicle_type,
    COUNT(eel.log_id)                     AS total_entries,
    COALESCE(SUM(eel.total_duration), 0)  AS total_hours,
    COALESCE(SUM(eel.parking_fee), 0)     AS total_fee,
    COALESCE(AVG(eel.total_duration), 0)  AS avg_duration
FROM entry_exit_logs eel
JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
JOIN users u   ON v.user_id      = u.user_id
WHERE eel.log_status = 'out'
  AND YEAR(eel.time_in)  = YEAR(CURDATE())
  AND MONTH(eel.time_in) = MONTH(CURDATE())
GROUP BY v.vehicle_id, v.plate_number, u.firstname, u.lastname, v.vehicle_type
ORDER BY total_entries DESC
LIMIT 5;

CREATE OR REPLACE VIEW view_daily_revenue AS
SELECT
    DATE(p.payment_date)         AS payment_date,
    COUNT(DISTINCT p.payment_id) AS total_transactions,
    COUNT(DISTINCT v.user_id)    AS unique_users,
    SUM(p.amount)                AS total_revenue,
    AVG(p.amount)                AS average_transaction,
    MIN(p.amount)                AS minimum_payment,
    MAX(p.amount)                AS maximum_payment
FROM payments p
JOIN entry_exit_logs eel ON p.log_id      = eel.log_id
JOIN vehicle         v   ON eel.vehicle_id = v.vehicle_id
GROUP BY DATE(p.payment_date)
ORDER BY payment_date DESC;

CREATE OR REPLACE VIEW view_monthly_parking_stats AS
SELECT
    YEAR(eel.time_in)              AS year,
    MONTH(eel.time_in)             AS month,
    COUNT(DISTINCT eel.log_id)     AS total_parking_sessions,
    COUNT(DISTINCT eel.vehicle_id) AS unique_vehicles,
    COUNT(DISTINCT v.user_id)      AS unique_users,
    SUM(eel.parking_fee)           AS total_parking_fees,
    AVG(eel.total_duration)        AS avg_parking_duration
FROM entry_exit_logs eel
JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
WHERE eel.log_status = 'out'
GROUP BY YEAR(eel.time_in), MONTH(eel.time_in)
ORDER BY year DESC, month DESC;

-- ============================================================================
-- 5. RESERVATIONS PAGE — VIEWS
-- ============================================================================
-- Add these to your main schema file under SECTION 2. VIEWS
-- ============================================================================


-- ----------------------------------------------------------------------------
-- view_reservations_full
-- Purpose : Main data source for the reservations table.
--           Joins reservations → users → vehicle → parking_slots so the
--           PHP layer never touches raw tables directly.
-- Used by : get_reservations.php
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW view_reservations_full AS
SELECT
    r.reservation_id,
    -- Reference number displayed in the UI (RES-001 style)
    CONCAT('RES-', LPAD(r.reservation_id, 3, '0'))      AS ref_number,

    -- User info
    r.user_id,
    u.user_code,
    u.firstname,
    u.lastname,
    CONCAT(u.firstname, ' ', u.lastname)                 AS full_name,
    u.email,
    u.contact_number,
    u.role                                               AS user_role,
    u.profile_picture,

    -- Vehicle info (latest registered vehicle for this user)
    v.vehicle_id,
    v.plate_number,
    v.vehicle_type,

    -- Slot info
    ps.slot_id,
    ps.slot_number,
    ps.location_area,

    -- Timing
    r.time_reserved,
    r.reservation_expiry,
    TIMESTAMPDIFF(MINUTE, r.time_reserved, r.reservation_expiry) AS duration_minutes,
    TIMESTAMPDIFF(MINUTE, NOW(), r.reservation_expiry)           AS minutes_until_expiry,

    -- Status
    r.status,

    -- Derived display helpers
    CASE
        WHEN DATE(r.time_reserved) = CURDATE()        THEN 'Today'
        WHEN DATE(r.time_reserved) = CURDATE() - INTERVAL 1 DAY THEN 'Yesterday'
        ELSE DATE_FORMAT(r.time_reserved, '%b %d, %Y')
    END AS date_label,
    TIME_FORMAT(r.time_reserved, '%h:%i %p')             AS time_label

FROM reservations r
JOIN users        u  ON r.user_id = u.user_id
-- Pick the vehicle most recently registered for this user
JOIN vehicle      v  ON v.vehicle_id = (
    SELECT vehicle_id FROM vehicle
    WHERE user_id = r.user_id
    ORDER BY vehicle_id DESC
    LIMIT 1
)
JOIN parking_slots ps ON r.slot_id = ps.slot_id
ORDER BY
    -- Pending first, then by most recently reserved
    FIELD(r.status, 'active', 'completed', 'expired', 'cancelled'),
    r.time_reserved DESC;


-- ----------------------------------------------------------------------------
-- view_reservation_stats_today
-- Purpose : Aggregate counts for the four stat cards at the top of the page.
-- Used by : get_reservations.php
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW view_reservation_stats_today AS
SELECT
    COUNT(*)                                                             AS total_today,
    SUM(CASE WHEN status = 'active'    THEN 1 ELSE 0 END)               AS pending,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END)               AS approved,
    SUM(CASE WHEN status = 'cancelled' OR status = 'expired'
             THEN 1 ELSE 0 END)                                          AS cancelled
FROM reservations
WHERE DATE(time_reserved) = CURDATE();

-- ============================================================================
--  6. ENTRY / EXIT LOGS PAGE
-- ============================================================================
-- Run this in MySQL before using the PHP backend files.
-- ============================================================================


-- ----------------------------------------------------------------------------
-- 1. view_logs_list
--    Main table data: one row per log entry with all joined details.
--    Used by: get_logs.php
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW view_logs_list AS
SELECT
    eel.log_id,
    eel.log_status,
    eel.time_in,
    eel.time_out,
    eel.total_duration,                          -- in hours (decimal)
    eel.parking_fee,

    -- Vehicle
    v.vehicle_id,
    v.plate_number,
    v.vehicle_type,

    -- Owner
    u.user_id,
    u.user_code,
    CONCAT(u.firstname, ' ', u.lastname) AS owner_name,
    u.contact_number,

    -- Slot
    ps.slot_id,
    ps.slot_number,
    ps.location_area,

    -- Payment (LEFT JOIN — may not exist yet if still parked)
    p.payment_id,
    p.amount        AS payment_amount,
    p.payment_date,
    p.method        AS payment_method,
    p.receipt_number,

    -- Reservation link (optional)
    eel.reservation_id

FROM entry_exit_logs eel
JOIN vehicle       v   ON eel.vehicle_id = v.vehicle_id
JOIN users         u   ON v.user_id      = u.user_id
JOIN parking_slots ps  ON eel.slot_id    = ps.slot_id
LEFT JOIN payments p   ON eel.log_id     = p.log_id;


-- ----------------------------------------------------------------------------
-- 2. view_logs_today_stats
--    Stat card data: today's entries, exits, revenue.
--    Used by: get_logs_stats.php
--
--    NOTE: "Denied" entries are not tracked in the current schema.
--    The entry_exit_logs.log_status ENUM only supports 'in' and 'out'.
--    To track denied entries, run:
--      ALTER TABLE entry_exit_logs
--      MODIFY COLUMN log_status ENUM('in','out','denied') NOT NULL DEFAULT 'in';
--    Then this view will automatically include denied counts.
-- ----------------------------------------------------------------------------
CREATE OR REPLACE VIEW view_logs_today_stats AS
SELECT
    -- Entries today (vehicles that came in)
    COUNT(CASE WHEN log_status IN ('in', 'out') THEN 1 END)  AS today_entries,

    -- Exits today (vehicles that have checked out)
    COUNT(CASE WHEN log_status = 'out' THEN 1 END)           AS today_exits,

    -- Denied today (will be 0 until ENUM is altered)
    COUNT(CASE WHEN log_status = 'denied' THEN 1 END)        AS today_denied,

    -- Revenue collected today (only from completed sessions)
    COALESCE(SUM(
        CASE WHEN log_status = 'out'
             THEN parking_fee ELSE 0 END
    ), 0)                                                     AS today_revenue

FROM entry_exit_logs
WHERE DATE(time_in) = CURDATE();

-- ============================================================================
-- SECTION 3. TRIGGERS
-- ============================================================================

-- ============================================================================
-- 3.1 USER TRIGGERS
-- ============================================================================

-- Validate email format before inserting a user
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_validate_email_format
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Invalid email format';
    END IF;
END$$
DELIMITER ;

-- Validate contact number format before inserting a user
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_validate_contact_number
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NOT (NEW.contact_number REGEXP '^[0-9]{10,20}$'
         OR NEW.contact_number REGEXP '^\+[0-9]{10,20}$') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Invalid contact number format. Use 10-20 digits or +country code format';
    END IF;
END$$
DELIMITER ;

-- Auto-generate user_code if not provided
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_generate_user_code
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.user_code IS NULL THEN
        SET NEW.user_code = CONCAT('USR', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0'));
    END IF;
END$$
DELIMITER ;

-- Update last_login timestamp only when a new non-NULL value is explicitly set
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_update_last_login
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NOT NULL AND (
        OLD.last_login IS NULL OR NEW.last_login != OLD.last_login
    ) THEN
        SET NEW.last_login = NOW();
    END IF;
END$$
DELIMITER ;

-- Prevent deletion of users who have active reservations
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_prevent_user_deletion_with_active_reservations
BEFORE DELETE ON users
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM reservations
        WHERE user_id = OLD.user_id
          AND status  = 'active'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Cannot delete user with active reservations';
    END IF;
END$$
DELIMITER ;


-- ============================================================================
-- 3.2 VEHICLE TRIGGERS
-- ============================================================================

-- Prevent duplicate vehicle registrations for the same user
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_prevent_duplicate_vehicles
BEFORE INSERT ON vehicle
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM vehicle
        WHERE user_id      = NEW.user_id
          AND plate_number = NEW.plate_number
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: This vehicle is already registered for this user';
    END IF;
END$$
DELIMITER ;


-- ============================================================================
-- 3.3 RESERVATION TRIGGERS
-- ============================================================================

-- Prevent booking a slot that is not available
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_check_slot_availability_on_reservation
BEFORE INSERT ON reservations
FOR EACH ROW
BEGIN
    DECLARE slot_status VARCHAR(20);
    SELECT status INTO slot_status
    FROM parking_slots
    WHERE slot_id = NEW.slot_id;

    IF slot_status != 'available' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Selected parking slot is not available for reservation';
    END IF;
END$$
DELIMITER ;

-- Mark slot as 'reserved' after a reservation is created
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_update_slot_on_reservation
AFTER INSERT ON reservations
FOR EACH ROW
BEGIN
    UPDATE parking_slots
    SET status = 'reserved'
    WHERE slot_id = NEW.slot_id;
END$$
DELIMITER ;

-- Release slot back to 'available' when reservation is cancelled OR expired
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_release_slot_on_cancelled_reservation
AFTER UPDATE ON reservations
FOR EACH ROW
BEGIN
    IF (NEW.status = 'cancelled' OR NEW.status = 'expired')
       AND OLD.status NOT IN ('cancelled', 'expired') THEN
        UPDATE parking_slots
        SET status = 'available'
        WHERE slot_id = NEW.slot_id
          AND status  = 'reserved';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- 3.3.1 SCHEDULED EVENT — Auto-expire reservations past their expiry time
-- NOTE: Requires MySQL Event Scheduler to be ON.
--       Run: SET GLOBAL event_scheduler = ON;
--       A trigger alone cannot handle time-based expiry reliably since
--       BEFORE UPDATE only fires when a row is explicitly updated.
-- ============================================================================
CREATE EVENT IF NOT EXISTS evt_expire_reservations
ON SCHEDULE EVERY 1 MINUTE
DO
    UPDATE reservations
    SET status = 'expired'
    WHERE status = 'active'
      AND NOW() > reservation_expiry;


-- ============================================================================
-- 3.4 ENTRY / EXIT LOG TRIGGERS
-- ============================================================================

-- Validate: parking fee cannot be negative on insert (NULL is allowed on entry)
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_validate_parking_fee
BEFORE INSERT ON entry_exit_logs
FOR EACH ROW
BEGIN
    IF NEW.parking_fee IS NOT NULL AND NEW.parking_fee < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Parking fee cannot be negative';
    END IF;
END$$
DELIMITER ;

-- Mark slot as 'occupied' when a vehicle enters
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_update_slot_on_entry
AFTER INSERT ON entry_exit_logs
FOR EACH ROW
BEGIN
    IF NEW.log_status = 'in' THEN
        UPDATE parking_slots
        SET status = 'occupied'
        WHERE slot_id = NEW.slot_id;
    END IF;
END$$
DELIMITER ;

-- Calculate duration and fee when a vehicle exits (BEFORE UPDATE)
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_calculate_parking_fee
BEFORE UPDATE ON entry_exit_logs
FOR EACH ROW
BEGIN
    IF NEW.log_status = 'out' AND OLD.log_status = 'in' THEN
        -- Duration in hours (decimal)
        SET NEW.total_duration = TIMESTAMPDIFF(MINUTE, OLD.time_in, NEW.time_out) / 60.0;
        -- Fee: ₱50 per hour, rounded to 2 decimal places
        SET NEW.parking_fee    = ROUND(NEW.total_duration * 50, 2);
    END IF;
END$$
DELIMITER ;

-- Release slot back to 'available' after vehicle exits (AFTER UPDATE)
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_update_slot_on_exit
AFTER UPDATE ON entry_exit_logs
FOR EACH ROW
BEGIN
    IF NEW.log_status = 'out' AND OLD.log_status = 'in' THEN
        UPDATE parking_slots
        SET status = 'available'
        WHERE slot_id = NEW.slot_id;
    END IF;
END$$
DELIMITER ;


-- ============================================================================
-- 3.5 PAYMENT TRIGGERS
-- ============================================================================

-- Audit log: record payment creation in a dedicated audit table
-- NOTE: Create the audit table first before enabling this trigger.
-- ============================================================================

CREATE TABLE IF NOT EXISTS audit_payment_log (
    audit_id     INT AUTO_INCREMENT PRIMARY KEY,
    log_id       INT           NOT NULL,
    payment_id   INT           NOT NULL,
    amount       DECIMAL(10,2) NOT NULL,
    method       VARCHAR(50)   NOT NULL,
    recorded_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS trg_log_payment_creation
AFTER INSERT ON payments
FOR EACH ROW
BEGIN
    INSERT INTO audit_payment_log (log_id, payment_id, amount, method, recorded_at)
    VALUES (NEW.log_id, NEW.payment_id, NEW.amount, NEW.method, NOW());
END$$
DELIMITER ;


-- ============================================================================
-- SECTION 5. STORED PROCEDURES
-- ============================================================================

-- ============================================================================
-- USER MANAGEMENT
-- ============================================================================

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_register_user (
    IN  p_firstname       VARCHAR(50),
    IN  p_lastname        VARCHAR(50),
    IN  p_email           VARCHAR(100),
    IN  p_contact_number  VARCHAR(20),
    IN  p_role            ENUM('customer','staff','admin'),
    IN  p_password_hash   VARCHAR(255),
    IN  p_birthdate       DATE,
    IN  p_gender          VARCHAR(50),
    OUT p_user_id         INT,
    OUT p_message         VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_user_id = NULL;
        SET p_message = 'ERROR: Registration failed due to a database error.';
    END;

    -- Check for duplicate email
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SET p_user_id = NULL;
        SET p_message = 'ERROR: Email address is already registered.';
    ELSE
        START TRANSACTION;

        INSERT INTO users (
            firstname, lastname, email, contact_number,
            role, password_hash, birthdate, gender
        ) VALUES (
            p_firstname, p_lastname, p_email, p_contact_number,
            p_role, p_password_hash, p_birthdate, p_gender
        );

        SET p_user_id = LAST_INSERT_ID();
        SET p_message = 'SUCCESS: User registered successfully.';

        COMMIT;
    END IF;
END$$
DELIMITER ;

-- ----------------------------------------------------------------------------
-- sp_update_user_full
-- Updates all editable admin-facing fields for a user, including email,
-- role, and optionally password if a new hash is provided.
-- ----------------------------------------------------------------------------
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_update_user_full (
    IN  p_user_id       INT,
    IN  p_firstname     VARCHAR(50),
    IN  p_lastname      VARCHAR(50),
    IN  p_email         VARCHAR(100),
    IN  p_contact       VARCHAR(20),
    IN  p_role          ENUM('customer','staff','admin'),
    IN  p_password_hash VARCHAR(255),   -- pass NULL to keep existing password
    OUT p_message       VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Update failed due to a database error.';
    END;

    -- User must exist
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    -- Email must not be taken by another user
    ELSEIF EXISTS (
        SELECT 1 FROM users
        WHERE email = p_email AND user_id != p_user_id
    ) THEN
        SET p_message = 'ERROR: Email address is already in use by another account.';

    ELSE
        START TRANSACTION;

        IF p_password_hash IS NOT NULL THEN
            UPDATE users
            SET
                firstname      = p_firstname,
                lastname       = p_lastname,
                email          = p_email,
                contact_number = p_contact,
                role           = p_role,
                password_hash  = p_password_hash
            WHERE user_id = p_user_id;
        ELSE
            UPDATE users
            SET
                firstname      = p_firstname,
                lastname       = p_lastname,
                email          = p_email,
                contact_number = p_contact,
                role           = p_role
            WHERE user_id = p_user_id;
        END IF;

        COMMIT;
        SET p_message = 'SUCCESS: User updated successfully.';
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_update_user_status (
    IN  p_user_id  INT,
    IN  p_status   ENUM('active','suspended'),
    OUT p_message  VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Status update failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    ELSEIF p_status = 'suspended' AND fn_user_has_active_reservation(p_user_id) = 1 THEN
        SET p_message = 'ERROR: Cannot suspend user with active reservations.';

    ELSE
        START TRANSACTION;
        UPDATE users SET status = p_status WHERE user_id = p_user_id;
        COMMIT;
        SET p_message = CONCAT('SUCCESS: User status updated to ', p_status, '.');
    END IF;
END$$
DELIMITER ;

CALL sp_update_user_status(1, 'active', @msg);
SELECT @msg;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_delete_user (
    IN  p_user_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: User deletion failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    ELSEIF fn_user_has_active_reservation(p_user_id) = 1 THEN
        SET p_message = 'ERROR: Cannot delete user with active reservations.';

    ELSE
        START TRANSACTION;
        DELETE FROM users WHERE user_id = p_user_id;
        COMMIT;
        SET p_message = 'SUCCESS: User deleted successfully.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- VEHICLE MANAGEMENT
-- ============================================================================
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_register_vehicle (
    IN  p_user_id      INT,
    IN  p_plate_number VARCHAR(20),
    IN  p_vehicle_type VARCHAR(50),
    OUT p_vehicle_id   INT,
    OUT p_message      VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: Vehicle registration failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: User not found.';

    ELSEIF EXISTS (
        SELECT 1 FROM vehicle
        WHERE user_id = p_user_id AND plate_number = p_plate_number
    ) THEN
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: This plate number is already registered for this user.';

    ELSE
        START TRANSACTION;
        INSERT INTO vehicle (user_id, plate_number, vehicle_type)
        VALUES (p_user_id, p_plate_number, p_vehicle_type);
        SET p_vehicle_id = LAST_INSERT_ID();
        COMMIT;
        SET p_message = 'SUCCESS: Vehicle registered successfully.';
    END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_remove_vehicle (
    IN  p_vehicle_id INT,
    OUT p_message    VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Vehicle removal failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM vehicle WHERE vehicle_id = p_vehicle_id) THEN
        SET p_message = 'ERROR: Vehicle not found.';

    ELSEIF EXISTS (
        SELECT 1 FROM entry_exit_logs
        WHERE vehicle_id = p_vehicle_id AND log_status = 'in'
    ) THEN
        SET p_message = 'ERROR: Cannot remove a vehicle that is currently parked.';

    ELSE
        START TRANSACTION;
        DELETE FROM vehicle WHERE vehicle_id = p_vehicle_id;
        COMMIT;
        SET p_message = 'SUCCESS: Vehicle removed successfully.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- RESERVATIONS
-- ============================================================================


DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_cancel_reservation (
    IN  p_reservation_id INT,
    IN  p_user_id        INT,
    OUT p_message        VARCHAR(255)
)
BEGIN
    DECLARE v_status  VARCHAR(20);
    DECLARE v_user_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Cancellation failed.';
    END;

    SELECT status, user_id
    INTO   v_status, v_user_id
    FROM   reservations
    WHERE  reservation_id = p_reservation_id;

    IF v_status IS NULL THEN
        SET p_message = 'ERROR: Reservation not found.';

    ELSEIF v_user_id != p_user_id THEN
        SET p_message = 'ERROR: You are not authorized to cancel this reservation.';

    ELSEIF v_status != 'active' THEN
        SET p_message = CONCAT('ERROR: Reservation cannot be cancelled. Current status: ', v_status, '.');

    ELSE
        START TRANSACTION;
        UPDATE reservations
        SET    status = 'cancelled'
        WHERE  reservation_id = p_reservation_id;
        COMMIT;
        -- Slot is released by trg_release_slot_on_cancelled_reservation
        SET p_message = 'SUCCESS: Reservation cancelled successfully.';
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- REPORTS AND ANALYTICS
-- ============================================================================

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_get_revenue_summary (
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    SELECT
        COUNT(DISTINCT p.payment_id)                AS total_transactions,
        COUNT(DISTINCT eel.log_id)                  AS total_sessions,
        COUNT(DISTINCT v.user_id)                   AS unique_users,
        COALESCE(SUM(p.amount),         0)          AS total_revenue,
        COALESCE(AVG(p.amount),         0)          AS avg_payment,
        COALESCE(AVG(eel.total_duration), 0)        AS avg_duration_hours,
        MIN(p.payment_date)                         AS first_transaction,
        MAX(p.payment_date)                         AS last_transaction
    FROM payments p
    JOIN entry_exit_logs eel ON p.log_id      = eel.log_id
    JOIN vehicle         v   ON eel.vehicle_id = v.vehicle_id
    WHERE DATE(p.payment_date) BETWEEN p_date_from AND p_date_to;
END$$
DELIMITER ;

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_get_daily_report (
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    SELECT
        DATE(eel.time_in)               AS report_date,
        COUNT(eel.log_id)               AS total_entries,
        SUM(CASE WHEN eel.log_status = 'out'    THEN 1 ELSE 0 END) AS total_exits,
        SUM(CASE WHEN eel.log_status = 'denied' THEN 1 ELSE 0 END) AS total_denied,
        COUNT(DISTINCT eel.vehicle_id)  AS unique_vehicles,
        COUNT(DISTINCT v.user_id)       AS unique_users,
        COALESCE(SUM(eel.parking_fee),  0) AS total_fees_collected,
        COALESCE(AVG(eel.total_duration), 0) AS avg_duration_hours
    FROM entry_exit_logs eel
    JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
    WHERE DATE(eel.time_in) BETWEEN p_date_from AND p_date_to
    GROUP BY DATE(eel.time_in)
    ORDER BY report_date ASC;
END$$
DELIMITER ;

-- ============================================================================
-- sp_get_vehicle_activity_report
-- 
-- Purpose:
--   Returns comprehensive vehicle activity statistics for a given date range.
--   Includes parking sessions, revenue, duration metrics, and user details.
--
-- Parameters:
--   p_date_from DATE — Start date (inclusive)
--   p_date_to   DATE — End date (inclusive)
--
-- Returns:
--   Rows with vehicle details, parking stats, and financial metrics
--
-- Usage:
--   CALL sp_get_vehicle_activity_report('2026-05-01', '2026-05-31');
--
-- ============================================================================

DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS sp_get_vehicle_activity_report (
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    -- ────────────────────────────────────────────────────────────────────
    -- Validate date inputs
    -- ────────────────────────────────────────────────────────────────────
    IF p_date_from IS NULL OR p_date_to IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Date parameters cannot be NULL';
    END IF;
    
    IF p_date_from > p_date_to THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: date_from must be before or equal to date_to';
    END IF;
    
    -- ────────────────────────────────────────────────────────────────────
    -- Main Query: Vehicle Activity Report
    -- ────────────────────────────────────────────────────────────────────
    SELECT
        -- Vehicle & Owner Info
        v.vehicle_id,
        v.plate_number,
        v.vehicle_type,
        
        -- User Info
        u.user_id,
        u.user_code,
        CONCAT(u.firstname, ' ', u.lastname) AS owner_name,
        u.email,
        u.contact_number,
        u.role AS user_role,
        
        -- Parking Activity
        COUNT(eel.log_id)                           AS total_entries,
        SUM(CASE WHEN eel.log_status = 'out' THEN 1 ELSE 0 END)     AS total_exits,
        SUM(CASE WHEN eel.log_status = 'in' THEN 1 ELSE 0 END)      AS currently_inside,
        
        -- Duration Metrics
        COALESCE(SUM(eel.total_duration), 0)        AS total_hours,
        COALESCE(AVG(eel.total_duration), 0)        AS avg_duration_hours,
        COALESCE(MIN(eel.total_duration), 0)        AS min_duration_hours,
        COALESCE(MAX(eel.total_duration), 0)        AS max_duration_hours,
        
        -- Financial Metrics
        COALESCE(SUM(eel.parking_fee), 0)           AS total_fees_paid,
        COALESCE(AVG(eel.parking_fee), 0)           AS avg_fee_per_session,
        COALESCE(MIN(eel.parking_fee), 0)           AS min_fee_per_session,
        COALESCE(MAX(eel.parking_fee), 0)           AS max_fee_per_session,
        
        -- Reservation Stats
        COUNT(DISTINCT r.reservation_id)            AS total_reservations,
        SUM(CASE WHEN r.status = 'active' THEN 1 ELSE 0 END)       AS active_reservations,
        SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END)    AS completed_reservations,
        SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END)    AS cancelled_reservations,
        
        -- Temporal Info
        MIN(eel.time_in)                            AS first_entry,
        MAX(eel.time_in)                            AS last_entry,
        MAX(eel.time_out)                           AS last_exit,
        
        -- Days active
        COUNT(DISTINCT DATE(eel.time_in))          AS days_active
        
    FROM vehicle v
    JOIN users u ON v.user_id = u.user_id
    LEFT JOIN entry_exit_logs eel 
        ON eel.vehicle_id = v.vehicle_id 
        AND DATE(eel.time_in) BETWEEN p_date_from AND p_date_to
    LEFT JOIN reservations r 
        ON r.user_id = v.user_id 
        AND DATE(r.time_reserved) BETWEEN p_date_from AND p_date_to
    
    GROUP BY 
        v.vehicle_id, 
        v.plate_number, 
        v.vehicle_type,
        u.user_id,
        u.user_code,
        u.firstname,
        u.lastname,
        u.email,
        u.contact_number,
        u.role
    
    ORDER BY 
        total_entries DESC,
        total_fees_paid DESC;

END$$
DELIMITER ;

-- ============================================================================
-- SECTION 6: STORED FUNCTIONS
-- ============================================================================

DELIMITER $$
CREATE FUNCTION IF NOT EXISTS fn_user_has_active_reservation (
    p_user_id INT
)
RETURNS TINYINT(1)
READS SQL DATA
BEGIN
    DECLARE v_count INT DEFAULT 0;

    SELECT COUNT(*) INTO v_count
    FROM   reservations
    WHERE  user_id = p_user_id
      AND  status  = 'active';

    RETURN IF(v_count > 0, 1, 0);
END$$
DELIMITER ;

DELIMITER $$
CREATE FUNCTION IF NOT EXISTS fn_is_slot_available (
    p_slot_id INT
)
RETURNS TINYINT(1)
READS SQL DATA
BEGIN
    DECLARE v_status VARCHAR(20);

    SELECT status INTO v_status
    FROM   parking_slots
    WHERE  slot_id = p_slot_id;

    RETURN IF(v_status = 'available', 1, 0);
END$$
DELIMITER ;

-- ============================================================================
-- SECTION 7. INDEXES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 4.1 users
-- ----------------------------------------------------------------------------
CREATE INDEX idx_users_role        ON users (role);
CREATE INDEX idx_users_status      ON users (status);
CREATE INDEX idx_users_last_login  ON users (last_login);
CREATE INDEX idx_users_created_at  ON users (created_at);

-- ----------------------------------------------------------------------------
-- 4.2 vehicle
-- ----------------------------------------------------------------------------
CREATE INDEX idx_vehicle_user_id          ON vehicle (user_id);
CREATE INDEX idx_vehicle_plate_number     ON vehicle (plate_number);
CREATE INDEX idx_vehicle_type             ON vehicle (vehicle_type);
CREATE UNIQUE INDEX idx_vehicle_user_plate ON vehicle (user_id, plate_number);

-- ----------------------------------------------------------------------------
-- 4.3 parking_slots
-- ----------------------------------------------------------------------------
CREATE INDEX idx_slots_status        ON parking_slots (status);
CREATE INDEX idx_slots_location_area ON parking_slots (location_area);
CREATE INDEX idx_slots_area_status   ON parking_slots (location_area, status);

-- ----------------------------------------------------------------------------
-- 4.4 reservations
-- ----------------------------------------------------------------------------
CREATE INDEX idx_reservations_user_id       ON reservations (user_id);
CREATE INDEX idx_reservations_slot_id       ON reservations (slot_id);
CREATE INDEX idx_reservations_status        ON reservations (status);
CREATE INDEX idx_reservations_time_reserved ON reservations (time_reserved);
CREATE INDEX idx_reservations_expiry        ON reservations (reservation_expiry);
CREATE INDEX idx_reservations_status_expiry ON reservations (status, reservation_expiry);

-- ----------------------------------------------------------------------------
-- 4.5 entry_exit_logs
-- ----------------------------------------------------------------------------
CREATE INDEX idx_logs_vehicle_id     ON entry_exit_logs (vehicle_id);
CREATE INDEX idx_logs_slot_id        ON entry_exit_logs (slot_id);
CREATE INDEX idx_logs_reservation_id ON entry_exit_logs (reservation_id);
CREATE INDEX idx_logs_log_status     ON entry_exit_logs (log_status);
CREATE INDEX idx_logs_time_in        ON entry_exit_logs (time_in);
CREATE INDEX idx_logs_time_out       ON entry_exit_logs (time_out);
CREATE INDEX idx_logs_status_time_in ON entry_exit_logs (log_status, time_in);

-- ----------------------------------------------------------------------------
-- 4.6 payments
-- ----------------------------------------------------------------------------
CREATE INDEX idx_payments_log_id       ON payments (log_id);
CREATE INDEX idx_payments_payment_date ON payments (payment_date);
CREATE INDEX idx_payments_method       ON payments (method);
CREATE INDEX idx_payments_date_method  ON payments (payment_date, method);

-- ----------------------------------------------------------------------------
-- 4.7 audit_payment_log
-- ----------------------------------------------------------------------------
CREATE INDEX idx_audit_log_id      ON audit_payment_log (log_id);
CREATE INDEX idx_audit_payment_id  ON audit_payment_log (payment_id);
CREATE INDEX idx_audit_recorded_at ON audit_payment_log (recorded_at);

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================


