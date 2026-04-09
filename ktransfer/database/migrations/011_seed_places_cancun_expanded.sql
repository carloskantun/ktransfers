-- Migración 011: Expansión de places/hoteles (subset curado desde legacy)
-- Objetivo: mejorar experiencia en admin y buscador con inventario inicial más amplio.

INSERT INTO places (zone_id, type, name, city, is_active, created_at)
SELECT z.id, src.place_type, src.place_name, src.city, 1, NOW()
FROM (
  -- CANCUN HOTEL ZONE
  SELECT 'CUN_HOTEL_ZONE' AS zone_code, 'HOTEL' AS place_type, 'Aloft Cancun' AS place_name, 'Cancún' AS city
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Beach Palace', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Dreams Sands Cancun Resort & Spa', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Fiesta Americana Condesa Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Hard Rock Hotel Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Hyatt Regency Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Krystal Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Live Aqua Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Nizuc Resort and Spa', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Paradisus Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Secrets The Vine Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'The Ritz-Carlton Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'The Westin Resort & Spa Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Sun Palace', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Riu Palace Las Americas', 'Cancún'
  UNION ALL SELECT 'CUN_HOTEL_ZONE', 'HOTEL', 'Riu Palace Peninsula', 'Cancún'

  -- CANCUN DOWNTOWN / AEROPUERTO
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Adhara Hacienda Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Ambiance Suites', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'City Express Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Fiesta Inn Cancun Las Americas', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'Krystal Urban Cancun', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'HOTEL', 'One Cancun Centro', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'POINT', 'Aeropuerto Internacional de Cancún (CUN)', 'Cancún'
  UNION ALL SELECT 'CUN_DOWNTOWN', 'POINT', 'Puerto Juárez Ultramar', 'Cancún'

  -- PLAYA MUJERES
  UNION ALL SELECT 'PLAYA_MUJERES', 'HOTEL', 'Secrets Playa Mujeres Golf & Spa Resort', 'Cancún'
  UNION ALL SELECT 'PLAYA_MUJERES', 'HOTEL', 'Villa del Palmar Playa Mujeres', 'Cancún'

  -- PUERTO MORELOS
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Now Jade Riviera Cancun', 'Puerto Morelos'
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Now Sapphire Riviera Cancun', 'Puerto Morelos'
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Royalton Riviera Cancun Resort & Spa', 'Puerto Morelos'
  UNION ALL SELECT 'PUERTO_MORELOS', 'HOTEL', 'Zoetry Paraiso de la Bonita', 'Puerto Morelos'

  -- PLAYA DEL CARMEN
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Fairmont Mayakoba', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Grand Velas Riviera Maya', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Hilton Playa del Carmen', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Mahekal Beach Resort', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Playacar Palace', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Riu Palace Riviera Maya', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Riu Palace Mexico', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Riu Yucatan', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'Sandos Playacar Beach Resort', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'HOTEL', 'The Reef Playacar', 'Playa del Carmen'
  UNION ALL SELECT 'PLAYA_DEL_CARMEN', 'POINT', 'Quinta Avenida Playa del Carmen', 'Playa del Carmen'

  -- MAROMA
  UNION ALL SELECT 'MAROMA', 'HOTEL', 'Catalonia Privileged Maroma', 'Maroma'
  UNION ALL SELECT 'MAROMA', 'HOTEL', 'El Dorado Maroma', 'Maroma'

  -- PUERTO AVENTURAS
  UNION ALL SELECT 'PUERTO_AVENTURAS', 'HOTEL', 'Catalonia Yucatan Beach', 'Puerto Aventuras'
  UNION ALL SELECT 'PUERTO_AVENTURAS', 'HOTEL', 'Hard Rock Hotel Riviera Maya', 'Puerto Aventuras'

  -- AKUMAL
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Akumal Bay Beach & Wellness Resort', 'Akumal'
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Bahia Principe Coba', 'Akumal'
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Bahia Principe Sian Kaan', 'Akumal'
  UNION ALL SELECT 'AKUMAL', 'HOTEL', 'Grand Sirenis Riviera Maya', 'Akumal'

  -- TULUM
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Ahau Tulum', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Ana y Jose Charming Hotel & Spa', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Casa Malca', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Nomade Tulum', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Papaya Playa Project', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Sanara Tulum', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'The Beach Tulum', 'Tulum'
  UNION ALL SELECT 'TULUM', 'HOTEL', 'Zamas Hotel', 'Tulum'
  UNION ALL SELECT 'TULUM', 'POINT', 'Zona Arqueológica de Tulum', 'Tulum'
) AS src
INNER JOIN zones z ON z.code = src.zone_code
LEFT JOIN places p ON p.zone_id = z.id AND p.name = src.place_name
WHERE p.id IS NULL;
