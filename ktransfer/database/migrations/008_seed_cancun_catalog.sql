-- Migración 008: Seed base Cancún (catálogos y tarifas iniciales)
-- Objetivo: dejar un entorno funcional desde el día 1 para demo/operación inicial.

-- Service types base
INSERT INTO service_types (code, name_es, name_en, is_active, sort_order, created_at) VALUES
('REGULAR', 'Regular', 'Regular', 1, 10, NOW()),
('VIP', 'VIP', 'VIP', 1, 20, NOW()),
('LUXURY', 'Luxury', 'Luxury', 1, 30, NOW())
ON DUPLICATE KEY UPDATE
  name_es = VALUES(name_es),
  name_en = VALUES(name_en),
  is_active = VALUES(is_active),
  sort_order = VALUES(sort_order);

-- Vehicles base
INSERT INTO vehicles (code, name, max_pax, is_active, created_at) VALUES
('SEDAN_1_3', 'Sedan 1-3 pax', 3, 1, NOW()),
('SUV_1_5', 'SUV 1-5 pax', 5, 1, NOW()),
('VAN_1_8', 'Van 1-8 pax', 8, 1, NOW()),
('SPRINTER_1_16', 'Sprinter 1-16 pax', 16, 1, NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  max_pax = VALUES(max_pax),
  is_active = VALUES(is_active);

-- Pax ranges base
INSERT INTO pax_ranges (label, min_pax, max_pax, sort_order) VALUES
('1-3', 1, 3, 10),
('4-5', 4, 5, 20),
('6-8', 6, 8, 30),
('9-12', 9, 12, 40),
('13-16', 13, 16, 50)
ON DUPLICATE KEY UPDATE
  label = VALUES(label),
  sort_order = VALUES(sort_order);

-- Zonas base Cancún / Riviera Maya
INSERT INTO zones (code, name_es, name_en, is_active, sort_order, created_at) VALUES
('CUN_HOTEL_ZONE', 'Zona Hotelera Cancún', 'Cancun Hotel Zone', 1, 10, NOW()),
('CUN_DOWNTOWN', 'Centro Cancún', 'Cancun Downtown', 1, 20, NOW()),
('PUERTO_MORELOS', 'Puerto Morelos', 'Puerto Morelos', 1, 30, NOW()),
('PLAYA_DEL_CARMEN', 'Playa del Carmen', 'Playa del Carmen', 1, 40, NOW()),
('TULUM', 'Tulum', 'Tulum', 1, 50, NOW())
ON DUPLICATE KEY UPDATE
  name_es = VALUES(name_es),
  name_en = VALUES(name_en),
  is_active = VALUES(is_active),
  sort_order = VALUES(sort_order);

-- Places base (evitar duplicados por nombre dentro de la zona)
INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Grand Oasis Cancun', 'Cancún', 1, NOW()
FROM zones z
WHERE z.code = 'CUN_HOTEL_ZONE'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Grand Oasis Cancun'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'JW Marriott Cancun Resort & Spa', 'Cancún', 1, NOW()
FROM zones z
WHERE z.code = 'CUN_HOTEL_ZONE'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'JW Marriott Cancun Resort & Spa'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Smart Cancun by Oasis', 'Cancún', 1, NOW()
FROM zones z
WHERE z.code = 'CUN_DOWNTOWN'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Smart Cancun by Oasis'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'POINT', 'ADO Cancún Centro', 'Cancún', 1, NOW()
FROM zones z
WHERE z.code = 'CUN_DOWNTOWN'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'ADO Cancún Centro'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Dreams Sapphire Resort & Spa', 'Puerto Morelos', 1, NOW()
FROM zones z
WHERE z.code = 'PUERTO_MORELOS'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Dreams Sapphire Resort & Spa'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Hyatt Ziva Riviera Cancun', 'Puerto Morelos', 1, NOW()
FROM zones z
WHERE z.code = 'PUERTO_MORELOS'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Hyatt Ziva Riviera Cancun'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Hilton Playa del Carmen', 'Playa del Carmen', 1, NOW()
FROM zones z
WHERE z.code = 'PLAYA_DEL_CARMEN'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Hilton Playa del Carmen'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'POINT', 'Quinta Avenida', 'Playa del Carmen', 1, NOW()
FROM zones z
WHERE z.code = 'PLAYA_DEL_CARMEN'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Quinta Avenida'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'HOTEL', 'Hilton Tulum Riviera Maya', 'Tulum', 1, NOW()
FROM zones z
WHERE z.code = 'TULUM'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Hilton Tulum Riviera Maya'
  );

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, 'POINT', 'Zona Arqueológica de Tulum', 'Tulum', 1, NOW()
FROM zones z
WHERE z.code = 'TULUM'
  AND NOT EXISTS (
    SELECT 1 FROM places p WHERE p.zone_id = z.id AND p.name = 'Zona Arqueológica de Tulum'
  );

-- Tarifas base en USD para REGULAR y VIP
INSERT INTO rate_rules (
  zone_id,
  service_type_id,
  pax_range_id,
  vehicle_id,
  currency_code,
  one_way_price,
  round_trip_price,
  is_active,
  created_at
)
SELECT
  z.id,
  st.id,
  pr.id,
  NULL,
  'USD',
  ROUND(
    (
      CASE z.code
        WHEN 'CUN_HOTEL_ZONE' THEN 45
        WHEN 'CUN_DOWNTOWN' THEN 55
        WHEN 'PUERTO_MORELOS' THEN 75
        WHEN 'PLAYA_DEL_CARMEN' THEN 95
        WHEN 'TULUM' THEN 150
        ELSE 60
      END
      +
      CASE
        WHEN pr.min_pax = 1 THEN 0
        WHEN pr.min_pax = 4 THEN 15
        WHEN pr.min_pax = 6 THEN 30
        WHEN pr.min_pax = 9 THEN 55
        WHEN pr.min_pax = 13 THEN 90
        ELSE 0
      END
    )
    *
    CASE st.code
      WHEN 'REGULAR' THEN 1.00
      WHEN 'VIP' THEN 1.35
      ELSE 1.00
    END
  , 2) AS one_way_price,
  ROUND(
    (
      (
        CASE z.code
          WHEN 'CUN_HOTEL_ZONE' THEN 45
          WHEN 'CUN_DOWNTOWN' THEN 55
          WHEN 'PUERTO_MORELOS' THEN 75
          WHEN 'PLAYA_DEL_CARMEN' THEN 95
          WHEN 'TULUM' THEN 150
          ELSE 60
        END
        +
        CASE
          WHEN pr.min_pax = 1 THEN 0
          WHEN pr.min_pax = 4 THEN 15
          WHEN pr.min_pax = 6 THEN 30
          WHEN pr.min_pax = 9 THEN 55
          WHEN pr.min_pax = 13 THEN 90
          ELSE 0
        END
      )
      *
      CASE st.code
        WHEN 'REGULAR' THEN 1.00
        WHEN 'VIP' THEN 1.35
        ELSE 1.00
      END
    ) * 1.85
  , 2) AS round_trip_price,
  1,
  NOW()
FROM zones z
INNER JOIN pax_ranges pr ON pr.min_pax IN (1, 4, 6, 9, 13)
INNER JOIN service_types st ON st.code IN ('REGULAR', 'VIP')
LEFT JOIN rate_rules rr
  ON rr.zone_id = z.id
 AND rr.service_type_id = st.id
 AND rr.pax_range_id = pr.id
 AND rr.currency_code = 'USD'
WHERE z.code IN ('CUN_HOTEL_ZONE', 'CUN_DOWNTOWN', 'PUERTO_MORELOS', 'PLAYA_DEL_CARMEN', 'TULUM')
  AND rr.id IS NULL;

-- Tarifas espejo en MXN (tipo de cambio fijo inicial 17.00)
INSERT INTO rate_rules (
  zone_id,
  service_type_id,
  pax_range_id,
  vehicle_id,
  currency_code,
  one_way_price,
  round_trip_price,
  is_active,
  created_at
)
SELECT
  rr_usd.zone_id,
  rr_usd.service_type_id,
  rr_usd.pax_range_id,
  rr_usd.vehicle_id,
  'MXN',
  ROUND(rr_usd.one_way_price * 17.00, 2),
  ROUND(rr_usd.round_trip_price * 17.00, 2),
  rr_usd.is_active,
  NOW()
FROM rate_rules rr_usd
INNER JOIN zones z ON z.id = rr_usd.zone_id
LEFT JOIN rate_rules rr_mxn
  ON rr_mxn.zone_id = rr_usd.zone_id
 AND rr_mxn.service_type_id = rr_usd.service_type_id
 AND rr_mxn.pax_range_id = rr_usd.pax_range_id
 AND rr_mxn.currency_code = 'MXN'
WHERE rr_usd.currency_code = 'USD'
  AND z.code IN ('CUN_HOTEL_ZONE', 'CUN_DOWNTOWN', 'PUERTO_MORELOS', 'PLAYA_DEL_CARMEN', 'TULUM')
  AND rr_mxn.id IS NULL;
