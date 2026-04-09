<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\DB;
use PDO;

class RateService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::connection();
    }

    public function quote(
        int $placeId,
        int $adults,
        int $children,
        string $currencyCode,
        string $tripType
    ): array {
        $totalPax = $adults + $children;
        if ($totalPax <= 0) {
            return [
                'zone_id' => null,
                'pax_range_id' => null,
                'pax_label' => null,
                'options' => [],
            ];
        }

        $zoneId = $this->resolveZoneId($placeId);
        if ($zoneId === null) {
            return [
                'zone_id' => null,
                'pax_range_id' => null,
                'pax_label' => null,
                'options' => [],
            ];
        }

        $paxRange = $this->resolvePaxRange($totalPax);
        if ($paxRange === null) {
            return [
                'zone_id' => $zoneId,
                'pax_range_id' => null,
                'pax_label' => null,
                'options' => [],
            ];
        }

        $priceColumn = $tripType === 'ROUND_TRIP' ? 'rr.round_trip_price' : 'rr.one_way_price';

        $sql = "
            SELECT
                rr.id,
                rr.service_type_id,
                st.name_es AS service_type_name,
                rr.pax_range_id,
                pr.label AS pax_label,
                rr.currency_code,
                {$priceColumn} AS quoted_price
            FROM rate_rules rr
            INNER JOIN service_types st ON st.id = rr.service_type_id
            INNER JOIN pax_ranges pr ON pr.id = rr.pax_range_id
            WHERE rr.zone_id = :zone_id
              AND rr.pax_range_id = :pax_range_id
              AND rr.currency_code = :currency_code
              AND rr.is_active = 1
              AND st.is_active = 1
            ORDER BY st.sort_order ASC, st.id ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'zone_id' => $zoneId,
            'pax_range_id' => $paxRange['id'],
            'currency_code' => strtoupper($currencyCode),
        ]);

        $rows = $stmt->fetchAll();
        $options = [];

        foreach ($rows as $row) {
            $options[] = [
                'rate_rule_id' => (int) $row['id'],
                'service_type_id' => (int) $row['service_type_id'],
                'service_type_name' => (string) $row['service_type_name'],
                'pax_range_id' => (int) $row['pax_range_id'],
                'pax_label' => (string) $row['pax_label'],
                'currency_code' => (string) $row['currency_code'],
                'quoted_price' => (float) $row['quoted_price'],
            ];
        }

        return [
            'zone_id' => $zoneId,
            'pax_range_id' => (int) $paxRange['id'],
            'pax_label' => (string) $paxRange['label'],
            'options' => $options,
        ];
    }

    private function resolveZoneId(int $placeId): ?int
    {
        $stmt = $this->db->prepare('SELECT zone_id FROM places WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute(['id' => $placeId]);
        $row = $stmt->fetch();

        if (!$row || !isset($row['zone_id'])) {
            return null;
        }

        return (int) $row['zone_id'];
    }

    private function resolvePaxRange(int $totalPax): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, label FROM pax_ranges WHERE :total BETWEEN min_pax AND max_pax ORDER BY min_pax ASC LIMIT 1'
        );
        $stmt->execute(['total' => $totalPax]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'label' => (string) $row['label'],
        ];
    }
}
