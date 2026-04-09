-- Migración 014: Importar places desde tabla legacy fzn3_hotels (si existe)
-- Objetivo: poblar catálogo de places con cobertura amplia del inventario histórico.

SET @legacy_hotels_table_exists := (
  SELECT COUNT(*)
  FROM information_schema.tables
  WHERE table_schema = DATABASE()
    AND table_name = 'fzn3_hotels'
);

SET @ensure_zones_sql := IF(
  @legacy_hotels_table_exists > 0,
  "INSERT INTO zones (code, name_es, name_en, is_active, sort_order, created_at)
   SELECT m.zone_code, m.name_es, m.name_en, 1, m.sort_order, NOW()
   FROM (
     SELECT 'CUN_HOTEL_ZONE' AS zone_code, 'Zona Hotelera Cancún' AS name_es, 'Cancun Hotel Zone' AS name_en, 10 AS sort_order
     UNION ALL SELECT 'PUERTO_MORELOS', 'Puerto Morelos', 'Puerto Morelos', 30
     UNION ALL SELECT 'MAROMA', 'Maroma', 'Maroma', 90
     UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'Playa del Carmen', 'Playa del Carmen', 40
     UNION ALL SELECT 'PUERTO_AVENTURAS', 'Puerto Aventuras', 'Puerto Aventuras', 70
     UNION ALL SELECT 'AKUMAL', 'Akumal', 'Akumal', 80
     UNION ALL SELECT 'TULUM', 'Tulum', 'Tulum', 50
     UNION ALL SELECT 'PLAYA_MUJERES', 'Playa Mujeres', 'Playa Mujeres', 60
     UNION ALL SELECT 'PUNTA_ALLEN', 'Punta Allen', 'Punta Allen', 100
     UNION ALL SELECT 'HOLBOX', 'Holbox', 'Holbox', 110
     UNION ALL SELECT 'VALLADOLID', 'Valladolid', 'Valladolid', 120
     UNION ALL SELECT 'CHICHEN_ITZA', 'Chichén Itzá', 'Chichen Itza', 130
     UNION ALL SELECT 'MERIDA', 'Mérida', 'Merida', 140
     UNION ALL SELECT 'PUERTO_JUAREZ', 'Puerto Juárez', 'Puerto Juarez', 150
    UNION ALL SELECT 'SIAN_KAAN', 'Sian Kaan', 'Sian Kaan', 160
   ) AS m
   ON DUPLICATE KEY UPDATE
     name_es = VALUES(name_es),
     name_en = VALUES(name_en),
     is_active = VALUES(is_active),
     sort_order = VALUES(sort_order)",
  "SELECT 1"
);

PREPARE ensure_zones_stmt FROM @ensure_zones_sql;
EXECUTE ensure_zones_stmt;
DEALLOCATE PREPARE ensure_zones_stmt;

SET @import_places_sql := IF(
  @legacy_hotels_table_exists > 0,
  "INSERT INTO places (zone_id, type, name, city, is_active, created_at)
   SELECT
     z.id,
     CASE
       WHEN UPPER(TRIM(h.name_hotel)) REGEXP 'MUELLE|FERRY|AEROPUERTO|CENTRO DE VISITANTES|VISITORS CENTER|\(CUN\)' THEN 'POINT'
       ELSE 'HOTEL'
     END AS place_type,
     TRIM(h.name_hotel) AS place_name,
     m.city,
     CASE
       WHEN COALESCE(h.status_hotel, 1) = 1 AND COALESCE(h.list_hotel, 1) = 1 THEN 1
       ELSE 0
     END AS is_active,
     NOW()
   FROM fzn3_hotels h
   INNER JOIN (
     SELECT 1 AS legacy_zone_id, 'CUN_HOTEL_ZONE' AS zone_code, 'Cancún' AS city
     UNION ALL SELECT 2, 'PUERTO_MORELOS', 'Puerto Morelos'
     UNION ALL SELECT 3, 'MAROMA', 'Maroma'
     UNION ALL SELECT 4, 'PLAYA_DEL_CARMEN', 'Playa del Carmen'
     UNION ALL SELECT 5, 'PUERTO_AVENTURAS', 'Puerto Aventuras'
     UNION ALL SELECT 7, 'AKUMAL', 'Akumal'
     UNION ALL SELECT 8, 'TULUM', 'Tulum'
     UNION ALL SELECT 9, 'PUNTA_ALLEN', 'Punta Allen'
     UNION ALL SELECT 10, 'HOLBOX', 'Holbox'
     UNION ALL SELECT 11, 'VALLADOLID', 'Valladolid'
     UNION ALL SELECT 12, 'CHICHEN_ITZA', 'Chichén Itzá'
     UNION ALL SELECT 13, 'MERIDA', 'Mérida'
     UNION ALL SELECT 14, 'PUERTO_JUAREZ', 'Cancún'
     UNION ALL SELECT 15, 'PLAYA_MUJERES', 'Cancún'
     UNION ALL SELECT 16, 'SIAN_KAAN', 'Sian Kaan'
   ) AS m ON m.legacy_zone_id = h.id_zone_hotel
   INNER JOIN zones z ON z.code = m.zone_code
   LEFT JOIN places p
     ON p.zone_id = z.id
    AND LOWER(TRIM(p.name)) = LOWER(TRIM(h.name_hotel))
   WHERE TRIM(COALESCE(h.name_hotel, '')) <> ''
     AND p.id IS NULL",
  "SELECT 1"
);

PREPARE import_places_stmt FROM @import_places_sql;
EXECUTE import_places_stmt;
DEALLOCATE PREPARE import_places_stmt;
