-- Migración 013: Completar rate_rules faltantes (LUXURY + gaps por zona)

-- 1) Generar tarifas LUXURY derivadas de VIP cuando no existan
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
  rr_vip.zone_id,
  st_lux.id,
  rr_vip.pax_range_id,
  rr_vip.vehicle_id,
  rr_vip.currency_code,
  ROUND(rr_vip.one_way_price * 1.25, 2),
  ROUND(rr_vip.round_trip_price * 1.25, 2),
  1,
  NOW()
FROM rate_rules rr_vip
INNER JOIN service_types st_vip ON st_vip.id = rr_vip.service_type_id AND st_vip.code = 'VIP'
INNER JOIN service_types st_lux ON st_lux.code = 'LUXURY'
LEFT JOIN rate_rules rr_lux
  ON rr_lux.zone_id = rr_vip.zone_id
 AND rr_lux.service_type_id = st_lux.id
 AND rr_lux.pax_range_id = rr_vip.pax_range_id
 AND rr_lux.currency_code = rr_vip.currency_code
WHERE rr_lux.id IS NULL;

-- 2) Para zonas activas sin ninguna tarifa USD, crear base REGULAR/VIP/LUXURY
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
      (60 + (z.sort_order * 0.8))
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
      WHEN 'LUXURY' THEN 1.70
      ELSE 1.00
    END
  , 2),
  ROUND(
    (
      (
        (60 + (z.sort_order * 0.8))
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
        WHEN 'LUXURY' THEN 1.70
        ELSE 1.00
      END
    ) * 1.85
  , 2),
  1,
  NOW()
FROM zones z
INNER JOIN pax_ranges pr ON pr.min_pax IN (1, 4, 6, 9, 13)
INNER JOIN service_types st ON st.code IN ('REGULAR', 'VIP', 'LUXURY')
LEFT JOIN rate_rules rr
  ON rr.zone_id = z.id
 AND rr.service_type_id = st.id
 AND rr.pax_range_id = pr.id
 AND rr.currency_code = 'USD'
WHERE z.is_active = 1
  AND rr.id IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM rate_rules x
    WHERE x.zone_id = z.id
      AND x.currency_code = 'USD'
  );

-- 3) Espejo MXN para reglas USD nuevas que no tengan MXN
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
LEFT JOIN rate_rules rr_mxn
  ON rr_mxn.zone_id = rr_usd.zone_id
 AND rr_mxn.service_type_id = rr_usd.service_type_id
 AND rr_mxn.pax_range_id = rr_usd.pax_range_id
 AND rr_mxn.currency_code = 'MXN'
WHERE rr_usd.currency_code = 'USD'
  AND rr_mxn.id IS NULL;
