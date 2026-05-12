-- Agrega metodo de pago Mercado Pago para checkout online sin Composer.

ALTER TABLE booking_payments
  MODIFY method ENUM('PAYPAL','CARD','BANK','CASH','MANUAL','MERCADO_PAGO') NOT NULL;
