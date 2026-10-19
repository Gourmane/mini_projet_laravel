<?php
namespace Database\Seeders;
use App\Models\{User, Category, Product};
use App\Services\StockService;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@demo.test'], ['name' => 'Amine Bennani', 'password' => 'Admin123!', 'role' => 'admin']);
        User::firstOrCreate(['email' => 'employee@demo.test'], ['name' => 'Salma El Amrani', 'password' => 'Employee123!', 'role' => 'employee']);
        $groups = [
            'Ordinateurs & PC' => [
                ['Dell OptiPlex 7010 · i5 / 16 Go', 'Dell', 7900], ['Lenovo ThinkCentre M70q · i5 / 8 Go', 'Lenovo', 6200],
                ['HP ProDesk 400 G9 · i5 / 16 Go', 'HP', 7100], ['Dell Latitude 5440 · 14 pouces', 'Dell', 11500],
                ['Lenovo ThinkPad E14 · Ryzen 5', 'Lenovo', 8900], ['HP EliteBook 840 G10 · i7', 'HP', 14200],
            ],
            'RAM & Mémoire' => [
                ['Kingston Fury 16 Go DDR4 3200 MHz', 'Kingston', 490], ['Crucial 8 Go DDR4 3200 MHz', 'Crucial', 250],
                ['Corsair Vengeance 32 Go DDR4', 'Corsair', 990], ['Samsung 16 Go DDR5 4800 MHz', 'Samsung', 620],
                ['Crucial 16 Go SODIMM DDR4', 'Crucial', 420], ['Kingston ValueRAM 8 Go DDR4', 'Kingston', 230],
            ],
            'SSD & Stockage' => [
                ['Samsung 990 EVO 1 To NVMe', 'Samsung', 1190], ['Kingston NV2 500 Go NVMe', 'Kingston', 420],
                ['Crucial MX500 1 To SATA', 'Crucial', 850], ['WD Blue SN580 1 To NVMe', 'Western Digital', 890],
                ['Seagate Barracuda 2 To HDD', 'Seagate', 680], ['Samsung T7 1 To · SSD externe', 'Samsung', 1350],
                ['SanDisk Ultra 128 Go USB 3.0', 'SanDisk', 150], ['WD Red Plus 4 To NAS', 'Western Digital', 1490],
            ],
            'Claviers & Souris' => [
                ['Logitech M185 · souris sans fil', 'Logitech', 130], ['Logitech K120 · clavier AZERTY', 'Logitech', 110],
                ['Logitech MX Master 3S', 'Logitech', 1190], ['Microsoft Wired Desktop 600', 'Microsoft', 320],
                ['HP 150 · souris USB', 'HP', 75], ['Dell KB216 · clavier AZERTY', 'Dell', 160],
                ['Logitech MK270 · ensemble sans fil', 'Logitech', 340], ['Lenovo Essential · souris optique', 'Lenovo', 95],
            ],
            'Réseau & Connectivité' => [
                ['TP-Link Archer AX23 · Wi-Fi 6', 'TP-Link', 790], ['TP-Link TL-SG108 · switch 8 ports', 'TP-Link', 390],
                ['Ubiquiti UniFi U6 Lite', 'Ubiquiti', 1290], ['D-Link DGS-1016D · switch 16 ports', 'D-Link', 990],
                ['TP-Link UE300 · adaptateur Ethernet', 'TP-Link', 220], ['Intel AX210 · carte Wi-Fi 6E', 'Intel', 390],
                ['Netgear GS305 · switch 5 ports', 'Netgear', 290],
            ],
            'Écrans & Moniteurs' => [
                ['Dell P2422H · 24 pouces FHD', 'Dell', 2290], ['HP M24f · 24 pouces IPS', 'HP', 1690],
                ['Samsung S27C310 · 27 pouces', 'Samsung', 1990], ['LG 27UP650 · 27 pouces 4K', 'LG', 3990],
                ['Lenovo ThinkVision T24i-30', 'Lenovo', 2190],
            ],
            'Imprimantes & Accessoires' => [
                ['HP LaserJet Pro M404dn', 'HP', 3290], ['Brother HL-L2350DW · laser', 'Brother', 2190],
                ['Canon PIXMA G3410 · multifonction', 'Canon', 2490], ['HP 59A · toner noir', 'HP', 1090],
                ['Epson 103 · kit encre 4 couleurs', 'Epson', 390],
            ],
            'Câbles & Adaptateurs' => [
                ['UGREEN USB-C · hub 6-en-1', 'UGREEN', 390], ['Belkin HDMI 2.1 · 2 mètres', 'Belkin', 190],
                ['UGREEN Cat6 RJ45 · 3 mètres', 'UGREEN', 55], ['Anker USB-C 65 W · chargeur', 'Anker', 390],
                ['StarTech DisplayPort vers HDMI', 'StarTech', 250],
            ],
        ];
        $stock = app(StockService::class);
        $index = 0;
        foreach ($groups as $name => $rows) {
            $category = Category::firstOrCreate(['name' => $name]);
            foreach ($rows as [$productName, $brand, $price]) {
                $index++;
                $product = Product::firstOrCreate(['sku' => sprintf('IT-%04d', $index)], [
                    'name' => $productName, 'brand' => $brand, 'category_id' => $category->id,
                    'unit_price' => $price, 'min_stock' => $index % 5 + 3, 'is_active' => true,
                ]);
                if (!$product->wasRecentlyCreated) {
                    continue;
                }
                // Every product starts at zero; even an out-of-stock item has an opening IN and a valid OUT.
                if ($index % 10 === 0) {
                    $opening = 5;
                } elseif ($index % 4 === 0) {
                    $opening = 2;
                } else {
                    $opening = 12 + ($index * 7 % 33);
                }
                $stock->record($product->id, $admin, 'IN', $opening, 'Reprise du stock initial — inventaire de démonstration', now()->subDays(18)->addMinutes($index * 7));
                if ($index % 10 === 0) {
                    $stock->record($product->id, $admin, 'OUT', 5, 'Affectation au parc informatique', now()->subDays(5)->addMinutes($index));
                }
                if (in_array($index, [1, 3, 7, 13, 17, 23, 29, 33, 41, 47])) {
                    $stock->record($product->id, $admin, 'IN', 4, 'Réception du réassort atelier', now()->subDays(2)->addMinutes($index));
                    $stock->record($product->id, $admin, 'OUT', 2, 'Préparation intervention client', now()->subHours(5)->addMinutes($index));
                }
                if (in_array($index, [6, 28, 45])) {
                    $product->update(['is_active' => false]);
                }
            }
        }
        $this->call(ProductImageSeeder::class);
    }
}
