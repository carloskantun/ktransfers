-- Migración 009: Expansión de seed Cancún desde fuente legacy (curada)
-- Basada en catálogo histórico, depurando códigos inválidos/ruido.

-- Aerolíneas adicionales curadas (códigos IATA/operador comunes)
INSERT INTO airlines (code, name, is_active, created_at) VALUES
('AF', 'Air France', 1, NOW()),
('AR', 'Aerolíneas Argentinas', 1, NOW()),
('AS', 'Alaska Airlines', 1, NOW()),
('AV', 'Avianca', 1, NOW()),
('BA', 'British Airways', 1, NOW()),
('B6', 'JetBlue Airways', 1, NOW()),
('CM', 'Copa Airlines', 1, NOW()),
('DE', 'Condor', 1, NOW()),
('EK', 'Emirates', 1, NOW()),
('EW', 'Eurowings', 1, NOW()),
('EY', 'Etihad Airways', 1, NOW()),
('G3', 'GOL Linhas Aéreas', 1, NOW()),
('G4', 'Allegiant Air', 1, NOW()),
('IB', 'Iberia', 1, NOW()),
('JL', 'Japan Airlines', 1, NOW()),
('KL', 'KLM', 1, NOW()),
('LH', 'Lufthansa', 1, NOW()),
('LX', 'Swiss International Air Lines', 1, NOW()),
('NH', 'ANA', 1, NOW()),
('NZ', 'Air New Zealand', 1, NOW()),
('QF', 'Qantas', 1, NOW()),
('SY', 'Sun Country Airlines', 1, NOW()),
('TK', 'Turkish Airlines', 1, NOW()),
('TS', 'Air Transat', 1, NOW()),
('UX', 'Air Europa', 1, NOW()),
('VA', 'Virgin Australia', 1, NOW()),
('VS', 'Virgin Atlantic', 1, NOW()),
('WN', 'Southwest Airlines', 1, NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  is_active = VALUES(is_active);

-- Zonas adicionales desde operación histórica
INSERT INTO zones (code, name_es, name_en, is_active, sort_order, created_at) VALUES
('PLAYA_MUJERES', 'Playa Mujeres', 'Playa Mujeres', 1, 60, NOW()),
('PUERTO_AVENTURAS', 'Puerto Aventuras', 'Puerto Aventuras', 1, 70, NOW()),
('AKUMAL', 'Akumal', 'Akumal', 1, 80, NOW()),
('MAROMA', 'Maroma', 'Maroma', 1, 90, NOW())
ON DUPLICATE KEY UPDATE
  name_es = VALUES(name_es),
  name_en = VALUES(name_en),
  is_active = VALUES(is_active),
  sort_order = VALUES(sort_order);

-- Places curados (subset útil y comercial)
INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, src.place_type, src.place_name, src.city, 1, NOW()
FROM (
  SELECT 'CUN_HOTEL_ZONE' AS zone_code, 'HOTEL' AS place_type, 'Hyatt Ziva Cancun' AS place_name, 'Cancún' AS city
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Hyatt Zilara Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Le Blanc Spa Resort Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Moon Palace Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Iberostar Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Riu Cancun', 'Cancún'

  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Four Points by Sheraton Cancun Centro', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Ibis Cancun Centro', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Courtyard by Marriott Cancun Airport', 'Cancún'

  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Dreams Riviera Cancun Resort & Spa', 'Puerto Morelos'
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Excellence Riviera Cancun', 'Puerto Morelos'
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Ocean Coral & Turquesa', 'Puerto Morelos'

  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Grand Hyatt Playa del Carmen Resort', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Paradisus Playa del Carmen', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Playacar Palace', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'POINT', 'Xcaret', 'Playa del Carmen'

  UNION ALL SELECT 'TULUM', 'HOTEL', 'Dreams Tulum Resort & Spa', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Be Tulum Hotel', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Azulik Tulum', 'Tulum'

  UNION ALL SELECT 'PLAYA_MUJERES', 'HOTEL', 'Beloved Playa Mujeres', 'Cancún'
  UNION ALL SELECT 'PLAYA_MUJERES', 'HOTEL', 'Excellence Playa Mujeres', 'Cancún'
  UNION ALL SELECT 'PLAYA_MUJERES', 'HOTEL', 'Finest Playa Mujeres', 'Cancún'

  UNION ALL SELECT 'PUERTO_AVENTURAS', 'HOTEL', 'Dreams Puerto Aventuras Resort & Spa', 'Puerto Aventuras'
  UNION ALL SELECT 'PUERTO_AVENTURAS', 'HOTEL', 'Catalonia Riviera Maya', 'Puerto Aventuras'

  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Secrets Akumal Riviera Maya', 'Akumal'
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Bahia Principe Akumal', 'Akumal'
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Bahia Principe Tulum', 'Akumal'

  UNION ALL SELECT 'MAROMA', 'HOTEL', 'Secrets Maroma Beach Riviera Cancun', 'Maroma'
  UNION ALL SELECT 'MAROMA', 'HOTEL', 'Catalonia Playa Maroma', 'Maroma'
  UNION ALL SELECT 'MAROMA', 'HOTEL', 'Belmond Maroma Resort & Spa', 'Maroma'
) AS src
INNER JOIN zones z ON z.code = src.zone_code
LEFT JOIN places p ON p.zone_id = z.id AND p.name = src.place_name
WHERE p.id IS NULL;

-- Tarifas base para zonas nuevas (USD)
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
        WHEN 'PLAYA_MUJERES' THEN 85
        WHEN 'PUERTO_AVENTURAS' THEN 110
        WHEN 'AKUMAL' THEN 120
        WHEN 'MAROMA' THEN 100
        ELSE 90
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
          WHEN 'PLAYA_MUJERES' THEN 85
          WHEN 'PUERTO_AVENTURAS' THEN 110
          WHEN 'AKUMAL' THEN 120
          WHEN 'MAROMA' THEN 100
          ELSE 90
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
WHERE z.code IN ('PLAYA_MUJERES', 'PUERTO_AVENTURAS', 'AKUMAL', 'MAROMA')
  AND rr.id IS NULL;

-- Tarifas espejo MXN para zonas nuevas
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
  AND z.code IN ('PLAYA_MUJERES', 'PUERTO_AVENTURAS', 'AKUMAL', 'MAROMA')
  AND rr_mxn.id IS NULL;
