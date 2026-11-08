<?php
// Lecture seule : adresses françaises dont la région ne correspond pas au département du code postal (lot 11, A5).
// Usage, depuis le poste : ssh <vps> 'sudo -u madame-aiguille php' < docs/requetes/adresses-region.php
// Local : MA_ENV=$PWD/shop/app/etc/env.php php docs/requetes/adresses-region.php
$env = getenv('MA_ENV') ?: '/var/www/madame-aiguille/current/app/etc/env.php';
$c = (require $env)['db']['connection']['default'];
$pdo = new PDO("mysql:host={$c['host']};dbname={$c['dbname']};charset=utf8mb4", $c['username'], $c['password']);
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$sql = <<<'SQL'
SELECT 'carnet' AS source, a.entity_id, a.parent_id AS client, a.postcode, r.code AS region_code, r.default_name AS region
FROM customer_address_entity a
JOIN directory_country_region r ON r.region_id = a.region_id
WHERE a.country_id = 'FR'
  AND r.code <> LEFT(REPLACE(a.postcode, ' ', ''), 2)
  AND NOT (LEFT(a.postcode, 2) = '20' AND r.code IN ('2A', '2B'))
UNION ALL
SELECT CONCAT('commande ', o.increment_id), oa.entity_id, o.customer_id, oa.postcode, r.code, r.default_name
FROM sales_order_address oa
JOIN sales_order o ON o.entity_id = oa.parent_id
JOIN directory_country_region r ON r.region_id = oa.region_id
WHERE oa.country_id = 'FR'
  AND r.code <> LEFT(REPLACE(oa.postcode, ' ', ''), 2)
  AND NOT (LEFT(oa.postcode, 2) = '20' AND r.code IN ('2A', '2B'))
SQL;
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo count($rows), " adresse(s)\n";
foreach ($rows as $r) {
    echo implode(' | ', array_map('strval', $r)), "\n";
}
