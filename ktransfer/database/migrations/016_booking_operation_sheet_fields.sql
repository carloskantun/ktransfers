ALTER TABLE bookings
  ADD COLUMN origin_name VARCHAR(190) NULL AFTER place_id,
  ADD COLUMN destination_name VARCHAR(190) NULL AFTER origin_name,
  ADD COLUMN terminal VARCHAR(60) NULL AFTER flight_number,
  ADD COLUMN agency_name VARCHAR(190) NULL AFTER customer_phone;
